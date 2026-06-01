package ao.gov.minfin.sgfe.users;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class UserService {
    private final UserRepository users;
    private final InstituicaoRepository instituicoes;
    private final PasswordEncoder passwordEncoder;
    private final AuditService auditService;

    public UserService(UserRepository users, InstituicaoRepository instituicoes, PasswordEncoder passwordEncoder, AuditService auditService) {
        this.users = users;
        this.instituicoes = instituicoes;
        this.passwordEncoder = passwordEncoder;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<UserDtos.Response> listar(Pageable pageable) {
        return users.findAll(pageable).map(UserDtos.Response::from);
    }

    @Transactional(readOnly = true)
    public UserDtos.Response me(UserPrincipal principal) {
        return users.findById(principal.id()).map(UserDtos.Response::from)
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
    }

    @Transactional
    public UserDtos.Response criar(UserDtos.CreateRequest request, UserPrincipal principal, HttpServletRequest http) {
        if (users.existsByEmailIgnoreCase(request.email())) {
            throw new RegraNegocioException("Email ja esta em uso.");
        }
        if (users.existsByUsername(request.username())) {
            throw new RegraNegocioException("Username ja esta em uso.");
        }
        Instituicao inst = instituicoes.findById(request.idInst())
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));

        User user = new User();
        user.setNome(request.nome());
        user.setUsername(request.username());
        user.setEmail(request.email());
        user.setPasswordHash(passwordEncoder.encode(request.password()));
        user.setRole(request.role());
        user.setStatus(request.status());
        user.setInstituicao(inst);
        User salvo = users.save(user);

        User actor = users.findById(principal.id()).orElse(null);
        auditService.registrar(actor, inst, "CRIAR_UTILIZADOR", "USER", String.valueOf(salvo.getId()),
            "SUCESSO", "CRITICO", Map.of("role", salvo.getRole().name(), "status", salvo.getStatus().name()), http);

        return UserDtos.Response.from(salvo);
    }

    @Transactional
    public UserDtos.Response atualizar(Long id, UserDtos.UpdateRequest request, UserPrincipal principal, HttpServletRequest http) {
        User user = users.findById(id).orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        Instituicao inst = instituicoes.findById(request.idInst())
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));

        users.findByEmailIgnoreCase(request.email())
            .filter(existing -> !existing.getId().equals(id))
            .ifPresent(existing -> { throw new RegraNegocioException("Email ja esta em uso."); });

        user.setNome(request.nome());
        user.setUsername(request.username());
        user.setEmail(request.email());
        user.setRole(request.role());
        user.setStatus(request.status());
        user.setInstituicao(inst);

        User actor = users.findById(principal.id()).orElse(null);
        auditService.registrar(actor, inst, "EDITAR_UTILIZADOR", "USER", String.valueOf(user.getId()),
            "SUCESSO", "CRITICO", Map.of("role", user.getRole().name(), "status", user.getStatus().name()), http);

        return UserDtos.Response.from(user);
    }

    @Transactional
    public UserDtos.Response alterarRoleStatus(Long id, UserDtos.RoleStatusRequest request, UserPrincipal principal, HttpServletRequest http) {
        User user = users.findById(id).orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        if (request.role() != null) {
            user.setRole(request.role());
        }
        if (request.status() != null) {
            user.setStatus(request.status());
        }

        User actor = users.findById(principal.id()).orElse(null);
        auditService.registrar(actor, user.getInstituicao(), "ALTERAR_ROLE_STATUS", "USER", String.valueOf(user.getId()),
            "SUCESSO", "CRITICO", Map.of("role", user.getRole().name(), "status", user.getStatus().name()), http);

        return UserDtos.Response.from(user);
    }

    @Transactional
    public UserDtos.Response atualizarPerfil(UserDtos.ProfileRequest request, UserPrincipal principal, HttpServletRequest http) {
        User user = users.findById(principal.id()).orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        users.findByEmailIgnoreCase(request.email())
            .filter(existing -> !existing.getId().equals(user.getId()))
            .ifPresent(existing -> { throw new RegraNegocioException("Email ja esta em uso."); });

        user.setNome(request.nome());
        user.setEmail(request.email());

        auditService.registrar(user, user.getInstituicao(), "EDITAR_PERFIL", "USER", String.valueOf(user.getId()),
            "SUCESSO", "INFO", Map.of(), http);

        return UserDtos.Response.from(user);
    }

    @Transactional
    public void alterarSenha(UserDtos.PasswordRequest request, UserPrincipal principal, HttpServletRequest http) {
        User user = users.findById(principal.id()).orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        if (!passwordEncoder.matches(request.currentPassword(), user.getPasswordHash())) {
            auditService.registrar(user, user.getInstituicao(), "ALTERAR_SENHA_BLOQUEADO", "USER", String.valueOf(user.getId()),
                "FALHA", "ALERTA", Map.of("motivo", "senha_atual_invalida"), http);
            throw new RegraNegocioException("Palavra-passe actual invalida.");
        }

        user.setPasswordHash(passwordEncoder.encode(request.newPassword()));
        auditService.registrar(user, user.getInstituicao(), "ALTERAR_SENHA", "USER", String.valueOf(user.getId()),
            "SUCESSO", "CRITICO", Map.of(), http);
    }
}
