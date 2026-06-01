package ao.gov.minfin.sgfe.common;

import java.util.EnumSet;
import java.util.Set;

public enum EstadoDespesa {
    PENDENTE_CABIMENTADA,
    LIQUIDADA_APROVADA,
    PAGA,
    REJEITADA,
    CANCELADA,
    EM_ANALISE;

    public static Set<EstadoDespesa> estadosQueComprometemTecto() {
        return EnumSet.of(PENDENTE_CABIMENTADA, LIQUIDADA_APROVADA, PAGA);
    }
}
