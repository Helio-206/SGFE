package ao.gov.minfin.sgfe.auditoria;

import jakarta.persistence.criteria.JoinType;
import java.time.Instant;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class AuditoriaService {
    private final AuditLogRepository logs;

    public AuditoriaService(AuditLogRepository logs) {
        this.logs = logs;
    }

    @Transactional(readOnly = true)
    public Page<AuditoriaDtos.Response> filtrar(
        Long idUser,
        String acao,
        String entidade,
        String ip,
        Instant inicio,
        Instant fim,
        Pageable pageable
    ) {
        Specification<AuditLog> spec = Specification.where(null);
        if (idUser != null) {
            spec = spec.and((root, query, cb) -> cb.equal(root.join("usuario", JoinType.LEFT).get("id"), idUser));
        }
        if (acao != null && !acao.isBlank()) {
            spec = spec.and((root, query, cb) -> cb.like(cb.lower(root.get("acao")), "%" + acao.toLowerCase() + "%"));
        }
        if (entidade != null && !entidade.isBlank()) {
            spec = spec.and((root, query, cb) -> cb.equal(root.get("entidade"), entidade));
        }
        if (ip != null && !ip.isBlank()) {
            spec = spec.and((root, query, cb) -> cb.equal(root.get("ipAddress"), ip));
        }
        if (inicio != null) {
            spec = spec.and((root, query, cb) -> cb.greaterThanOrEqualTo(root.get("createdAt"), inicio));
        }
        if (fim != null) {
            spec = spec.and((root, query, cb) -> cb.lessThanOrEqualTo(root.get("createdAt"), fim));
        }

        return logs.findAll(spec, pageable).map(AuditoriaDtos.Response::from);
    }
}
