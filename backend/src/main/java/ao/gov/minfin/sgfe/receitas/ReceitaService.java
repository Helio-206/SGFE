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
import java.time.Instant;
import java.time.LocalDate;
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
    private final AutorizacaoReceitaRetroativaRepository autorizacoesRetroativas;

    public ReceitaService(
        TransacaoReceitaRepository receitas,
        ClassificacaoEconomicaRepository classificacoes,
        InstituicaoRepository instituicoes,
        UserRepository users,
        FiscalYearService fiscalYear,
        RupeService rupeService,
        AuditService auditService,
        AutorizacaoReceitaRetroativaRepository autorizacoesRetroativas
    ) {
        this.receitas = receitas;
        this.classificacoes = classificacoes;
        this.instituicoes = instituicoes;
        this.users = users;
        this.fiscalYear = fiscalYear;
        this.rupeService = rupeService;
        this.auditService = auditService;
        this.autorizacoesRetroativas = autorizacoesRetroativas;
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
        LocalDate hoje = LocalDate.now(ReceitaAutorizacaoService.ZONA_SISTEMA);
        if (request.dataRegistro().isAfter(hoje)) {
            throw new RegraNegocioException("A data da receita deve ser a data corrente. Datas futuras nao sao permitidas.");
        }
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
        AutorizacaoReceitaRetroativa autorizacaoRetroativa = validarAutorizacaoRetroativa(request, principal, idInst, hoje);

        TransacaoReceita receita = new TransacaoReceita();
        receita.setFonteReceita(request.fonteReceita());
        receita.setCodigoRupe(rupeService.gerarCodigoRupe());
        receita.setDataRegistro(request.dataRegistro());
        receita.setValorArrecadado(request.valorArrecadado());
        receita.setClassificacao(classe);
        receita.setInstituicao(instituicao);
        TransacaoReceita salva = receitas.save(receita);

        if (autorizacaoRetroativa != null) {
            autorizacaoRetroativa.setStatus(AutorizacaoReceitaRetroativaStatus.UTILIZADA);
            autorizacaoRetroativa.setReceita(salva);
            autorizacaoRetroativa.setUtilizadoAt(Instant.now());
            autorizacoesRetroativas.save(autorizacaoRetroativa);
        }

        auditService.registrar(user, instituicao, "CRIAR_RECEITA_RUPE", "RECEITA", String.valueOf(salva.getId()),
            "SUCESSO", "CRITICO", Map.of(
                "rupe", salva.getCodigoRupe(),
                "valor", salva.getValorArrecadado(),
                "dataRegistro", salva.getDataRegistro(),
                "autorizacaoRetroativa", autorizacaoRetroativa != null ? autorizacaoRetroativa.getId() : ""
            ), http);

        return ReceitaDtos.Response.from(salva);
    }

    @Transactional(readOnly = true)
    public ReceitaDtos.Response obter(Long id, UserPrincipal principal) {
        TransacaoReceita receita = receitas.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Receita nao encontrada."));
        if (principal.isGestor() && !receita.getInstituicao().getId().equals(principal.idInst())) {
            throw new RegraNegocioException("Nao pode acessar receita de outra Unidade Orcamental.");
        }
        return ReceitaDtos.Response.from(receita);
    }

    @Transactional
    public void remover(Long id, UserPrincipal principal, HttpServletRequest http) {
        TransacaoReceita receita = receitas.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Receita nao encontrada."));
        if (principal.isGestor() && !receita.getInstituicao().getId().equals(principal.idInst())) {
            throw new RegraNegocioException("Nao pode remover receita de outra Unidade Orcamental.");
        }
        User user = users.findById(principal.id()).orElse(null);
        receitas.deleteById(id);
        auditService.registrar(user, receita.getInstituicao(), "REMOVER_RECEITA_RUPE", "RECEITA", String.valueOf(id),
            "SUCESSO", "INFO", Map.of("rupe", receita.getCodigoRupe()), http);
    }

    private AutorizacaoReceitaRetroativa validarAutorizacaoRetroativa(
        ReceitaDtos.CriarRequest request,
        UserPrincipal principal,
        Long idInst,
        LocalDate hoje
    ) {
        if (request.dataRegistro().isEqual(hoje)) {
            if (request.idAutorizacaoRetroativa() != null) {
                throw new RegraNegocioException("Autorizacao retroativa nao se aplica a data corrente.");
            }
            return null;
        }

        if (!request.dataRegistro().isBefore(hoje)) {
            throw new RegraNegocioException("A data da receita deve ser a data corrente.");
        }
        if (!principal.isAdmin()) {
            throw new RegraNegocioException("Receita retroativa so pode ser criada pelo Admin com autorizacao do Auditor.");
        }
        if (request.idAutorizacaoRetroativa() == null) {
            throw new RegraNegocioException("Criacao de receita retroativa exige autorizacao do Auditor.");
        }

        AutorizacaoReceitaRetroativa autorizacao = autorizacoesRetroativas.findById(request.idAutorizacaoRetroativa())
            .orElseThrow(() -> new RegraNegocioException("Autorizacao retroativa nao encontrada."));
        if (autorizacao.getStatus() != AutorizacaoReceitaRetroativaStatus.AUTORIZADA) {
            throw new RegraNegocioException("Autorizacao retroativa ainda nao esta autorizada ou ja foi utilizada.");
        }
        if (!autorizacao.getInstituicao().getId().equals(idInst)) {
            throw new RegraNegocioException("Autorizacao retroativa pertence a outra Unidade Orcamental.");
        }
        if (!autorizacao.getDataRegistro().equals(request.dataRegistro())) {
            throw new RegraNegocioException("Data da receita difere da data autorizada pelo Auditor.");
        }
        return autorizacao;
    }
}
