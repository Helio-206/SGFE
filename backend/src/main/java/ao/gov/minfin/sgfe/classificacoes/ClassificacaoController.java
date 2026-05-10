package ao.gov.minfin.sgfe.classificacoes;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/classificacoes")
public class ClassificacaoController {
    private final ClassificacaoEconomicaRepository classificacoes;
    private final UserRepository users;
    private final AuditService auditService;

    public ClassificacaoController(ClassificacaoEconomicaRepository classificacoes, UserRepository users, AuditService auditService) {
        this.classificacoes = classificacoes;
        this.users = users;
        this.auditService = auditService;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public Page<ClassificacaoDtos.Response> listar(Pageable pageable) {
        return classificacoes.findAll(pageable).map(ClassificacaoDtos.Response::from);
    }

    @PostMapping
    @Transactional
    @PreAuthorize("hasRole('ADMIN')")
    public ClassificacaoDtos.Response criar(
        @Valid @RequestBody ClassificacaoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        if (classificacoes.existsByCodigo(request.codigo())) {
            throw new RegraNegocioException("Codigo de classificacao ja existente.");
        }

        ClassificacaoEconomica c = new ClassificacaoEconomica();
        c.setDescricao(request.descricao());
        c.setCodigo(request.codigo());
        c.setTipo(request.tipo());
        ClassificacaoEconomica salva = classificacoes.save(c);

        User user = users.findById(principal.id()).orElse(null);
        auditService.registrar(user, user != null ? user.getInstituicao() : null, "CRIAR_CLASSIFICACAO", "CLASSIFICACAO", String.valueOf(salva.getId()),
            "SUCESSO", "INFO", Map.of("codigo", salva.getCodigo()), http);

        return ClassificacaoDtos.Response.from(salva);
    }

    @PutMapping("/{id}")
    @Transactional
    @PreAuthorize("hasRole('ADMIN')")
    public ClassificacaoDtos.Response atualizar(
        @PathVariable Long id,
        @Valid @RequestBody ClassificacaoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        ClassificacaoEconomica c = classificacoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Classificacao nao encontrada."));
        c.setDescricao(request.descricao());
        c.setCodigo(request.codigo());
        c.setTipo(request.tipo());

        User user = users.findById(principal.id()).orElse(null);
        auditService.registrar(user, user != null ? user.getInstituicao() : null, "EDITAR_CLASSIFICACAO", "CLASSIFICACAO", String.valueOf(c.getId()),
            "SUCESSO", "INFO", Map.of("codigo", c.getCodigo()), http);

        return ClassificacaoDtos.Response.from(c);
    }
}
