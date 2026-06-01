package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import org.springframework.core.io.ByteArrayResource;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.http.ContentDisposition;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/receitas/autorizacoes-retroativas")
public class ReceitaAutorizacaoController {
    private final ReceitaAutorizacaoService service;

    public ReceitaAutorizacaoController(ReceitaAutorizacaoService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public Page<ReceitaAutorizacaoDtos.Response> listar(
        @AuthenticationPrincipal UserPrincipal principal,
        Pageable pageable
    ) {
        return service.listar(principal, pageable);
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR')")
    public ReceitaAutorizacaoDtos.Response solicitar(
        @Valid @RequestBody ReceitaAutorizacaoDtos.SolicitarRequest request,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        return service.solicitar(request, principal, http);
    }

    @PostMapping("/{id}/autorizar")
    @PreAuthorize("hasRole('AUDITOR')")
    public ResponseEntity<ByteArrayResource> autorizar(
        @PathVariable Long id,
        @AuthenticationPrincipal UserPrincipal principal,
        HttpServletRequest http
    ) {
        byte[] pdf = service.autorizarEGerarPdf(id, principal, http);
        return ResponseEntity.ok()
            .header(HttpHeaders.CONTENT_DISPOSITION, ContentDisposition.attachment()
                .filename("autorizacao-receita-retroativa-" + id + ".pdf")
                .build()
                .toString())
            .contentType(MediaType.APPLICATION_PDF)
            .body(new ByteArrayResource(pdf));
    }

    @GetMapping("/{id}/pdf")
    @PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
    public ResponseEntity<ByteArrayResource> pdf(
        @PathVariable Long id,
        @AuthenticationPrincipal UserPrincipal principal
    ) {
        byte[] pdf = service.gerarPdfAutorizacao(id, principal);
        return ResponseEntity.ok()
            .header(HttpHeaders.CONTENT_DISPOSITION, ContentDisposition.attachment()
                .filename("autorizacao-receita-retroativa-" + id + ".pdf")
                .build()
                .toString())
            .contentType(MediaType.APPLICATION_PDF)
            .body(new ByteArrayResource(pdf));
    }
}
