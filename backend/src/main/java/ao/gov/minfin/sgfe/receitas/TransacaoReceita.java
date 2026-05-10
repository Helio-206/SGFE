package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.classificacoes.ClassificacaoEconomica;
import ao.gov.minfin.sgfe.common.FonteReceita;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
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
import java.math.BigDecimal;
import java.time.Instant;
import java.time.LocalDate;

@Entity
@Table(name = "transacoes_receitas")
public class TransacaoReceita {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_receita")
    private Long id;

    @Enumerated(EnumType.STRING)
    @Column(name = "font_receita", columnDefinition = "enum('PETROLIFERA','NAO_PETROLIFERA','PATRIMONIAL')")
    private FonteReceita fonteReceita;

    @Column(name = "codigo_rupe", length = 40)
    private String codigoRupe;

    @Column(name = "data_registro")
    private LocalDate dataRegistro;

    @Column(name = "valor_arrecadado", precision = 15, scale = 2)
    private BigDecimal valorArrecadado;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "id_classe")
    private ClassificacaoEconomica classificacao;

    @ManyToOne(fetch = FetchType.LAZY, optional = false)
    @JoinColumn(name = "id_inst")
    private Instituicao instituicao;

    @Column(name = "created_at", insertable = false, updatable = false)
    private Instant createdAt;

    @Column(name = "updated_at", insertable = false, updatable = false)
    private Instant updatedAt;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public FonteReceita getFonteReceita() { return fonteReceita; }
    public void setFonteReceita(FonteReceita fonteReceita) { this.fonteReceita = fonteReceita; }
    public String getCodigoRupe() { return codigoRupe; }
    public void setCodigoRupe(String codigoRupe) { this.codigoRupe = codigoRupe; }
    public LocalDate getDataRegistro() { return dataRegistro; }
    public void setDataRegistro(LocalDate dataRegistro) { this.dataRegistro = dataRegistro; }
    public BigDecimal getValorArrecadado() { return valorArrecadado; }
    public void setValorArrecadado(BigDecimal valorArrecadado) { this.valorArrecadado = valorArrecadado; }
    public ClassificacaoEconomica getClassificacao() { return classificacao; }
    public void setClassificacao(ClassificacaoEconomica classificacao) { this.classificacao = classificacao; }
    public Instituicao getInstituicao() { return instituicao; }
    public void setInstituicao(Instituicao instituicao) { this.instituicao = instituicao; }
    public Instant getCreatedAt() { return createdAt; }
    public Instant getUpdatedAt() { return updatedAt; }
}
