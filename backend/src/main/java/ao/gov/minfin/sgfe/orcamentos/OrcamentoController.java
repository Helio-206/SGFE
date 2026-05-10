package ao.gov.minfin.sgfe.orcamentos;

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
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/orcamentos")
public class OrcamentoController {
    private final OrcamentoService service;

    public OrcamentoController(OrcamentoService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','AUDITOR')")
    public Page<OrcamentoDtos.Response> listar(Pageable pageable) {
        return service.listar(pageable);
    }

    @GetMapping("/meu-tecto")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public OrcamentoDtos.Response meuTecto(@AuthenticationPrincipal UserPrincipal principal) {
        return service.consultarTecto(principal);
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    public OrcamentoDtos.Response criar(
        @Valid @RequestBody OrcamentoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.criar(request, principal, http);
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    public OrcamentoDtos.Response atualizar(
        @PathVariable Long id,
        @Valid @RequestBody OrcamentoDtos.Request request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.atualizar(id, request, principal, http);
    }
}
