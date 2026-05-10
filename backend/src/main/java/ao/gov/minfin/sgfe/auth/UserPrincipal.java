package ao.gov.minfin.sgfe.auth;

import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.users.User;
import java.util.Collection;
import java.util.List;
import org.springframework.security.core.GrantedAuthority;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.userdetails.UserDetails;

public class UserPrincipal implements UserDetails {
    private final Long id;
    private final Long idInst;
    private final String nome;
    private final String email;
    private final String passwordHash;
    private final Role role;
    private final UserStatus status;

    private UserPrincipal(User user) {
        this.id = user.getId();
        this.idInst = user.getInstituicao().getId();
        this.nome = user.getNome();
        this.email = user.getEmail();
        this.passwordHash = user.getPasswordHash();
        this.role = user.getRole();
        this.status = user.getStatus();
    }

    public static UserPrincipal from(User user) {
        return new UserPrincipal(user);
    }

    public Long id() { return id; }
    public Long idInst() { return idInst; }
    public String nome() { return nome; }
    public Role role() { return role; }

    public boolean isAdmin() { return role == Role.ADMIN; }
    public boolean isGestor() { return role == Role.GESTOR; }
    public boolean isAuditor() { return role == Role.AUDITOR; }

    @Override
    public Collection<? extends GrantedAuthority> getAuthorities() {
        return List.of(new SimpleGrantedAuthority("ROLE_" + role.name()));
    }

    @Override
    public String getPassword() { return passwordHash; }

    @Override
    public String getUsername() { return email; }

    @Override
    public boolean isAccountNonExpired() { return true; }

    @Override
    public boolean isAccountNonLocked() { return status == UserStatus.ATIVO; }

    @Override
    public boolean isCredentialsNonExpired() { return true; }

    @Override
    public boolean isEnabled() { return status == UserStatus.ATIVO; }
}
