package ao.gov.minfin.sgfe.dashboard;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.EstadoDespesa;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.despesas.TransacaoDespesaRepository;
import ao.gov.minfin.sgfe.orcamentos.OrcamentoRepository;
import ao.gov.minfin.sgfe.receitas.TransacaoReceitaRepository;
import java.math.BigDecimal;
import java.util.List;
import java.util.Map;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class DashboardService {
    private final FiscalYearService fiscalYear;
    private final OrcamentoRepository orcamentos;
    private final TransacaoDespesaRepository despesas;
    private final TransacaoReceitaRepository receitas;
    private final JdbcTemplate jdbc;

    public DashboardService(
        FiscalYearService fiscalYear,
        OrcamentoRepository orcamentos,
        TransacaoDespesaRepository despesas,
        TransacaoReceitaRepository receitas,
        JdbcTemplate jdbc
    ) {
        this.fiscalYear = fiscalYear;
        this.orcamentos = orcamentos;
        this.despesas = despesas;
        this.receitas = receitas;
        this.jdbc = jdbc;
    }

    @Transactional(readOnly = true)
    public Map<String, Object> dados(UserPrincipal principal) {
        int ano = fiscalYear.anoCorrente();
        boolean gestor = principal.isGestor();
        Long idInst = principal.idInst();

        BigDecimal tecto = gestor
            ? orcamentos.findByInstituicaoIdAndAnoFiscal(idInst, ano).map(o -> o.getValorTotal()).orElse(BigDecimal.ZERO)
            : orcamentos.sumTectoByAno(ano);
        BigDecimal comprometido = gestor
            ? despesas.sumByInstituicaoAnoEstados(idInst, ano, EstadoDespesa.estadosQueComprometemTecto())
            : despesas.sumByAnoEstados(ano, EstadoDespesa.estadosQueComprometemTecto());
        BigDecimal pago = gestor
            ? despesas.sumByInstituicaoAnoEstados(idInst, ano, List.of(EstadoDespesa.PAGA))
            : despesas.sumByAnoEstado(ano, EstadoDespesa.PAGA);
        BigDecimal receita = gestor
            ? receitas.sumByInstituicaoAndAno(idInst, ano)
            : receitas.sumByAno(ano);
        BigDecimal saldo = tecto.subtract(comprometido).max(BigDecimal.ZERO);
        BigDecimal execucao = tecto.signum() > 0
            ? comprometido.multiply(BigDecimal.valueOf(100)).divide(tecto, 2, java.math.RoundingMode.HALF_UP)
            : BigDecimal.ZERO;

        return Map.of(
            "anoFiscal", ano,
            "contexto", gestor ? "UO" : "NACIONAL",
            "tectoTotal", tecto,
            "valorComprometido", comprometido,
            "valorPago", pago,
            "totalReceita", receita,
            "saldoDisponivel", saldo,
            "percentualExecucao", execucao,
            "riscoOrcamental", risco(execucao),
            "topUos", gestor ? List.of() : topUos(ano)
        );
    }

    private String risco(BigDecimal execucao) {
        if (execucao.compareTo(BigDecimal.valueOf(95)) >= 0) {
            return "CRITICO";
        }
        if (execucao.compareTo(BigDecimal.valueOf(80)) >= 0) {
            return "ALTO";
        }
        if (execucao.compareTo(BigDecimal.valueOf(60)) >= 0) {
            return "MODERADO";
        }
        return "CONTROLADO";
    }

    private List<Map<String, Object>> topUos(int ano) {
        return jdbc.queryForList("""
            SELECT i.codigo, i.nome, o.valor_total AS tecto,
                   COALESCE(SUM(td.valor_bruto), 0) AS comprometido,
                   CASE WHEN o.valor_total > 0
                        THEN ROUND((COALESCE(SUM(td.valor_bruto), 0) / o.valor_total) * 100, 2)
                        ELSE 0 END AS percentual
            FROM orcamentos o
            JOIN instituicoes i ON i.id_inst = o.id_inst
            LEFT JOIN transacoes_despesas td ON td.id_inst = o.id_inst
                AND YEAR(td.data_registro) = o.ano_fiscal
                AND td.estado IN ('PENDENTE_CABIMENTADA','LIQUIDADA_APROVADA','PAGA')
            WHERE o.ano_fiscal = ?
            GROUP BY i.id_inst, i.codigo, i.nome, o.valor_total
            ORDER BY percentual DESC
            LIMIT 5
        """, ano);
    }
}
