package ao.gov.minfin.sgfe.despesas;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/despesas")
public class DespesaController {
    private final DespesaService service;

    public DespesaController(DespesaService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public Page<DespesaDtos.Response> listar(@AuthenticationPrincipal UserPrincipal principal, Pageable pageable) {
        return service.listar(principal, pageable);
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public DespesaDtos.Response criar(
        @Valid @RequestBody DespesaDtos.CriarRequest request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.criar(request, principal, http);
    }

    @PostMapping("/{id}/liquidar")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public DespesaDtos.Response liquidar(@PathVariable Long id, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.liquidar(id, principal, http);
    }

    @PostMapping("/{id}/pagar")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public DespesaDtos.Response pagar(@PathVariable Long id, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.pagar(id, principal, http);
    }
}
