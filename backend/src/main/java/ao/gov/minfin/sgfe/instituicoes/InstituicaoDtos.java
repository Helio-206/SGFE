package ao.gov.minfin.sgfe.instituicoes;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.Pattern;
import jakarta.validation.constraints.Size;

public final class InstituicaoDtos {
    private InstituicaoDtos() {}

    public record Request(
        @NotBlank @Size(max = 150) String nome,
        @NotBlank @Size(max = 50) String tipo,
        @NotBlank @Size(min = 3, max = 20) @Pattern(regexp = "^[A-Za-z0-9-]+$") String codigo,
        @NotBlank @Size(max = 100) String responsavel,
        @Email @Size(max = 100) String emailResponsavel,
        @Size(min = 8, max = 100) String senhaResponsavel
    ) {}

    public record Response(
        Long id,
        String nome,
        String tipo,
        String codigo,
        String responsavel,
        String status
    ) {
        public static Response from(Instituicao instituicao) {
            return new Response(
                instituicao.getId(),
                instituicao.getNome(),
                instituicao.getTipo(),
                instituicao.getCodigo(),
                instituicao.getResponsavel(),
                instituicao.getStatus()
            );
        }
    }
}
