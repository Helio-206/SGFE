package ao.gov.minfin.sgfe.users;

import ao.gov.minfin.sgfe.auth.UserPrincipal;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.Valid;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/users")
public class UserController {
    private final UserService service;

    public UserController(UserService service) {
        this.service = service;
    }

    @GetMapping
    @PreAuthorize("hasRole('ADMIN')")
    public Page<UserDtos.Response> listar(Pageable pageable) {
        return service.listar(pageable);
    }

    @PostMapping
    @PreAuthorize("hasRole('ADMIN')")
    public UserDtos.Response criar(@Valid @RequestBody UserDtos.CreateRequest request, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.criar(request, principal, http);
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    public UserDtos.Response atualizar(@PathVariable Long id, @Valid @RequestBody UserDtos.UpdateRequest request, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.atualizar(id, request, principal, http);
    }

    @PatchMapping("/{id}/role-status")
    @PreAuthorize("hasRole('ADMIN')")
    public UserDtos.Response alterarRoleStatus(@PathVariable Long id, @Valid @RequestBody UserDtos.RoleStatusRequest request, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.alterarRoleStatus(id, request, principal, http);
    }

    @GetMapping("/me")
    public UserDtos.Response me(@AuthenticationPrincipal UserPrincipal principal) {
        return service.me(principal);
    }

    @PutMapping("/me")
    public UserDtos.Response atualizarPerfil(@Valid @RequestBody UserDtos.ProfileRequest request, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        return service.atualizarPerfil(request, principal, http);
    }

    @PatchMapping("/me/password")
    public ResponseEntity<Void> alterarSenha(@Valid @RequestBody UserDtos.PasswordRequest request, @AuthenticationPrincipal UserPrincipal principal, HttpServletRequest http) {
        service.alterarSenha(request, principal, http);
        return ResponseEntity.noContent().build();
    }
}
