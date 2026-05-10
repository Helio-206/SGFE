package ao.gov.minfin.sgfe.users;

import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public final class UserDtos {
    private UserDtos() {}

    public record CreateRequest(
        @NotBlank @Size(max = 100) String nome,
        @NotBlank @Size(max = 50) String username,
        @Email @NotBlank @Size(max = 100) String email,
        @NotBlank @Size(min = 8, max = 100) String password,
        @NotNull Role role,
        @NotNull UserStatus status,
        @NotNull Long idInst
    ) {}

    public record UpdateRequest(
        @NotBlank @Size(max = 100) String nome,
        @NotBlank @Size(max = 50) String username,
        @Email @NotBlank @Size(max = 100) String email,
        @NotNull Role role,
        @NotNull UserStatus status,
        @NotNull Long idInst
    ) {}

    public record ProfileRequest(
        @NotBlank @Size(max = 100) String nome,
        @Email @NotBlank @Size(max = 100) String email
    ) {}

    public record PasswordRequest(
        @NotBlank String currentPassword,
        @NotBlank @Size(min = 8, max = 100) String newPassword
    ) {}

    public record RoleStatusRequest(
        Role role,
        UserStatus status
    ) {}

    public record Response(
        Long id,
        String nome,
        String username,
        String email,
        Role role,
        UserStatus status,
        Long idInst,
        String codigoUo,
        String instituicao
    ) {
        public static Response from(User user) {
            return new Response(
                user.getId(),
                user.getNome(),
                user.getUsername(),
                user.getEmail(),
                user.getRole(),
                user.getStatus(),
                user.getInstituicao().getId(),
                user.getInstituicao().getCodigo(),
                user.getInstituicao().getNome()
            );
        }
    }
}
