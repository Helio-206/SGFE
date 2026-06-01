package ao.gov.minfin.sgfe.auth;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.time.Instant;
import java.util.HexFormat;
import java.util.Map;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class AuthService {
    private static final long SECONDS_PER_DAY = 24 * 60 * 60;

    private final AuthenticationManager authenticationManager;
    private final UserRepository users;
    private final RefreshTokenRepository refreshTokens;
    private final PasswordResetTokenRepository passwordResetTokens;
    private final JwtService jwtService;
    private final AuditService auditService;
    private final AuthCookieService authCookieService;
    private final PasswordEncoder passwordEncoder;
    private final SecureRandom secureRandom = new SecureRandom();
    private final long accessTokenMinutes;
    private final long refreshTokenDays;

    public AuthService(
        AuthenticationManager authenticationManager,
        UserRepository users,
        RefreshTokenRepository refreshTokens,
        PasswordResetTokenRepository passwordResetTokens,
        JwtService jwtService,
        AuditService auditService,
        AuthCookieService authCookieService,
        PasswordEncoder passwordEncoder,
        @Value("${sgfe.security.access-token-minutes}") long accessTokenMinutes,
        @Value("${sgfe.security.refresh-token-days}") long refreshTokenDays
    ) {
        if (accessTokenMinutes <= 0 || refreshTokenDays <= 0) {
            throw new IllegalStateException("Tempos de sessao SGFE devem ser maiores que zero.");
        }
        this.authenticationManager = authenticationManager;
        this.users = users;
        this.refreshTokens = refreshTokens;
        this.passwordResetTokens = passwordResetTokens;
        this.jwtService = jwtService;
        this.auditService = auditService;
        this.authCookieService = authCookieService;
        this.passwordEncoder = passwordEncoder;
        this.accessTokenMinutes = accessTokenMinutes;
        this.refreshTokenDays = refreshTokenDays;
    }

    @Transactional
    public AuthDtos.TokenResponse login(AuthDtos.LoginRequest request, HttpServletRequest http) {
        authenticationManager.authenticate(
            new UsernamePasswordAuthenticationToken(request.email(), request.password())
        );

        User user = users.findByEmailIgnoreCase(request.email())
            .orElseThrow(() -> new RegraNegocioException("Credenciais invalidas."));

        if (user.getStatus() != UserStatus.ATIVO) {
            auditService.registrar(user, user.getInstituicao(), "LOGIN_BLOQUEADO", "USER", String.valueOf(user.getId()),
                "NEGADO", "ALERTA", Map.of("motivo", "utilizador_inativo"), http);
            throw new RegraNegocioException("A conta esta inativa. Contacte o administrador.");
        }

        UserPrincipal principal = UserPrincipal.from(user);
        String accessToken = jwtService.gerarAccessToken(principal);
        String refreshToken = criarRefreshToken(user, http);

        auditService.registrar(user, user.getInstituicao(), "LOGIN", "USER", String.valueOf(user.getId()),
            "SUCESSO", "INFO", Map.of("role", user.getRole().name()), http);

        return resposta(accessToken, refreshToken, user);
    }

    @Transactional
    public AuthDtos.TokenResponse refresh(AuthDtos.RefreshRequest request, HttpServletRequest http) {
        RefreshToken atual = refreshTokens.findByTokenHash(hash(resolveRefreshToken(request, http)))
            .orElseThrow(() -> new RegraNegocioException("Refresh token invalido."));

        if (atual.getRevokedAt() != null || atual.getExpiresAt().isBefore(Instant.now())) {
            throw new RegraNegocioException("Refresh token expirado ou revogado.");
        }

        User user = atual.getUsuario();
        if (user.getStatus() != UserStatus.ATIVO) {
            throw new RegraNegocioException("A conta esta inativa.");
        }

        atual.setRevokedAt(Instant.now());
        String novoRefresh = criarRefreshToken(user, http);
        String accessToken = jwtService.gerarAccessToken(UserPrincipal.from(user));

        auditService.registrar(user, user.getInstituicao(), "REFRESH_TOKEN_ROTACIONADO", "USER", String.valueOf(user.getId()),
            "SUCESSO", "INFO", Map.of(), http);

        return resposta(accessToken, novoRefresh, user);
    }

    @Transactional
    public void logout(AuthDtos.RefreshRequest request, HttpServletRequest http) {
        String refreshToken = resolveRefreshToken(request, http);

        refreshTokens.findByTokenHash(hash(refreshToken)).ifPresent(token -> {
            token.setRevokedAt(Instant.now());
            User user = token.getUsuario();
            auditService.registrar(user, user.getInstituicao(), "LOGOUT", "USER", String.valueOf(user.getId()),
                "SUCESSO", "INFO", Map.of(), http);
        });
    }

    @Transactional
    public AuthDtos.MessageResponse forgotPassword(AuthDtos.ForgotPasswordRequest request, HttpServletRequest http) {
        users.findByEmailIgnoreCase(request.email()).ifPresent(user -> {
            String raw = gerarTokenSeguro();
            PasswordResetToken token = new PasswordResetToken();
            token.setEmail(user.getEmail());
            token.setTokenHash(hash(raw));
            token.setExpiresAt(Instant.now().plusSeconds(60 * 60));
            token.setIpAddress(http != null ? http.getRemoteAddr() : null);
            passwordResetTokens.save(token);

            auditService.registrar(user, user.getInstituicao(), "SOLICITAR_RESET_SENHA", "USER", String.valueOf(user.getId()),
                "SUCESSO", "ALERTA", Map.of("entrega", "token_persistido_para_servico_de_email"), http);
        });

        return new AuthDtos.MessageResponse("Se a conta existir, as instrucoes de recuperacao serao enviadas pelo canal institucional configurado.");
    }

    @Transactional
    public AuthDtos.MessageResponse resetPassword(AuthDtos.ResetPasswordRequest request, HttpServletRequest http) {
        PasswordResetToken token = passwordResetTokens.findByTokenHash(hash(request.token()))
            .orElseThrow(() -> new RegraNegocioException("Token de recuperacao invalido."));

        if (token.getUsedAt() != null || token.getExpiresAt().isBefore(Instant.now())) {
            throw new RegraNegocioException("Token de recuperacao expirado ou utilizado.");
        }

        User user = users.findByEmailIgnoreCase(token.getEmail())
            .orElseThrow(() -> new RegraNegocioException("Utilizador não encontrado."));
        user.setPasswordHash(passwordEncoder.encode(request.newPassword()));
        token.setUsedAt(Instant.now());

        auditService.registrar(user, user.getInstituicao(), "RESET_SENHA", "USER", String.valueOf(user.getId()),
            "SUCESSO", "CRITICO", Map.of(), http);

        return new AuthDtos.MessageResponse("Palavra-passe alterada com sucesso.");
    }

    private AuthDtos.TokenResponse resposta(String accessToken, String refreshToken, User user) {
        return new AuthDtos.TokenResponse(
            accessToken,
            refreshToken,
            "Bearer",
            accessTokenSeconds(),
            new AuthDtos.MeResponse(user.getId(), user.getInstituicao().getId(), user.getNome(), user.getEmail(), user.getRole().name())
        );
    }

    public AuthDtos.TokenResponse sanitize(AuthDtos.TokenResponse response) {
        return new AuthDtos.TokenResponse(
            null,
            null,
            response.tokenType(),
            response.expiresInSeconds(),
            response.user()
        );
    }

    public long accessTokenSeconds() {
        return accessTokenMinutes * 60;
    }

    public long refreshTokenSeconds() {
        return refreshTokenDays * SECONDS_PER_DAY;
    }

    private String criarRefreshToken(User user, HttpServletRequest http) {
        String raw = gerarTokenSeguro();

        RefreshToken token = new RefreshToken();
        token.setUsuario(user);
        token.setTokenHash(hash(raw));
        token.setExpiresAt(Instant.now().plusSeconds(refreshTokenSeconds()));
        token.setIpAddress(http != null ? http.getRemoteAddr() : null);
        token.setUserAgent(http != null ? http.getHeader("User-Agent") : null);
        refreshTokens.save(token);

        return raw;
    }

    private String gerarTokenSeguro() {
        byte[] randomBytes = new byte[64];
        secureRandom.nextBytes(randomBytes);
        return java.util.Base64.getUrlEncoder().withoutPadding().encodeToString(randomBytes);
    }

    private String hash(String raw) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            return HexFormat.of().formatHex(digest.digest(raw.getBytes(StandardCharsets.UTF_8)));
        } catch (Exception ex) {
            throw new IllegalStateException("Não foi possivel calcular hash do token.", ex);
        }
    }

    private String resolveRefreshToken(AuthDtos.RefreshRequest request, HttpServletRequest http) {
        String providedToken = request != null ? request.refreshToken() : null;
        if (providedToken != null && !providedToken.isBlank()) {
            return providedToken;
        }

        return authCookieService.resolveRefreshToken(http)
            .orElseThrow(() -> new RegraNegocioException("Refresh token invalido."));
    }
}
