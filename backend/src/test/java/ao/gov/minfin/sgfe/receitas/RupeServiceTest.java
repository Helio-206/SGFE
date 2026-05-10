package ao.gov.minfin.sgfe.receitas;

import static org.assertj.core.api.Assertions.assertThat;
import static org.assertj.core.api.Assertions.assertThatThrownBy;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

import ao.gov.minfin.sgfe.common.RegraNegocioException;
import org.junit.jupiter.api.Test;

class RupeServiceTest {
    @Test
    void geraRupeNumericaComVinteDigitosEValidaUnicidade() {
        TransacaoReceitaRepository repository = mock(TransacaoReceitaRepository.class);
        when(repository.existsByCodigoRupe(anyString())).thenReturn(false);

        String codigo = new RupeService(repository).gerarCodigoRupe();

        assertThat(codigo).matches("\\d{20}");
        verify(repository).existsByCodigoRupe(codigo);
    }

    @Test
    void falhaQuandoNaoConsegueGerarCodigoUnico() {
        TransacaoReceitaRepository repository = mock(TransacaoReceitaRepository.class);
        when(repository.existsByCodigoRupe(anyString())).thenReturn(true);

        assertThatThrownBy(() -> new RupeService(repository).gerarCodigoRupe())
            .isInstanceOf(RegraNegocioException.class)
            .hasMessageContaining("RUPE unica");
    }
}
