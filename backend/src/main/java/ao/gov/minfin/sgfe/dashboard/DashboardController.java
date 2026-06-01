package ao.gov.minfin.sgfe.dashboard;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import java.util.Map;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/dashboard")
@PreAuthorize("isAuthenticated()")
public class DashboardController {
    private final DashboardService service;

    public DashboardController(DashboardService service) {
        this.service = service;
    }

    @GetMapping
    public Map<String, Object> dados(@AuthenticationPrincipal UserPrincipal principal) {
        return service.dados(principal);
    }
}
