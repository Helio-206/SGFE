package ao.gov.minfin.sgfe.relatorios;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.EstadoDespesa;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.despesas.TransacaoDespesaRepository;
import ao.gov.minfin.sgfe.orcamentos.OrcamentoRepository;
import ao.gov.minfin.sgfe.receitas.TransacaoReceitaRepository;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import com.lowagie.text.Document;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
import com.lowagie.text.Image;
import com.lowagie.text.PageSize;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfPCell;
import com.lowagie.text.pdf.PdfPTable;
import com.lowagie.text.pdf.PdfWriter;
import jakarta.servlet.http.HttpServletRequest;
import java.io.ByteArrayOutputStream;
import java.math.BigDecimal;
import java.net.URL;
import java.time.LocalDate;
import java.util.List;
import java.util.Map;
import org.apache.poi.ss.usermodel.CellStyle;
import org.apache.poi.ss.usermodel.Row;
import org.apache.poi.ss.usermodel.Workbook;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class RelatorioService {
    private final FiscalYearService fiscalYear;
    private final OrcamentoRepository orcamentos;
    private final TransacaoDespesaRepository despesas;
    private final TransacaoReceitaRepository receitas;
    private final UserRepository users;
    private final AuditService auditService;
    private final JdbcTemplate jdbc;
    private final String minfinLogoUrl;

    public RelatorioService(
        FiscalYearService fiscalYear,
        OrcamentoRepository orcamentos,
        TransacaoDespesaRepository despesas,
        TransacaoReceitaRepository receitas,
        UserRepository users,
        AuditService auditService,
        JdbcTemplate jdbc,
        @Value("${sgfe.branding.minfin-logo-url}") String minfinLogoUrl
    ) {
        this.fiscalYear = fiscalYear;
        this.orcamentos = orcamentos;
        this.despesas = despesas;
        this.receitas = receitas;
        this.users = users;
        this.auditService = auditService;
        this.jdbc = jdbc;
        this.minfinLogoUrl = minfinLogoUrl;
    }

    @Transactional(readOnly = true)
    public byte[] resumoFinanceiroPdf(UserPrincipal principal, HttpServletRequest http) {
        int ano = fiscalYear.anoCorrente();
        Long idInst = principal.idInst();
        boolean gestor = principal.isGestor();
        BigDecimal tecto = gestor
            ? orcamentos.findByInstituicaoIdAndAnoFiscal(idInst, ano).map(o -> o.getValorTotal()).orElse(BigDecimal.ZERO)
            : orcamentos.sumTectoByAno(ano);
        BigDecimal comprometido = gestor
            ? despesas.sumByInstituicaoAnoEstados(idInst, ano, EstadoDespesa.estadosQueComprometemTecto())
            : despesas.sumByAnoEstados(ano, EstadoDespesa.estadosQueComprometemTecto());
        BigDecimal pago = gestor
            ? despesas.sumByInstituicaoAnoEstados(idInst, ano, List.of(EstadoDespesa.PAGA))
            : despesas.sumByAnoEstado(ano, EstadoDespesa.PAGA);
        BigDecimal receita = gestor
            ? receitas.sumByInstituicaoAndAno(idInst, ano)
            : receitas.sumByAno(ano);

        ByteArrayOutputStream out = new ByteArrayOutputStream();
        Document document = new Document(PageSize.A4, 48, 48, 48, 48);
        PdfWriter.getInstance(document, out);
        document.open();
        addBrandingHeader(document, "SGFE - Resumo Financeiro");
        document.add(titulo("SGFE - Resumo Financeiro"));
        document.add(new Paragraph("Ano fiscal: " + ano));
        document.add(new Paragraph("Contexto: " + (gestor ? "Unidade Orcamental" : "Nacional")));
        document.add(new Paragraph(" "));

        PdfPTable table = new PdfPTable(2);
        table.setWidthPercentage(100);
        addRow(table, "Tecto orcamental", moeda(tecto));
        addRow(table, "Valor comprometido", moeda(comprometido));
        addRow(table, "Valor pago", moeda(pago));
        addRow(table, "Receita RUPE", moeda(receita));
        addRow(table, "Saldo disponivel", moeda(tecto.subtract(comprometido).max(BigDecimal.ZERO)));
        document.add(table);
        document.close();

        auditar(principal, "EXPORTACAO_RELATORIO_FINANCEIRO_PDF", "RELATORIO", "resumo-financeiro", http);
        return out.toByteArray();
    }

    @Transactional(readOnly = true)
    public byte[] despesaPorNaturezaPdf(UserPrincipal principal, HttpServletRequest http) {
        int ano = fiscalYear.anoCorrente();
        List<Map<String, Object>> rows = principal.isGestor()
            ? jdbc.queryForList("""
                SELECT ce.cod_classe, ce.descricao, COUNT(*) qtd, COALESCE(SUM(td.valor_bruto), 0) total
                FROM transacoes_despesas td
                JOIN classificacoes_economicas ce ON ce.id_classe = td.id_classe
                WHERE td.estado = 'PAGA' AND YEAR(td.data_registro) = ? AND td.id_inst = ?
                GROUP BY ce.cod_classe, ce.descricao
                ORDER BY ce.cod_classe
            """, ano, principal.idInst())
            : jdbc.queryForList("""
                SELECT ce.cod_classe, ce.descricao, COUNT(*) qtd, COALESCE(SUM(td.valor_bruto), 0) total
                FROM transacoes_despesas td
                JOIN classificacoes_economicas ce ON ce.id_classe = td.id_classe
                WHERE td.estado = 'PAGA' AND YEAR(td.data_registro) = ?
                GROUP BY ce.cod_classe, ce.descricao
                ORDER BY ce.cod_classe
            """, ano);

        ByteArrayOutputStream out = new ByteArrayOutputStream();
        Document document = new Document(PageSize.A4.rotate(), 36, 36, 36, 36);
        PdfWriter.getInstance(document, out);
        document.open();
        addBrandingHeader(document, "SGFE - Despesa por Natureza");
        document.add(titulo("SGFE - Despesa por Natureza"));
        document.add(new Paragraph("Ano fiscal: " + ano));
        document.add(new Paragraph(" "));
        PdfPTable table = new PdfPTable(4);
        table.setWidthPercentage(100);
        addHeader(table, "Rubrica");
        addHeader(table, "Descricao");
        addHeader(table, "Quantidade");
        addHeader(table, "Total pago");
        for (Map<String, Object> row : rows) {
            table.addCell(String.valueOf(row.get("cod_classe")));
            table.addCell(String.valueOf(row.get("descricao")));
            table.addCell(String.valueOf(row.get("qtd")));
            table.addCell(moeda((BigDecimal) row.get("total")));
        }
        document.add(table);
        document.close();

        auditar(principal, "EXPORTACAO_DESPESA_POR_NATUREZA_PDF", "RELATORIO", "despesa-por-natureza", http);
        return out.toByteArray();
    }

    @Transactional(readOnly = true)
    public byte[] receitasRupeXlsx(UserPrincipal principal, LocalDate inicio, LocalDate fim, HttpServletRequest http) {
        int ano = fiscalYear.anoCorrente();
        LocalDate dataInicio = inicio != null ? inicio : LocalDate.of(ano, 1, 1);
        LocalDate dataFim = fim != null ? fim : LocalDate.now();
        List<Map<String, Object>> rows = principal.isGestor()
            ? jdbc.queryForList("""
                SELECT tr.id_receita, tr.codigo_rupe, tr.data_registro, tr.font_receita, ce.cod_classe, ce.descricao,
                       i.codigo, i.nome, tr.valor_arrecadado
                FROM transacoes_receitas tr
                JOIN classificacoes_economicas ce ON ce.id_classe = tr.id_classe
                JOIN instituicoes i ON i.id_inst = tr.id_inst
                WHERE tr.id_inst = ? AND tr.data_registro BETWEEN ? AND ?
                ORDER BY tr.data_registro DESC
            """, principal.idInst(), dataInicio, dataFim)
            : jdbc.queryForList("""
                SELECT tr.id_receita, tr.codigo_rupe, tr.data_registro, tr.font_receita, ce.cod_classe, ce.descricao,
                       i.codigo, i.nome, tr.valor_arrecadado
                FROM transacoes_receitas tr
                JOIN classificacoes_economicas ce ON ce.id_classe = tr.id_classe
                JOIN instituicoes i ON i.id_inst = tr.id_inst
                WHERE tr.data_registro BETWEEN ? AND ?
                ORDER BY tr.data_registro DESC
            """, dataInicio, dataFim);

        try (Workbook workbook = new XSSFWorkbook(); ByteArrayOutputStream out = new ByteArrayOutputStream()) {
            var sheet = workbook.createSheet("Receitas RUPE");
            CellStyle header = workbook.createCellStyle();
            org.apache.poi.ss.usermodel.Font font = workbook.createFont();
            font.setBold(true);
            header.setFont(font);

            Row headerRow = sheet.createRow(0);
            String[] headings = {"ID", "RUPE", "Data", "Fonte", "Rubrica", "Descricao", "UO", "Valor AOA"};
            for (int i = 0; i < headings.length; i++) {
                var cell = headerRow.createCell(i);
                cell.setCellValue(headings[i]);
                cell.setCellStyle(header);
            }

            int rowIndex = 1;
            for (Map<String, Object> item : rows) {
                Row row = sheet.createRow(rowIndex++);
                row.createCell(0).setCellValue(String.valueOf(item.get("id_receita")));
                row.createCell(1).setCellValue(String.valueOf(item.get("codigo_rupe")));
                row.createCell(2).setCellValue(String.valueOf(item.get("data_registro")));
                row.createCell(3).setCellValue(String.valueOf(item.get("font_receita")));
                row.createCell(4).setCellValue(String.valueOf(item.get("cod_classe")));
                row.createCell(5).setCellValue(String.valueOf(item.get("descricao")));
                row.createCell(6).setCellValue(item.get("codigo") + " - " + item.get("nome"));
                row.createCell(7).setCellValue(String.valueOf(item.get("valor_arrecadado")));
            }
            for (int i = 0; i < headings.length; i++) {
                sheet.autoSizeColumn(i);
            }
            workbook.write(out);
            auditar(principal, "EXPORTACAO_RECEITAS_RUPE_XLSX", "RELATORIO", "receitas-rupe", http);
            return out.toByteArray();
        } catch (Exception ex) {
            throw new IllegalStateException("Nao foi possivel gerar o ficheiro Excel.", ex);
        }
    }

    private void auditar(UserPrincipal principal, String acao, String entidade, String entidadeId, HttpServletRequest http) {
        User user = users.findById(principal.id()).orElse(null);
        auditService.registrar(user, user != null ? user.getInstituicao() : null, acao, entidade, entidadeId,
            "SUCESSO", "CRITICO", Map.of(), http);
    }

    private Paragraph titulo(String text) {
        Paragraph paragraph = new Paragraph(text, new Font(Font.HELVETICA, 18, Font.BOLD));
        paragraph.setSpacingAfter(6);
        return paragraph;
    }

    private void addBrandingHeader(Document document, String reportName) {
        PdfPTable table = new PdfPTable(2);
        table.setWidthPercentage(100);
        table.setSpacingAfter(12);
        table.setWidths(new float[] {1.2f, 2.8f});

        PdfPCell republicCell = new PdfPCell();
        republicCell.setBorder(0);
        republicCell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        loadRepublicInsignia().ifPresent(republicCell::addElement);

        PdfPCell ministryCell = new PdfPCell();
        ministryCell.setBorder(0);
        ministryCell.setHorizontalAlignment(Element.ALIGN_RIGHT);
        ministryCell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        loadMinfinLogo().ifPresent(ministryCell::addElement);
        Paragraph context = new Paragraph("Ministerio das Financas de Angola", new Font(Font.HELVETICA, 10, Font.BOLD));
        context.setAlignment(Element.ALIGN_RIGHT);
        ministryCell.addElement(context);
        Paragraph subtitle = new Paragraph(reportName, new Font(Font.HELVETICA, 9, Font.NORMAL));
        subtitle.setAlignment(Element.ALIGN_RIGHT);
        ministryCell.addElement(subtitle);

        table.addCell(republicCell);
        table.addCell(ministryCell);
        document.add(table);
    }

    private java.util.Optional<Image> loadRepublicInsignia() {
        try {
            URL resource = Thread.currentThread().getContextClassLoader().getResource("branding/insignia-republica-angola.png");
            if (resource == null) {
                return java.util.Optional.empty();
            }

            Image image = Image.getInstance(resource);
            image.scaleToFit(62, 72);
            image.setAlignment(Element.ALIGN_LEFT);
            return java.util.Optional.of(image);
        } catch (Exception ignored) {
            return java.util.Optional.empty();
        }
    }

    private java.util.Optional<Image> loadMinfinLogo() {
        try {
            Image image = Image.getInstance(new URL(minfinLogoUrl));
            image.scaleToFit(220, 36);
            image.setAlignment(Element.ALIGN_RIGHT);
            return java.util.Optional.of(image);
        } catch (Exception ignored) {
            return java.util.Optional.empty();
        }
    }

    private void addRow(PdfPTable table, String label, String value) {
        addHeader(table, label);
        table.addCell(value);
    }

    private void addHeader(PdfPTable table, String value) {
        PdfPCell cell = new PdfPCell(new Phrase(value, new Font(Font.HELVETICA, 10, Font.BOLD)));
        table.addCell(cell);
    }

    private String moeda(BigDecimal value) {
        return value == null ? "0.00 AOA" : value.toPlainString() + " AOA";
    }
}
