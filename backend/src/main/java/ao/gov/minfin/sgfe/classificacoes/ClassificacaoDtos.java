package ao.gov.minfin.sgfe.classificacoes;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

public final class ClassificacaoDtos {
    private ClassificacaoDtos() {}

    public record Request(
        @NotBlank @Size(max = 100) String descricao,
        @NotBlank @Size(max = 20) String codigo,
        @NotBlank @Size(max = 80) String tipo
    ) {}

    public record Response(Long id, String descricao, String codigo, String tipo) {
        public static Response from(ClassificacaoEconomica c) {
            return new Response(c.getId(), c.getDescricao(), c.getCodigo(), c.getTipo());
        }
    }
}
