package ao.gov.minfin.sgfe.instituicoes;

import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface InstituicaoRepository extends JpaRepository<Instituicao, Long> {
    Optional<Instituicao> findByCodigo(String codigo);
    boolean existsByCodigo(String codigo);
}
