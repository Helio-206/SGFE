package ao.gov.minfin.sgfe.orcamentos;

import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;

public final class OrcamentoDtos {
    private OrcamentoDtos() {}

    public record Request(
        @NotNull Long idInst,
        @NotNull @DecimalMin(value = "0.00") BigDecimal valorTotal
    ) {}

    public record Response(
        Long id,
        Long idInst,
        String codigoUo,
        String instituicao,
        BigDecimal valorTotal,
        BigDecimal valorComprometido,
        BigDecimal saldoDisponivel,
        Integer anoFiscal
    ) {
        public static Response from(Orcamento o, BigDecimal comprometido) {
            BigDecimal saldo = o.getValorTotal().subtract(comprometido);
            if (saldo.signum() < 0) {
                saldo = BigDecimal.ZERO;
            }
            return new Response(
                o.getId(),
                o.getInstituicao().getId(),
                o.getInstituicao().getCodigo(),
                o.getInstituicao().getNome(),
                o.getValorTotal(),
                comprometido,
                saldo,
                o.getAnoFiscal()
            );
        }
    }
}
