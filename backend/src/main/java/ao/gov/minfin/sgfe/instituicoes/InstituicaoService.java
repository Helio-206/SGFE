package ao.gov.minfin.sgfe.instituicoes;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.HashMap;
import java.util.Locale;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.transaction.support.TransactionSynchronization;
import org.springframework.transaction.support.TransactionSynchronizationManager;

@Service
public class InstituicaoService {
    private static final Logger log = LoggerFactory.getLogger(InstituicaoService.class);

    private final InstituicaoRepository instituicoes;
    private final UserRepository users;
    private final PasswordEncoder passwordEncoder;
    private final AuditService auditService;

    public InstituicaoService(
        InstituicaoRepository instituicoes,
        UserRepository users,
        PasswordEncoder passwordEncoder,
        AuditService auditService
    ) {
        this.instituicoes = instituicoes;
        this.users = users;
        this.passwordEncoder = passwordEncoder;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<InstituicaoDtos.Response> listar(Pageable pageable) {
        return instituicoes.findAll(pageable).map(InstituicaoDtos.Response::from);
    }

    @Transactional
    public InstituicaoDtos.Response criar(InstituicaoDtos.Request request, Long idUser, HttpServletRequest http) {
        if (instituicoes.existsByCodigo(request.codigo())) {
            throw new RegraNegocioException("O codigo da Unidade Orcamental ja esta em uso.");
        }
        String emailResponsavel = normalizarOpcional(request.emailResponsavel());
        String senhaResponsavel = normalizarOpcional(request.senhaResponsavel());
        if (emailResponsavel == null || senhaResponsavel == null) {
            throw new RegraNegocioException("Informe o email e a senha inicial do responsavel pela UO.");
        }
        if (emailResponsavel != null && users.existsByEmailIgnoreCase(emailResponsavel)) {
            throw new RegraNegocioException("Ja existe um utilizador com o email do responsavel informado.");
        }

        Instituicao inst = new Instituicao();
        inst.setNome(request.nome());
        inst.setTipo(request.tipo());
        inst.setCodigo(request.codigo());
        inst.setResponsavel(request.responsavel());
        Instituicao salva = instituicoes.save(inst);
        User responsavel = emailResponsavel != null
            ? criarUtilizadorResponsavel(salva, request.responsavel(), emailResponsavel, senhaResponsavel)
            : null;

        User user = users.findById(idUser).orElse(null);
        registrarCriacaoAposCommit(user, salva, responsavel, http);

        return InstituicaoDtos.Response.from(salva);
    }

    @Transactional
    public InstituicaoDtos.Response atualizar(Long id, InstituicaoDtos.Request request, Long idUser, HttpServletRequest http) {
        Instituicao inst = instituicoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));

        instituicoes.findByCodigo(request.codigo())
            .filter(existente -> !existente.getId().equals(id))
            .ifPresent(existente -> {
                throw new RegraNegocioException("O codigo da Unidade Orcamental ja esta em uso.");
            });

        inst.setNome(request.nome());
        inst.setTipo(request.tipo());
        inst.setCodigo(request.codigo());
        inst.setResponsavel(request.responsavel());

        User user = users.findById(idUser).orElse(null);
        auditService.registrar(user, inst, "EDITAR_INSTITUICAO", "INSTITUICAO", String.valueOf(inst.getId()),
            "SUCESSO", "INFO", Map.of("codigo", inst.getCodigo()), http);

        return InstituicaoDtos.Response.from(inst);
    }

    @Transactional(readOnly = true)
    public InstituicaoDtos.Response obter(Long id) {
        Instituicao inst = instituicoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));
        return InstituicaoDtos.Response.from(inst);
    }

    @Transactional
    public void remover(Long id, Long idUser, HttpServletRequest http) {
        Instituicao inst = instituicoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));
        
        User user = users.findById(idUser).orElse(null);
        instituicoes.deleteById(id);
        auditService.registrar(user, inst, "REMOVER_INSTITUICAO", "INSTITUICAO", String.valueOf(id),
            "SUCESSO", "INFO", Map.of("codigo", inst.getCodigo()), http);
    }

    private User criarUtilizadorResponsavel(Instituicao instituicao, String nome, String email, String senha) {
        User user = new User();
        user.setNome(nome);
        user.setUsername(gerarUsername(email));
        user.setEmail(email);
        user.setPasswordHash(passwordEncoder.encode(senha));
        user.setRole(Role.GESTOR);
        user.setStatus(UserStatus.ATIVO);
        user.setInstituicao(instituicao);
        return users.save(user);
    }

    private String gerarUsername(String email) {
        String localPart = email.split("@", 2)[0].toLowerCase(Locale.ROOT)
            .replaceAll("[^a-z0-9._-]", "-")
            .replaceAll("-+", "-")
            .replaceAll("(^-|-$)", "");
        String base = localPart.isBlank() ? "gestor" : localPart;
        String candidate = limitar(base, 50);
        int counter = 2;

        while (users.existsByUsername(candidate)) {
            String suffix = "-" + counter++;
            candidate = limitar(base, 50 - suffix.length()) + suffix;
        }

        return candidate;
    }

    private String limitar(String value, int maxLength) {
        if (value.length() <= maxLength) {
            return value;
        }
        return value.substring(0, maxLength);
    }

    private String normalizarOpcional(String value) {
        if (value == null || value.trim().isEmpty()) {
            return null;
        }
        return value.trim();
    }

    private void registrarCriacaoAposCommit(User user, Instituicao instituicao, User responsavel, HttpServletRequest http) {
        if (!TransactionSynchronizationManager.isSynchronizationActive()) {
            registrarCriacao(user, instituicao, responsavel, http);
            return;
        }

        TransactionSynchronizationManager.registerSynchronization(new TransactionSynchronization() {
            @Override
            public void afterCommit() {
                registrarCriacao(user, instituicao, responsavel, http);
            }
        });
    }

    private void registrarCriacao(User user, Instituicao instituicao, User responsavel, HttpServletRequest http) {
        try {
            Map<String, Object> contexto = new HashMap<>();
            contexto.put("codigo", instituicao.getCodigo());
            if (responsavel != null) {
                contexto.put("idUserResponsavel", responsavel.getId());
                contexto.put("emailResponsavel", responsavel.getEmail());
            }
            auditService.registrar(user, instituicao, "CRIAR_INSTITUICAO", "INSTITUICAO", String.valueOf(instituicao.getId()),
                "SUCESSO", "INFO", contexto, http);
        } catch (RuntimeException ex) {
            log.warn("Nao foi possivel registrar auditoria de criacao da instituicao {}", instituicao.getCodigo(), ex);
        }
    }
}
