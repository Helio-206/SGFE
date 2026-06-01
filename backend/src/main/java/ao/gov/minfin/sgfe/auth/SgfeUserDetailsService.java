package ao.gov.minfin.sgfe.auth;

import ao.gov.minfin.sgfe.users.UserRepository;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class SgfeUserDetailsService implements UserDetailsService {
    private final UserRepository users;

    public SgfeUserDetailsService(UserRepository users) {
        this.users = users;
    }

    @Override
    @Transactional(readOnly = true)
    public UserDetails loadUserByUsername(String username) throws UsernameNotFoundException {
        return users.findByEmailIgnoreCase(username)
            .map(UserPrincipal::from)
            .orElseThrow(() -> new UsernameNotFoundException("Utilizador nao encontrado."));
    }
}
