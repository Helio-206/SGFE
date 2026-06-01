package ao.gov.minfin.sgfe.auth;

import io.jsonwebtoken.Claims;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.security.Keys;
import java.nio.charset.StandardCharsets;
import java.time.Instant;
import java.util.Date;
import javax.crypto.SecretKey;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

@Service
public class JwtService {
    private final String secret;
    private final long accessTokenMinutes;

    public JwtService(
        @Value("${sgfe.security.jwt-secret}") String secret,
        @Value("${sgfe.security.access-token-minutes}") long accessTokenMinutes
    ) {
        if (secret == null || secret.getBytes(StandardCharsets.UTF_8).length < 32) {
            throw new IllegalStateException("SGFE_JWT_SECRET deve ter pelo menos 32 bytes.");
        }
        if (accessTokenMinutes <= 0) {
            throw new IllegalStateException("SGFE_ACCESS_TOKEN_MINUTES deve ser maior que zero.");
        }
        this.secret = secret;
        this.accessTokenMinutes = accessTokenMinutes;
    }

    public String gerarAccessToken(UserPrincipal principal) {
        Instant agora = Instant.now();
        Instant expira = agora.plusSeconds(accessTokenMinutes * 60);

        return Jwts.builder()
            .subject(principal.getUsername())
            .claim("uid", principal.id())
            .claim("idInst", principal.idInst())
            .claim("role", principal.role().name())
            .issuedAt(Date.from(agora))
            .expiration(Date.from(expira))
            .signWith(chave(), Jwts.SIG.HS256)
            .compact();
    }

    public String subject(String token) {
        return claims(token).getSubject();
    }

    public Claims claims(String token) {
        return Jwts.parser()
            .verifyWith(chave())
            .build()
            .parseSignedClaims(token)
            .getPayload();
    }

    private SecretKey chave() {
        return Keys.hmacShaKeyFor(secret.getBytes(StandardCharsets.UTF_8));
    }
}
