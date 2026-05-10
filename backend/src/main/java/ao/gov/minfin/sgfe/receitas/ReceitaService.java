package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.classificacoes.ClassificacaoEconomica;
import ao.gov.minfin.sgfe.classificacoes.ClassificacaoEconomicaRepository;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class ReceitaService {
    private final TransacaoReceitaRepository receitas;
    private final ClassificacaoEconomicaRepository classificacoes;
    private final InstituicaoRepository instituicoes;
    private final UserRepository users;
    private final FiscalYearService fiscalYear;
    private final RupeService rupeService;
    private final AuditService auditService;

    public ReceitaService(
        TransacaoReceitaRepository receitas,
        ClassificacaoEconomicaRepository classificacoes,
        InstituicaoRepository instituicoes,
        UserRepository users,
        FiscalYearService fiscalYear,
        RupeService rupeService,
        AuditService auditService
    ) {
        this.receitas = receitas;
        this.classificacoes = classificacoes;
        this.instituicoes = instituicoes;
        this.users = users;
        this.fiscalYear = fiscalYear;
        this.rupeService = rupeService;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<ReceitaDtos.Response> listar(UserPrincipal principal, Pageable pageable) {
        Page<TransacaoReceita> page = principal.isGestor()
            ? receitas.findByInstituicaoId(principal.idInst(), pageable)
            : receitas.findAll(pageable);
        return page.map(ReceitaDtos.Response::from);
    }

    @Transactional
    public ReceitaDtos.Response criar(ReceitaDtos.CriarRequest request, UserPrincipal principal, HttpServletRequest http) {
        fiscalYear.validarDataNoAnoFiscalCorrente(request.dataRegistro());
        User user = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        Long idInst = principal.isAdmin() ? request.idInst() : principal.idInst();
        if (idInst == null) {
            throw new RegraNegocioException("Admin deve informar a Unidade Orcamental da receita.");
        }

        Instituicao instituicao = instituicoes.findById(idInst)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));
        ClassificacaoEconomica classe = classificacoes.findById(request.idClasse())
            .orElseThrow(() -> new RegraNegocioException("Classificacao economica nao encontrada."));

        TransacaoReceita receita = new TransacaoReceita();
        receita.setFonteReceita(request.fonteReceita());
        receita.setCodigoRupe(rupeService.gerarCodigoRupe());
        receita.setDataRegistro(request.dataRegistro());
        receita.setValorArrecadado(request.valorArrecadado());
        receita.setClassificacao(classe);
        receita.setInstituicao(instituicao);
        TransacaoReceita salva = receitas.save(receita);

        auditService.registrar(user, instituicao, "CRIAR_RECEITA_RUPE", "RECEITA", String.valueOf(salva.getId()),
            "SUCESSO", "CRITICO", Map.of("rupe", salva.getCodigoRupe(), "valor", salva.getValorArrecadado()), http);

        return ReceitaDtos.Response.from(salva);
    }
}
