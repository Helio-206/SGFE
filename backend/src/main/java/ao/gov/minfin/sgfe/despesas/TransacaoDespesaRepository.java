package ao.gov.minfin.sgfe.despesas;

import ao.gov.minfin.sgfe.common.EstadoDespesa;
import java.math.BigDecimal;
import java.util.Collection;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface TransacaoDespesaRepository extends JpaRepository<TransacaoDespesa, Long> {
    Page<TransacaoDespesa> findByInstituicaoId(Long idInst, Pageable pageable);

    @Query("""
        select coalesce(sum(d.valorBruto), 0)
        from TransacaoDespesa d
        where d.instituicao.id = :idInst
          and year(d.dataRegistro) = :ano
          and d.estado in :estados
    """)
    BigDecimal sumByInstituicaoAnoEstados(
        @Param("idInst") Long idInst,
        @Param("ano") Integer ano,
        @Param("estados") Collection<EstadoDespesa> estados
    );

    @Query("""
        select coalesce(sum(d.valorBruto), 0)
        from TransacaoDespesa d
        where year(d.dataRegistro) = :ano
          and d.estado in :estados
    """)
    BigDecimal sumByAnoEstados(@Param("ano") Integer ano, @Param("estados") Collection<EstadoDespesa> estados);

    @Query("""
        select coalesce(sum(d.valorBruto), 0)
        from TransacaoDespesa d
        where year(d.dataRegistro) = :ano
          and d.estado = :estado
    """)
    BigDecimal sumByAnoEstado(@Param("ano") Integer ano, @Param("estado") EstadoDespesa estado);

    long countByEstado(EstadoDespesa estado);
}
