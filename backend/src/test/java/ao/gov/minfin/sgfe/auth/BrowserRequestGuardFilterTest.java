package ao.gov.minfin.sgfe.auth;

import static org.assertj.core.api.Assertions.assertThat;

import org.junit.jupiter.api.Test;
import org.springframework.mock.web.MockFilterChain;
import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.mock.web.MockHttpServletResponse;

class BrowserRequestGuardFilterTest {
    private final BrowserRequestGuardFilter filter = new BrowserRequestGuardFilter();

    @Test
    void rejeitaPostApiSemCabecalhoDeIntencao() throws Exception {
        MockHttpServletRequest request = new MockHttpServletRequest("POST", "/api/auth/logout");
        MockHttpServletResponse response = new MockHttpServletResponse();

        filter.doFilter(request, response, new MockFilterChain());

        assertThat(response.getStatus()).isEqualTo(403);
        assertThat(response.getContentAsString()).contains("Pedido rejeitado");
    }

    @Test
    void permitePostApiComCabecalhoDeIntencao() throws Exception {
        MockHttpServletRequest request = new MockHttpServletRequest("POST", "/api/auth/logout");
        request.addHeader("X-Requested-With", "XMLHttpRequest");
        MockHttpServletResponse response = new MockHttpServletResponse();

        filter.doFilter(request, response, new MockFilterChain());

        assertThat(response.getStatus()).isEqualTo(200);
    }

    @Test
    void permiteGetApiSemCabecalhoExtra() throws Exception {
        MockHttpServletRequest request = new MockHttpServletRequest("GET", "/api/dashboard");
        MockHttpServletResponse response = new MockHttpServletResponse();

        filter.doFilter(request, response, new MockFilterChain());

        assertThat(response.getStatus()).isEqualTo(200);
    }
}
