package ao.gov.minfin.sgfe.auditoria;

import java.time.Instant;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/auditoria")
@PreAuthorize("hasAnyRole('ADMIN','AUDITOR')")
public class AuditoriaController {
    private final AuditoriaService service;

    public AuditoriaController(AuditoriaService service) {
        this.service = service;
    }

    @GetMapping("/logs")
    public Page<AuditoriaDtos.Response> logs(
        @RequestParam(required = false) Long idUser,
        @RequestParam(required = false) String acao,
        @RequestParam(required = false) String entidade,
        @RequestParam(required = false) String ip,
        @RequestParam(required = false) Instant inicio,
        @RequestParam(required = false) Instant fim,
        Pageable pageable
    ) {
        return service.filtrar(idUser, acao, entidade, ip, inicio, fim, pageable);
    }
}
