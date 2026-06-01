package ao.gov.minfin.sgfe.auth;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.util.Optional;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.ResponseCookie;
import org.springframework.stereotype.Component;
import org.springframework.web.util.WebUtils;

@Component
public class AuthCookieService {
    public static final String ACCESS_COOKIE = "SGFE_ACCESS_TOKEN";
    public static final String REFRESH_COOKIE = "SGFE_REFRESH_TOKEN";

    private final boolean secureCookies;
    private final String sameSite;

    public AuthCookieService(
        @Value("${sgfe.security.cookie-secure:false}") boolean secureCookies,
        @Value("${sgfe.security.cookie-same-site:Lax}") String sameSite
    ) {
        this.sameSite = normalizeSameSite(sameSite);
        this.secureCookies = secureCookies || "None".equals(this.sameSite);
    }

    public void writeAuthenticationCookies(
        HttpServletResponse response,
        String accessToken,
        long accessTokenSeconds,
        String refreshToken,
        long refreshTokenSeconds
    ) {
        response.addHeader("Set-Cookie", buildCookie(ACCESS_COOKIE, accessToken, "/", accessTokenSeconds).toString());
        response.addHeader("Set-Cookie", buildCookie(REFRESH_COOKIE, refreshToken, "/api/auth", refreshTokenSeconds).toString());
    }

    public void clearAuthenticationCookies(HttpServletResponse response) {
        response.addHeader("Set-Cookie", buildCookie(ACCESS_COOKIE, "", "/", 0).toString());
        response.addHeader("Set-Cookie", buildCookie(REFRESH_COOKIE, "", "/api/auth", 0).toString());
    }

    public Optional<String> resolveAccessToken(HttpServletRequest request) {
        return resolveCookie(request, ACCESS_COOKIE);
    }

    public Optional<String> resolveRefreshToken(HttpServletRequest request) {
        return resolveCookie(request, REFRESH_COOKIE);
    }

    private Optional<String> resolveCookie(HttpServletRequest request, String name) {
        if (request == null) {
            return Optional.empty();
        }

        var cookie = WebUtils.getCookie(request, name);
        if (cookie == null || cookie.getValue() == null || cookie.getValue().isBlank()) {
            return Optional.empty();
        }

        return Optional.of(cookie.getValue());
    }

    private ResponseCookie buildCookie(String name, String value, String path, long maxAgeSeconds) {
        return ResponseCookie.from(name, value)
            .httpOnly(true)
            .secure(secureCookies)
            .sameSite(sameSite)
            .path(path)
            .maxAge(maxAgeSeconds)
            .build();
    }

    private String normalizeSameSite(String value) {
        if (value == null || value.isBlank()) {
            return "Lax";
        }

        return switch (value.trim().toLowerCase()) {
            case "strict" -> "Strict";
            case "none" -> "None";
            default -> "Lax";
        };
    }
}
