package ao.gov.minfin.sgfe.instituicoes;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/instituicoes")
public class InstituicaoController {
    private final InstituicaoService service;

    public InstituicaoController(InstituicaoService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','AUDITOR')")
    public Page<InstituicaoDtos.Response> listar(Pageable pageable) {
        return service.listar(pageable);
    }

    @GetMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN','AUDITOR')")
    public InstituicaoDtos.Response obter(@PathVariable Long id) {
        return service.obter(id);
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    public InstituicaoDtos.Response criar(
        @Valid @RequestBody InstituicaoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.criar(request, principal.id(), http);
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    public InstituicaoDtos.Response atualizar(
        @PathVariable Long id,
        @Valid @RequestBody InstituicaoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.atualizar(id, request, principal.id(), http);
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    public void remover(@PathVariable Long id, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        service.remover(id, principal.id(), http);
    }
}
