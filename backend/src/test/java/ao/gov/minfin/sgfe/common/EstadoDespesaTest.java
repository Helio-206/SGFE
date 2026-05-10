package ao.gov.minfin.sgfe.common;

import static org.assertj.core.api.Assertions.assertThat;

import org.junit.jupiter.api.Test;

class EstadoDespesaTest {
    @Test
    void estadosQueComprometemTectoIncluemTodasAsFasesFinanceirasAtivas() {
        assertThat(EstadoDespesa.estadosQueComprometemTecto())
            .containsExactlyInAnyOrder(
                EstadoDespesa.PENDENTE_CABIMENTADA,
                EstadoDespesa.LIQUIDADA_APROVADA,
                EstadoDespesa.PAGA
            )
            .doesNotContain(
                EstadoDespesa.REJEITADA,
                EstadoDespesa.CANCELADA,
                EstadoDespesa.EM_ANALISE
            );
    }
}
