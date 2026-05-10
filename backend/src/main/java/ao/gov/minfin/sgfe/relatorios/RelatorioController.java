package ao.gov.minfin.sgfe.relatorios;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import jakarta.servlet.http.HttpServletRequest;
import java.time.LocalDate;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/relatorios")
@PreAuthorize("hasAnyRole('ADMIN','GESTOR','AUDITOR')")
public class RelatorioController {
    private final RelatorioService service;

    public RelatorioController(RelatorioService service) {
        this.service = service;
    }

    @GetMapping("/exportar/resumo-financeiro.pdf")
    public ResponseEntity<byte[]> resumoFinanceiro(@AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return arquivoPdf("resumo-financeiro.pdf", service.resumoFinanceiroPdf(principal, http));
    }

    @GetMapping("/exportar/despesa-por-natureza.pdf")
    public ResponseEntity<byte[]> despesaPorNatureza(@AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return arquivoPdf("despesa-por-natureza.pdf", service.despesaPorNaturezaPdf(principal, http));
    }

    @GetMapping("/exportar/receitas-rupe.xlsx")
    public ResponseEntity<byte[]> receitasRupe(
        @AuthenticationPrincipal UserPrincipal principal,
        @RequestParam(required = false) @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate inicio,
        @RequestParam(required = false) @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate fim,
        HttpServletRequest http
    ) {
        return ResponseEntity.ok()
            .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"receitas-rupe.xlsx\"")
            .contentType(MediaType.parseMediaType("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
            .body(service.receitasRupeXlsx(principal, inicio, fim, http));
    }

    private ResponseEntity<byte[]> arquivoPdf(String filename, byte[] bytes) {
        return ResponseEntity.ok()
            .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + filename + "\"")
            .contentType(MediaType.APPLICATION_PDF)
            .body(bytes);
    }
}
