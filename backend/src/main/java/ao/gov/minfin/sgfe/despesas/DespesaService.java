package ao.gov.minfin.sgfe.despesas;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.classificacoes.ClassificacaoEconomica;
import ao.gov.minfin.sgfe.classificacoes.ClassificacaoEconomicaRepository;
import ao.gov.minfin.sgfe.common.AcessoNegadoException;
import ao.gov.minfin.sgfe.common.EstadoDespesa;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.orcamentos.Orcamento;
import ao.gov.minfin.sgfe.orcamentos.OrcamentoRepository;
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
public class DespesaService {
    private final TransacaoDespesaRepository despesas;
    private final OrcamentoRepository orcamentos;
    private final ClassificacaoEconomicaRepository classificacoes;
    private final UserRepository users;
    private final FiscalYearService fiscalYear;
    private final AuditService auditService;

    public DespesaService(
        TransacaoDespesaRepository despesas,
        OrcamentoRepository orcamentos,
        ClassificacaoEconomicaRepository classificacoes,
        UserRepository users,
        FiscalYearService fiscalYear,
        AuditService auditService
    ) {
        this.despesas = despesas;
        this.orcamentos = orcamentos;
        this.classificacoes = classificacoes;
        this.users = users;
        this.fiscalYear = fiscalYear;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<DespesaDtos.Response> listar(UserPrincipal principal, Pageable pageable) {
        Page<TransacaoDespesa> page = principal.isGestor()
            ? despesas.findByInstituicaoId(principal.idInst(), pageable)
            : despesas.findAll(pageable);
        return page.map(DespesaDtos.Response::from);
    }

    @Transactional
    public DespesaDtos.Response criar(DespesaDtos.CriarRequest request, UserPrincipal principal, HttpServletRequest http) {
        fiscalYear.validarDataNoAnoFiscalCorrente(request.dataRegistro());
        User user = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        Long idInst = resolverInstituicaoOperacao(request.idInst(), principal);
        int ano = request.dataRegistro().getYear();

        Orcamento orcamento = orcamentos.findForUpdate(idInst, ano)
            .orElseThrow(() -> {
                auditService.registrar(user, user.getInstituicao(), "CABIMENTACAO_BLOQUEADA", "DESPESA", null,
                    "FALHA", "ALERTA", Map.of("motivo", "sem_orcamento", "idInst", idInst, "anoFiscal", ano), http);
                return new RegraNegocioException("Cabimentacao bloqueada: a UO nao possui tecto orcamental para o ano fiscal.");
            });

        BigDecimal comprometido = despesas.sumByInstituicaoAnoEstados(idInst, ano, EstadoDespesa.estadosQueComprometemTecto());
        BigDecimal saldo = orcamento.getValorTotal().subtract(comprometido);
        if (request.valorBruto().compareTo(saldo) > 0) {
            auditService.registrar(user, orcamento.getInstituicao(), "CABIMENTACAO_BLOQUEADA", "DESPESA", null,
                "FALHA", "CRITICO", Map.of("valorSolicitado", request.valorBruto(), "saldoDisponivel", saldo), http);
            throw new RegraNegocioException("Cabimentacao bloqueada: valor superior ao saldo disponivel.");
        }

        ClassificacaoEconomica classe = classificacoes.findById(request.idClasse())
            .orElseThrow(() -> new RegraNegocioException("Classificacao economica nao encontrada."));

        TransacaoDespesa despesa = new TransacaoDespesa();
        despesa.setEstado(EstadoDespesa.PENDENTE_CABIMENTADA);
        despesa.setDescricao(request.descricao());
        despesa.setValorBruto(request.valorBruto());
        despesa.setDataRegistro(request.dataRegistro());
        despesa.setInstituicao(orcamento.getInstituicao());
        despesa.setUsuario(user);
        despesa.setClassificacao(classe);
        TransacaoDespesa salva = despesas.save(despesa);

        auditService.registrar(user, salva.getInstituicao(), "CRIAR_NCD", "DESPESA", String.valueOf(salva.getId()),
            "SUCESSO", "CRITICO", Map.of("valor", salva.getValorBruto(), "estado", salva.getEstado().name()), http);

        return DespesaDtos.Response.from(salva);
    }

    @Transactional
    public DespesaDtos.Response liquidar(Long id, UserPrincipal principal, HttpServletRequest http) {
        TransacaoDespesa despesa = despesas.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Despesa nao encontrada."));
        validarEscopo(principal, despesa.getInstituicao());
        User user = users.findById(principal.id()).orElseThrow();

        if (despesa.getEstado() != EstadoDespesa.PENDENTE_CABIMENTADA) {
            auditService.registrar(user, despesa.getInstituicao(), "LIQUIDACAO_BLOQUEADA", "DESPESA", String.valueOf(id),
                "FALHA", "ALERTA", Map.of("estadoAtual", despesa.getEstado().name()), http);
            throw new RegraNegocioException("Apenas despesas cabimentadas podem ser liquidadas.");
        }

        despesa.setEstado(EstadoDespesa.LIQUIDADA_APROVADA);
        auditService.registrar(user, despesa.getInstituicao(), "LIQUIDAR_NLD", "DESPESA", String.valueOf(id),
            "SUCESSO", "CRITICO", Map.of("estado", despesa.getEstado().name()), http);

        return DespesaDtos.Response.from(despesa);
    }

    @Transactional
    public DespesaDtos.Response pagar(Long id, UserPrincipal principal, HttpServletRequest http) {
        TransacaoDespesa despesa = despesas.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Despesa nao encontrada."));
        validarEscopo(principal, despesa.getInstituicao());
        User user = users.findById(principal.id()).orElseThrow();

        if (despesa.getEstado() != EstadoDespesa.LIQUIDADA_APROVADA) {
            auditService.registrar(user, despesa.getInstituicao(), "PAGAMENTO_BLOQUEADO", "DESPESA", String.valueOf(id),
                "FALHA", "ALERTA", Map.of("estadoAtual", despesa.getEstado().name()), http);
            throw new RegraNegocioException("Pagamento permitido apenas para despesas liquidadas.");
        }

        despesa.setEstado(EstadoDespesa.PAGA);
        auditService.registrar(user, despesa.getInstituicao(), "REGISTRAR_PAGAMENTO", "DESPESA", String.valueOf(id),
            "SUCESSO", "CRITICO", Map.of("valor", despesa.getValorBruto(), "estado", despesa.getEstado().name()), http);

        return DespesaDtos.Response.from(despesa);
    }

    private Long resolverInstituicaoOperacao(Long idInstRequest, UserPrincipal principal) {
        if (principal.isAdmin()) {
            if (idInstRequest == null) {
                throw new RegraNegocioException("Admin deve informar a Unidade Orcamental da operacao.");
            }
            return idInstRequest;
        }
        return principal.idInst();
    }

    private void validarEscopo(UserPrincipal principal, Instituicao instituicao) {
        if (!principal.isAdmin() && !instituicao.getId().equals(principal.idInst())) {
            throw new AcessoNegadoException("Nao pode operar despesas de outra Unidade Orcamental.");
        }
    }
}
