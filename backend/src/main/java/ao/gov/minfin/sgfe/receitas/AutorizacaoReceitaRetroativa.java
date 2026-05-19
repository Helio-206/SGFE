package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.users.User;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.time.Instant;
import java.time.LocalDate;

@Entity
@Table(name = "autorizacoes_receitas_retroativas")
public class AutorizacaoReceitaRetroativa {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_autorizacao")
    private Long id;

    @Column(name = "data_receita")
    private LocalDate dataRegistro;

    @Column(name = "dias_atraso")
    private Short diasAtraso;

    @Column(length = 255)
    private String motivo;

    @Enumerated(EnumType.STRING)
    @Column(columnDefinition = "enum('PENDENTE','AUTORIZADA','UTILIZADA')")
    private AutorizacaoReceitaRetroativaStatus status = AutorizacaoReceitaRetroativaStatus.PENDENTE;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "id_inst")
    private Instituicao instituicao;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "id_solicitante")
    private User solicitante;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "id_auditor")
    private User auditor;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "id_receita")
    private TransacaoReceita receita;

    @Column(name = "autorizado_at")
    private Instant autorizadoAt;

    @Column(name = "pdf_gerado_at")
    private Instant pdfGeradoAt;

    @Column(name = "utilizado_at")
    private Instant utilizadoAt;

    @Column(name = "created_at", insertable = false, updatable = false)
    private Instant createdAt;

    @Column(name = "updated_at", insertable = false, updatable = false)
    private Instant updatedAt;

    public Long getId() { return id; }
    public LocalDate getDataRegistro() { return dataRegistro; }
    public void setDataRegistro(LocalDate dataRegistro) { this.dataRegistro = dataRegistro; }
    public Short getDiasAtraso() { return diasAtraso; }
    public void setDiasAtraso(Short diasAtraso) { this.diasAtraso = diasAtraso; }
    public String getMotivo() { return motivo; }
    public void setMotivo(String motivo) { this.motivo = motivo; }
    public AutorizacaoReceitaRetroativaStatus getStatus() { return status; }
    public void setStatus(AutorizacaoReceitaRetroativaStatus status) { this.status = status; }
    public Instituicao getInstituicao() { return instituicao; }
    public void setInstituicao(Instituicao instituicao) { this.instituicao = instituicao; }
    public User getSolicitante() { return solicitante; }
    public void setSolicitante(User solicitante) { this.solicitante = solicitante; }
    public User getAuditor() { return auditor; }
    public void setAuditor(User auditor) { this.auditor = auditor; }
    public TransacaoReceita getReceita() { return receita; }
    public void setReceita(TransacaoReceita receita) { this.receita = receita; }
    public Instant getAutorizadoAt() { return autorizadoAt; }
    public void setAutorizadoAt(Instant autorizadoAt) { this.autorizadoAt = autorizadoAt; }
    public Instant getPdfGeradoAt() { return pdfGeradoAt; }
    public void setPdfGeradoAt(Instant pdfGeradoAt) { this.pdfGeradoAt = pdfGeradoAt; }
    public Instant getUtilizadoAt() { return utilizadoAt; }
    public void setUtilizadoAt(Instant utilizadoAt) { this.utilizadoAt = utilizadoAt; }
    public Instant getCreatedAt() { return createdAt; }
    public Instant getUpdatedAt() { return updatedAt; }
}
