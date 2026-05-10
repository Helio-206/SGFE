package ao.gov.minfin.sgfe.classificacoes;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ClassificacaoEconomicaRepository extends JpaRepository<ClassificacaoEconomica, Long> {
    Optional<ClassificacaoEconomica> findByCodigo(String codigo);
    boolean existsByCodigo(String codigo);
}
