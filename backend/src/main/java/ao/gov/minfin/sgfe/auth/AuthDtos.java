package ao.gov.minfin.sgfe.auth;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;

public final class AuthDtos {
    private AuthDtos() {}

    public record LoginRequest(
        @Email @NotBlank String email,
        @NotBlank String password
    ) {}

    public record RefreshRequest(
        String refreshToken
    ) {}

    public record ForgotPasswordRequest(
        @Email @NotBlank String email
    ) {}

    public record ResetPasswordRequest(
        @NotBlank String token,
        @NotBlank String newPassword
    ) {}

    public record MessageResponse(String message) {}

    public record TokenResponse(
        String accessToken,
        String refreshToken,
        String tokenType,
        long expiresInSeconds,
        MeResponse user
    ) {}

    public record MeResponse(
        Long id,
        Long idInst,
        String nome,
        String email,
        String role
    ) {}
}
