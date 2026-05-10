package ao.gov.minfin.sgfe.orcamentos;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.EstadoDespesa;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.despesas.TransacaoDespesaRepository;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.math.BigDecimal;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class OrcamentoService {
    private final OrcamentoRepository orcamentos;
    private final TransacaoDespesaRepository despesas;
    private final InstituicaoRepository instituicoes;
    private final UserRepository users;
    private final FiscalYearService fiscalYear;
    private final AuditService auditService;

    public OrcamentoService(
        OrcamentoRepository orcamentos,
        TransacaoDespesaRepository despesas,
        InstituicaoRepository instituicoes,
        UserRepository users,
        FiscalYearService fiscalYear,
        AuditService auditService
    ) {
        this.orcamentos = orcamentos;
        this.despesas = despesas;
        this.instituicoes = instituicoes;
        this.users = users;
        this.fiscalYear = fiscalYear;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<OrcamentoDtos.Response> listar(Pageable pageable) {
        return orcamentos.findAll(pageable)
            .map(o -> OrcamentoDtos.Response.from(o, comprometido(o.getInstituicao().getId(), o.getAnoFiscal())));
    }

    @Transactional(readOnly = true)
    public OrcamentoDtos.Response consultarTecto(UserPrincipal principal) {
        int ano = fiscalYear.anoCorrente();
        Long idInst = principal.idInst();
        Orcamento orcamento = orcamentos.findByInstituicaoIdAndAnoFiscal(idInst, ano)
            .orElseThrow(() -> new RegraNegocioException("A Unidade Orcamental nao possui tecto para o ano fiscal corrente."));
        return OrcamentoDtos.Response.from(orcamento, comprometido(idInst, ano));
    }

    @Transactional
    public OrcamentoDtos.Response criar(OrcamentoDtos.Request request, UserPrincipal principal, HttpServletRequest http) {
        int ano = fiscalYear.anoCorrente();
        if (orcamentos.existsByInstituicaoIdAndAnoFiscal(request.idInst(), ano)) {
            throw new RegraNegocioException("Ja existe tecto orcamental para esta UO no ano fiscal corrente.");
        }

        Instituicao inst = instituicoes.findById(request.idInst())
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));
        User user = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));

        Orcamento o = new Orcamento();
        o.setInstituicao(inst);
        o.setUsuario(user);
        o.setValorTotal(request.valorTotal());
        o.setAnoFiscal(ano);
        Orcamento salvo = orcamentos.save(o);

        auditService.registrar(user, inst, "CRIAR_ORCAMENTO", "ORCAMENTO", String.valueOf(salvo.getId()),
            "SUCESSO", "CRITICO", Map.of("valorTotal", request.valorTotal(), "anoFiscal", ano), http);

        return OrcamentoDtos.Response.from(salvo, BigDecimal.ZERO);
    }

    @Transactional
    public OrcamentoDtos.Response atualizar(Long id, OrcamentoDtos.Request request, UserPrincipal principal, HttpServletRequest http) {
        Orcamento o = orcamentos.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Orcamento nao encontrado."));
        User user = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));

        BigDecimal comprometido = comprometido(o.getInstituicao().getId(), o.getAnoFiscal());
        if (request.valorTotal().compareTo(comprometido) < 0) {
            auditService.registrar(user, o.getInstituicao(), "EDITAR_ORCAMENTO_BLOQUEADO", "ORCAMENTO", String.valueOf(o.getId()),
                "FALHA", "ALERTA", Map.of("valorSolicitado", request.valorTotal(), "comprometido", comprometido), http);
            throw new RegraNegocioException("O tecto nao pode ser inferior ao valor ja comprometido.");
        }

        o.setValorTotal(request.valorTotal());
        auditService.registrar(user, o.getInstituicao(), "EDITAR_ORCAMENTO", "ORCAMENTO", String.valueOf(o.getId()),
            "SUCESSO", "CRITICO", Map.of("valorTotal", request.valorTotal(), "comprometido", comprometido), http);

        return OrcamentoDtos.Response.from(o, comprometido);
    }

    public BigDecimal comprometido(Long idInst, int anoFiscal) {
        return despesas.sumByInstituicaoAnoEstados(idInst, anoFiscal, EstadoDespesa.estadosQueComprometemTecto());
    }
}
