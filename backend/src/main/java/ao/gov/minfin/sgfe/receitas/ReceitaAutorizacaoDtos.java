package ao.gov.minfin.sgfe.receitas;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import java.time.Instant;
import java.time.LocalDate;

public final class ReceitaAutorizacaoDtos {
    private ReceitaAutorizacaoDtos() {}

    public record SolicitarRequest(
        Long idInst,
        @NotNull LocalDate dataRegistro,
        @NotBlank @Size(max = 255) String motivo
    ) {}

    public record Response(
        Long id,
        Long idInst,
        String codigoUo,
        String instituicao,
        String solicitante,
        String auditor,
        LocalDate dataRegistro,
        Short diasAtraso,
        String motivo,
        AutorizacaoReceitaRetroativaStatus status,
        Long idReceita,
        Instant createdAt,
        Instant autorizadoAt,
        Instant utilizadoAt
    ) {
        public static Response from(AutorizacaoReceitaRetroativa autorizacao) {
            return new Response(
                autorizacao.getId(),
                autorizacao.getInstituicao().getId(),
                autorizacao.getInstituicao().getCodigo(),
                autorizacao.getInstituicao().getNome(),
                autorizacao.getSolicitante().getNome(),
                autorizacao.getAuditor() != null ? autorizacao.getAuditor().getNome() : null,
                autorizacao.getDataRegistro(),
                autorizacao.getDiasAtraso(),
                autorizacao.getMotivo(),
                autorizacao.getStatus(),
                autorizacao.getReceita() != null ? autorizacao.getReceita().getId() : null,
                autorizacao.getCreatedAt(),
                autorizacao.getAutorizadoAt(),
                autorizacao.getUtilizadoAt()
            );
        }
    }
}
