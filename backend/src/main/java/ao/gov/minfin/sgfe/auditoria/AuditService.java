package ao.gov.minfin.sgfe.auditoria;

import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.users.User;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.servlet.http.HttpServletRequest;
import java.util.Map;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Propagation;
import org.springframework.transaction.annotation.Transactional;

@Service
public class AuditService {
    private final AuditLogRepository logs;
    private final ObjectMapper objectMapper;

    public AuditService(AuditLogRepository logs, ObjectMapper objectMapper) {
        this.logs = logs;
        this.objectMapper = objectMapper;
    }

    @Transactional(propagation = Propagation.REQUIRES_NEW)
    public void registrar(
        User usuario,
        Instituicao instituicao,
        String acao,
        String entidade,
        String entidadeId,
        String resultado,
        String severidade,
        Map<String, Object> contexto,
        HttpServletRequest request
    ) {
        AuditLog log = new AuditLog();
        log.setUsuario(usuario);
        log.setInstituicao(instituicao);
        log.setAcao(acao);
        log.setEntidade(entidade);
        log.setEntidadeId(entidadeId);
        log.setResultado(resultado);
        log.setSeveridade(severidade);
        log.setIpAddress(request != null ? request.getRemoteAddr() : null);
        log.setUserAgent(request != null ? request.getHeader("User-Agent") : null);
        log.setCorrelationId(request != null ? request.getHeader("X-Correlation-Id") : null);
        log.setContexto(toJson(contexto));
        logs.save(log);
    }

    private String toJson(Map<String, Object> contexto) {
        if (contexto == null || contexto.isEmpty()) {
            return null;
        }
        try {
            return objectMapper.writeValueAsString(contexto);
        } catch (JsonProcessingException ex) {
            return "{\"erro\":\"contexto_nao_serializado\"}";
        }
    }
}
