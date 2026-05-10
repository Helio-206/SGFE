package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.common.FonteReceita;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDate;

public final class ReceitaDtos {
    private ReceitaDtos() {}

    public record CriarRequest(
        Long idInst,
        @NotNull FonteReceita fonteReceita,
        @NotNull LocalDate dataRegistro,
        @NotNull @DecimalMin(value = "0.01") BigDecimal valorArrecadado,
        @NotNull Long idClasse
    ) {}

    public record Response(
        Long id,
        Long idInst,
        String codigoUo,
        String instituicao,
        FonteReceita fonteReceita,
        String codigoRupe,
        LocalDate dataRegistro,
        BigDecimal valorArrecadado,
        Long idClasse,
        String codigoClasse
    ) {
        public static Response from(TransacaoReceita r) {
            return new Response(
                r.getId(),
                r.getInstituicao().getId(),
                r.getInstituicao().getCodigo(),
                r.getInstituicao().getNome(),
                r.getFonteReceita(),
                r.getCodigoRupe(),
                r.getDataRegistro(),
                r.getValorArrecadado(),
                r.getClassificacao().getId(),
                r.getClassificacao().getCodigo()
            );
        }
    }
}
