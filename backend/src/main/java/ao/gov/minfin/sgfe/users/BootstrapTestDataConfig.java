package ao.gov.minfin.sgfe.users;

import ao.gov.minfin.sgfe.auditoria.AuditLogRepository;
import ao.gov.minfin.sgfe.auth.PasswordResetTokenRepository;
import ao.gov.minfin.sgfe.auth.RefreshTokenRepository;
import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.despesas.TransacaoDespesaRepository;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import ao.gov.minfin.sgfe.orcamentos.Orcamento;
import ao.gov.minfin.sgfe.orcamentos.OrcamentoRepository;
import ao.gov.minfin.sgfe.receitas.TransacaoReceitaRepository;
import java.math.BigDecimal;
import java.time.Year;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

@Component
public class BootstrapTestDataConfig implements ApplicationRunner {
    private static final Logger LOG = LoggerFactory.getLogger(BootstrapTestDataConfig.class);
    private static final String ADMIN_PASSWORD = "Admin@SGFE2026";
    private static final String GESTOR_PASSWORD = "Gestor@SGFE2026";
    private static final String AUDITOR_PASSWORD = "Auditor@SGFE2026";

    private final UserRepository users;
    private final InstituicaoRepository instituicoes;
    private final OrcamentoRepository orcamentos;
    private final TransacaoDespesaRepository despesas;
    private final TransacaoReceitaRepository receitas;
    private final RefreshTokenRepository refreshTokens;
    private final PasswordResetTokenRepository passwordResetTokens;
    private final AuditLogRepository auditLogs;
    private final PasswordEncoder passwordEncoder;
    private final boolean enabled;

    public BootstrapTestDataConfig(
        UserRepository users,
        InstituicaoRepository instituicoes,
        OrcamentoRepository orcamentos,
        TransacaoDespesaRepository despesas,
        TransacaoReceitaRepository receitas,
        RefreshTokenRepository refreshTokens,
        PasswordResetTokenRepository passwordResetTokens,
        AuditLogRepository auditLogs,
        PasswordEncoder passwordEncoder,
        @Value("${SGFE_BOOTSTRAP_TEST_DATA:false}") boolean enabled
    ) {
        this.users = users;
        this.instituicoes = instituicoes;
        this.orcamentos = orcamentos;
        this.despesas = despesas;
        this.receitas = receitas;
        this.refreshTokens = refreshTokens;
        this.passwordResetTokens = passwordResetTokens;
        this.auditLogs = auditLogs;
        this.passwordEncoder = passwordEncoder;
        this.enabled = enabled;
    }

    @Override
    @Transactional
    public void run(ApplicationArguments args) {
        if (!enabled) {
            return;
        }

        limparDadosOperacionais();

        Map<String, Instituicao> instituicoesSeed = seedInstituicoes();
        User admin = criarUtilizador(
            "Administrador do Sistema",
            "admin.sistema",
            "admin.sistema@sgfe.gov.ao",
            ADMIN_PASSWORD,
            Role.ADMIN,
            instituicoesSeed.get("UO-001")
        );

        criarUtilizador(
            "Gestor Ministerio das Financas",
            "gestor.minfin",
            "gestor.minfin@sgfe.gov.ao",
            GESTOR_PASSWORD,
            Role.GESTOR,
            instituicoesSeed.get("UO-001")
        );
        criarUtilizador(
            "Auditor Ministerio das Financas",
            "auditor.minfin",
            "auditor.minfin@sgfe.gov.ao",
            AUDITOR_PASSWORD,
            Role.AUDITOR,
            instituicoesSeed.get("UO-001")
        );
        criarUtilizador(
            "Gestor AGT",
            "gestor.agt",
            "gestor.agt@sgfe.gov.ao",
            GESTOR_PASSWORD,
            Role.GESTOR,
            instituicoesSeed.get("UO-002")
        );
        criarUtilizador(
            "Auditor AGT",
            "auditor.agt",
            "auditor.agt@sgfe.gov.ao",
            AUDITOR_PASSWORD,
            Role.AUDITOR,
            instituicoesSeed.get("UO-002")
        );
        criarUtilizador(
            "Gestor Tesouro",
            "gestor.tesouro",
            "gestor.tesouro@sgfe.gov.ao",
            GESTOR_PASSWORD,
            Role.GESTOR,
            instituicoesSeed.get("UO-003")
        );
        criarUtilizador(
            "Gestor Governo Luanda",
            "gestor.luanda",
            "gestor.luanda@sgfe.gov.ao",
            GESTOR_PASSWORD,
            Role.GESTOR,
            instituicoesSeed.get("UO-004")
        );

        criarOrcamento(admin, instituicoesSeed.get("UO-001"), new BigDecimal("1250000000.00"));
        criarOrcamento(admin, instituicoesSeed.get("UO-002"), new BigDecimal("980000000.00"));
        criarOrcamento(admin, instituicoesSeed.get("UO-003"), new BigDecimal("760000000.00"));
        criarOrcamento(admin, instituicoesSeed.get("UO-004"), new BigDecimal("540000000.00"));

        LOG.info("Dados de teste do SGFE recriados: {} instituicoes, 7 utilizadores e 4 orcamentos.", instituicoesSeed.size());
    }

    private void limparDadosOperacionais() {
        refreshTokens.deleteAllInBatch();
        passwordResetTokens.deleteAllInBatch();
        auditLogs.deleteAllInBatch();
        despesas.deleteAllInBatch();
        receitas.deleteAllInBatch();
        orcamentos.deleteAllInBatch();
        users.deleteAllInBatch();
    }

    private Map<String, Instituicao> seedInstituicoes() {
        List<InstituicaoSeed> seeds = List.of(
            new InstituicaoSeed("UO-001", "Ministerio das Financas", "Ministerio", "Ministro das Financas"),
            new InstituicaoSeed("UO-002", "Administracao Geral Tributaria", "Instituto Publico", "Presidente da AGT"),
            new InstituicaoSeed("UO-003", "Direccao Nacional do Tesouro", "Direccao Nacional", "Director Nacional do Tesouro"),
            new InstituicaoSeed("UO-004", "Governo Provincial de Luanda", "Governo Provincial", "Governador Provincial de Luanda")
        );

        Map<String, Instituicao> resultado = new LinkedHashMap<>();
        for (InstituicaoSeed seed : seeds) {
            Instituicao instituicao = instituicoes.findByCodigo(seed.codigo()).orElseGet(Instituicao::new);
            instituicao.setCodigo(seed.codigo());
            instituicao.setNome(seed.nome());
            instituicao.setTipo(seed.tipo());
            instituicao.setResponsavel(seed.responsavel());
            instituicao.setStatus("ATIVA");
            resultado.put(seed.codigo(), instituicoes.save(instituicao));
        }
        return resultado;
    }

    private User criarUtilizador(String nome, String username, String email, String rawPassword, Role role, Instituicao instituicao) {
        User user = new User();
        user.setNome(nome);
        user.setUsername(username);
        user.setEmail(email);
        user.setPasswordHash(passwordEncoder.encode(rawPassword));
        user.setRole(role);
        user.setStatus(UserStatus.ATIVO);
        user.setInstituicao(instituicao);
        return users.save(user);
    }

    private void criarOrcamento(User admin, Instituicao instituicao, BigDecimal valorTotal) {
        Orcamento orcamento = new Orcamento();
        orcamento.setUsuario(admin);
        orcamento.setInstituicao(instituicao);
        orcamento.setAnoFiscal(Year.now().getValue());
        orcamento.setValorTotal(valorTotal);
        orcamentos.save(orcamento);
    }

    private record InstituicaoSeed(String codigo, String nome, String tipo, String responsavel) {
    }
}