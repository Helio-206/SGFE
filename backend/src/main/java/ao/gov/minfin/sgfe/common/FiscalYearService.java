package ao.gov.minfin.sgfe.common;

import java.time.Clock;
import java.time.LocalDate;
import org.springframework.stereotype.Service;

@Service
public class FiscalYearService {
    private final Clock clock;

    public FiscalYearService() {
        this.clock = Clock.systemUTC();
    }

    public int anoCorrente() {
        return LocalDate.now(clock).getYear();
    }

    public void validarDataNoAnoFiscalCorrente(LocalDate data) {
        if (data == null) {
            throw new RegraNegocioException("A data e obrigatoria.");
        }

        LocalDate hoje = LocalDate.now(clock);
        LocalDate primeiroDia = LocalDate.of(hoje.getYear(), 1, 1);

        if (data.isBefore(primeiroDia) || data.isAfter(hoje)) {
            throw new RegraNegocioException("A data deve pertencer ao ano fiscal corrente e nao pode ser futura.");
        }
    }
}
