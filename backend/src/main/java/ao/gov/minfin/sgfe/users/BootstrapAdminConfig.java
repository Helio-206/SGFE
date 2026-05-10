package ao.gov.minfin.sgfe.users;

import ao.gov.minfin.sgfe.common.Role;
import ao.gov.minfin.sgfe.common.UserStatus;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.boot.ApplicationArguments;
import org.springframework.boot.ApplicationRunner;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

@Component
public class BootstrapAdminConfig implements ApplicationRunner {
    private final UserRepository users;
    private final InstituicaoRepository instituicoes;
    private final PasswordEncoder passwordEncoder;
    private final boolean bootstrapTestData;
    private final String adminEmail;
    private final String adminPassword;

    public BootstrapAdminConfig(
        UserRepository users,
        InstituicaoRepository instituicoes,
        PasswordEncoder passwordEncoder,
        @Value("${SGFE_BOOTSTRAP_TEST_DATA:false}") boolean bootstrapTestData,
        @Value("${SGFE_BOOTSTRAP_ADMIN_EMAIL:admin@sgfe.gov.ao}") String adminEmail,
        @Value("${SGFE_BOOTSTRAP_ADMIN_PASSWORD:}") String adminPassword
    ) {
        this.users = users;
        this.instituicoes = instituicoes;
        this.passwordEncoder = passwordEncoder;
        this.bootstrapTestData = bootstrapTestData;
        this.adminEmail = adminEmail;
        this.adminPassword = adminPassword;
    }

    @Override
    @Transactional
    public void run(ApplicationArguments args) {
        if (bootstrapTestData || adminPassword == null || adminPassword.isBlank() || users.existsByEmailIgnoreCase(adminEmail)) {
            return;
        }

        Instituicao inst = instituicoes.findByCodigo("UO-001")
            .orElseGet(() -> {
                Instituicao nova = new Instituicao();
                nova.setCodigo("UO-001");
                nova.setNome("Ministerio das Financas");
                nova.setTipo("Ministerio");
                nova.setResponsavel("Ministro das Financas");
                return instituicoes.save(nova);
            });

        User admin = new User();
        admin.setNome("Administrador do Sistema");
        admin.setUsername("admin");
        admin.setEmail(adminEmail);
        admin.setPasswordHash(passwordEncoder.encode(adminPassword));
        admin.setRole(Role.ADMIN);
        admin.setStatus(UserStatus.ATIVO);
        admin.setInstituicao(inst);
        users.save(admin);
    }
}
