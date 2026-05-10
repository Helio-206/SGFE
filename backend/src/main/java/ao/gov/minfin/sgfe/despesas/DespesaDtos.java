package ao.gov.minfin.sgfe.despesas;

import ao.gov.minfin.sgfe.common.EstadoDespesa;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import java.math.BigDecimal;
import java.time.LocalDate;

public final class DespesaDtos {
    private DespesaDtos() {}

    public record CriarRequest(
        Long idInst,
        @NotBlank @Size(max = 150) String descricao,
        @NotNull @DecimalMin(value = "0.01") BigDecimal valorBruto,
        @NotNull LocalDate dataRegistro,
        @NotNull Long idClasse
    ) {}

    public record Response(
        Long id,
        Long idInst,
        String codigoUo,
        String instituicao,
        String descricao,
        BigDecimal valorBruto,
        LocalDate dataRegistro,
        EstadoDespesa estado,
        Long idClasse,
        String codigoClasse
    ) {
        public static Response from(TransacaoDespesa d) {
            return new Response(
                d.getId(),
                d.getInstituicao().getId(),
                d.getInstituicao().getCodigo(),
                d.getInstituicao().getNome(),
                d.getDescricao(),
                d.getValorBruto(),
                d.getDataRegistro(),
                d.getEstado(),
                d.getClassificacao() != null ? d.getClassificacao().getId() : null,
                d.getClassificacao() != null ? d.getClassificacao().getCodigo() : null
            );
        }
    }
}
