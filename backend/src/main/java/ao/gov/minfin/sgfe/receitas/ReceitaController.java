package ao.gov.minfin.sgfe.receitas;

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
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/receitas")
public class ReceitaController {
    private final ReceitaService service;

    public ReceitaController(ReceitaService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public Page<ReceitaDtos.Response> listar(@AuthenticationPrincipal UserPrincipal principal, Pageable pageable) {
        return service.listar(principal, pageable);
    }

    @GetMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public ReceitaDtos.Response obter(@PathVariable Long id, @AuthenticationPrincipal UserPrincipal principal) {
        return service.obter(id, principal);
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public ReceitaDtos.Response criar(
        @Valid @RequestBody ReceitaDtos.CriarRequest request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.criar(request, principal, http);
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public void remover(@PathVariable Long id, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        service.remover(id, principal, http);
    }
}
