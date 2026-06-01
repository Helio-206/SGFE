package ao.gov.minfin.sgfe.auditoria;

import java.time.Instant;

public final class AuditoriaDtos {
    private AuditoriaDtos() {}

    public record Filtro(
        Long idUser,
        String acao,
        String entidade,
        String ip,
        Instant inicio,
        Instant fim
    ) {}

    public record Response(
        Long id,
        Long idUser,
        String usuario,
        Long idInst,
        String codigoUo,
        String acao,
        String entidade,
        String entidadeId,
        String resultado,
        String severidade,
        String ipAddress,
        String contexto,
        Instant createdAt
    ) {
        public static Response from(AuditLog log) {
            return new Response(
                log.getId(),
                log.getUsuario() != null ? log.getUsuario().getId() : null,
                log.getUsuario() != null ? log.getUsuario().getNome() : null,
                log.getInstituicao() != null ? log.getInstituicao().getId() : null,
                log.getInstituicao() != null ? log.getInstituicao().getCodigo() : null,
                log.getAcao(),
                log.getEntidade(),
                log.getEntidadeId(),
                log.getResultado(),
                log.getSeveridade(),
                log.getIpAddress(),
                log.getContexto(),
                log.getCreatedAt()
            );
        }
    }
}
