package ao.gov.minfin.sgfe.receitas;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

public interface AutorizacaoReceitaRetroativaRepository extends JpaRepository<AutorizacaoReceitaRetroativa, Long> {
    Page<AutorizacaoReceitaRetroativa> findByInstituicaoId(Long idInst, Pageable pageable);
}
