package ao.gov.minfin.sgfe.auth;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/auth")
public class AuthController {
    private final AuthService authService;
    private final AuthCookieService authCookieService;

    public AuthController(AuthService authService, AuthCookieService authCookieService) {
        this.authService = authService;
        this.authCookieService = authCookieService;
    }

    @PostMapping("/login")
    public AuthDtos.TokenResponse login(
        @Valid @RequestBody AuthDtos.LoginRequest request,
        HttpServletRequest http,
        HttpServletResponse response
    ) {
        AuthDtos.TokenResponse tokens = authService.login(request, http);
        authCookieService.writeAuthenticationCookies(
            response,
            tokens.accessToken(),
            authService.accessTokenSeconds(),
            tokens.refreshToken(),
            authService.refreshTokenSeconds()
        );
        return authService.sanitize(tokens);
    }

    @PostMapping("/refresh")
    public AuthDtos.TokenResponse refresh(
        @RequestBody(required = false) AuthDtos.RefreshRequest request,
        HttpServletRequest http,
        HttpServletResponse response
    ) {
        AuthDtos.TokenResponse tokens = authService.refresh(request, http);
        authCookieService.writeAuthenticationCookies(
            response,
            tokens.accessToken(),
            authService.accessTokenSeconds(),
            tokens.refreshToken(),
            authService.refreshTokenSeconds()
        );
        return authService.sanitize(tokens);
    }

    @PostMapping("/logout")
    public ResponseEntity<Void> logout(
        @RequestBody(required = false) AuthDtos.RefreshRequest request,
        HttpServletRequest http,
        HttpServletResponse response
    ) {
        authService.logout(request, http);
        authCookieService.clearAuthenticationCookies(response);
        return ResponseEntity.noContent().build();
    }

    @PostMapping("/forgot-password")
    public AuthDtos.MessageResponse forgotPassword(@Valid @RequestBody AuthDtos.ForgotPasswordRequest request, HttpServletRequest http) {
        return authService.forgotPassword(request, http);
    }

    @PostMapping("/reset-password")
    public AuthDtos.MessageResponse resetPassword(@Valid @RequestBody AuthDtos.ResetPasswordRequest request, HttpServletRequest http) {
        return authService.resetPassword(request, http);
    }

    @GetMapping("/me")
    public AuthDtos.MeResponse me(@AuthenticationPrincipal UserPrincipal principal) {
        return new AuthDtos.MeResponse(principal.id(), principal.idInst(), principal.nome(), principal.getUsername(), principal.role().name());
    }
}
