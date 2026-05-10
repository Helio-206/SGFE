package ao.gov.minfin.sgfe.auth;

import static org.assertj.core.api.Assertions.assertThat;

import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.users.User;
import io.jsonwebtoken.Claims;
import org.junit.jupiter.api.Test;

class JwtServiceTest {
    @Test
    void accessTokenCarregaClaimsEssenciaisParaRbacEUo() {
        JwtService jwtService = new JwtService("0123456789abcdef0123456789abcdef", 15);
        UserPrincipal principal = UserPrincipal.from(utilizadorGestor());

        String token = jwtService.gerarAccessToken(principal);
        Claims claims = jwtService.claims(token);

        assertThat(jwtService.subject(token)).isEqualTo("gestor@sgfe.gov.ao");
        assertThat(claims.get("uid", Integer.class)).isEqualTo(9);
        assertThat(claims.get("idInst", Integer.class)).isEqualTo(77);
        assertThat(claims.get("role", String.class)).isEqualTo("GESTOR");
        assertThat(claims.getExpiration()).isAfter(claims.getIssuedAt());
    }

    private User utilizadorGestor() {
        Instituicao instituicao = new Instituicao();
        instituicao.setId(77L);
        instituicao.setNome("Unidade Orcamental de Teste");

        User user = new User();
        user.setId(9L);
        user.setInstituicao(instituicao);
        user.setNome("Gestor Teste");
        user.setEmail("gestor@sgfe.gov.ao");
        user.setPasswordHash("$2a$12$hash");
        user.setRole(Role.GESTOR);
        user.setStatus(UserStatus.ATIVO);
        return user;
    }
}
