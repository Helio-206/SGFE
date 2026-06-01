package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.common.RegraNegocioException;
import java.security.SecureRandom;
import org.springframework.stereotype.Service;

@Service
public class RupeService {
    private final SecureRandom random = new SecureRandom();
    private final TransacaoReceitaRepository receitas;

    public RupeService(TransacaoReceitaRepository receitas) {
        this.receitas = receitas;
    }

    public String gerarCodigoRupe() {
        for (int tentativa = 0; tentativa < 20; tentativa++) {
            StringBuilder codigo = new StringBuilder(20);
            for (int i = 0; i < 20; i++) {
                codigo.append(random.nextInt(10));
            }
            String rupe = codigo.toString();
            if (!receitas.existsByCodigoRupe(rupe)) {
                return rupe;
            }
        }
        throw new RegraNegocioException("Nao foi possivel gerar uma RUPE unica. Tente novamente.");
    }
}
