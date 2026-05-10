package ao.gov.minfin.sgfe.instituicoes;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class InstituicaoService {
    private final InstituicaoRepository instituicoes;
    private final UserRepository users;
    private final AuditService auditService;

    public InstituicaoService(InstituicaoRepository instituicoes, UserRepository users, AuditService auditService) {
        this.instituicoes = instituicoes;
        this.users = users;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<InstituicaoDtos.Response> listar(Pageable pageable) {
        return instituicoes.findAll(pageable).map(InstituicaoDtos.Response::from);
    }

    @Transactional
    public InstituicaoDtos.Response criar(InstituicaoDtos.Request request, Long idUser, HttpServletRequest http) {
        if (instituicoes.existsByCodigo(request.codigo())) {
            throw new RegraNegocioException("O codigo da Unidade Orcamental ja esta em uso.");
        }

        Instituicao inst = new Instituicao();
        inst.setNome(request.nome());
        inst.setTipo(request.tipo());
        inst.setCodigo(request.codigo());
        inst.setResponsavel(request.responsavel());
        Instituicao salva = instituicoes.save(inst);

        User user = users.findById(idUser).orElse(null);
        auditService.registrar(user, salva, "CRIAR_INSTITUICAO", "INSTITUICAO", String.valueOf(salva.getId()),
            "SUCESSO", "INFO", Map.of("codigo", salva.getCodigo()), http);

        return InstituicaoDtos.Response.from(salva);
    }

    @Transactional
    public InstituicaoDtos.Response atualizar(Long id, InstituicaoDtos.Request request, Long idUser, HttpServletRequest http) {
        Instituicao inst = instituicoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));

        instituicoes.findByCodigo(request.codigo())
            .filter(existente -> !existente.getId().equals(id))
            .ifPresent(existente -> {
                throw new RegraNegocioException("O codigo da Unidade Orcamental ja esta em uso.");
            });

        inst.setNome(request.nome());
        inst.setTipo(request.tipo());
        inst.setCodigo(request.codigo());
        inst.setResponsavel(request.responsavel());

        User user = users.findById(idUser).orElse(null);
        auditService.registrar(user, inst, "EDITAR_INSTITUICAO", "INSTITUICAO", String.valueOf(inst.getId()),
            "SUCESSO", "INFO", Map.of("codigo", inst.getCodigo()), http);

        return InstituicaoDtos.Response.from(inst);
    }
}
