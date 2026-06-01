package ao.gov.minfin.sgfe.auth;

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

@Component
public class JwtAuthenticationFilter extends OncePerRequestFilter {
    private final JwtService jwtService;
    private final AuthCookieService authCookieService;
    private final SgfeUserDetailsService users;

    public JwtAuthenticationFilter(JwtService jwtService, AuthCookieService authCookieService, SgfeUserDetailsService users) {
        this.jwtService = jwtService;
        this.authCookieService = authCookieService;
        this.users = users;
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request, HttpServletResponse response, FilterChain chain)
        throws ServletException, IOException {
        String header = request.getHeader("Authorization");
        String token = extractToken(header, request);

        if (token == null) {
            chain.doFilter(request, response);
            return;
        }

        try {
            String email = jwtService.subject(token);

            if (email != null && SecurityContextHolder.getContext().getAuthentication() == null) {
                UserDetails details = users.loadUserByUsername(email);
                UsernamePasswordAuthenticationToken auth = new UsernamePasswordAuthenticationToken(
                    details,
                    null,
                    details.getAuthorities()
                );
                auth.setDetails(new WebAuthenticationDetailsSource().buildDetails(request));
                SecurityContextHolder.getContext().setAuthentication(auth);
            }
        } catch (RuntimeException ignored) {
            SecurityContextHolder.clearContext();
        }

        chain.doFilter(request, response);
    }

    private String extractToken(String header, HttpServletRequest request) {
        if (header != null && header.startsWith("Bearer ")) {
            return header.substring(7);
        }

        return authCookieService.resolveAccessToken(request).orElse(null);
    }
}
