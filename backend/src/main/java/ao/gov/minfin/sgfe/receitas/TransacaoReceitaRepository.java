package ao.gov.minfin.sgfe.receitas;

import java.math.BigDecimal;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface TransacaoReceitaRepository extends JpaRepository<TransacaoReceita, Long> {
    boolean existsByCodigoRupe(String codigoRupe);

    Page<TransacaoReceita> findByInstituicaoId(Long idInst, Pageable pageable);

    @Query("select coalesce(sum(r.valorArrecadado), 0) from TransacaoReceita r where year(r.dataRegistro) = :ano")
    BigDecimal sumByAno(@Param("ano") Integer ano);

    @Query("select coalesce(sum(r.valorArrecadado), 0) from TransacaoReceita r where r.instituicao.id = :idInst and year(r.dataRegistro) = :ano")
    BigDecimal sumByInstituicaoAndAno(@Param("idInst") Long idInst, @Param("ano") Integer ano);
}
