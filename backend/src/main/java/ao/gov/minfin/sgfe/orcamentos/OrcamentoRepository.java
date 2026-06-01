package ao.gov.minfin.sgfe.orcamentos;

import jakarta.persistence.LockModeType;
import java.math.BigDecimal;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Lock;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface OrcamentoRepository extends JpaRepository<Orcamento, Long> {
    Optional<Orcamento> findByInstituicaoIdAndAnoFiscal(Long idInst, Integer anoFiscal);

    @Lock(LockModeType.PESSIMISTIC_WRITE)
    @Query("select o from Orcamento o where o.instituicao.id = :idInst and o.anoFiscal = :anoFiscal")
    Optional<Orcamento> findForUpdate(@Param("idInst") Long idInst, @Param("anoFiscal") Integer anoFiscal);

    @Query("select coalesce(sum(o.valorTotal), 0) from Orcamento o where o.anoFiscal = :anoFiscal")
    BigDecimal sumTectoByAno(@Param("anoFiscal") Integer anoFiscal);

    boolean existsByInstituicaoIdAndAnoFiscal(Long idInst, Integer anoFiscal);
}
