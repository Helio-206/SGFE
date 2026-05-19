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
import com.lowagie.text.pdf.ColumnText;
import com.lowagie.text.pdf.PdfPCell;
import com.lowagie.text.pdf.PdfContentByte;
import com.lowagie.text.pdf.PdfPageEventHelper;
import com.lowagie.text.pdf.PdfPTable;
import com.lowagie.text.pdf.PdfWriter;
import jakarta.servlet.http.HttpServletRequest;
import java.awt.Color;
import java.io.ByteArrayOutputStream;
import java.math.BigDecimal;
import java.net.URL;
import java.text.NumberFormat;
import java.time.Instant;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.Currency;
import java.util.List;
import java.util.Locale;
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
    private static final Locale LOCALE_PT_AO = Locale.forLanguageTag("pt-AO");
    private static final ZoneId ZONA_LUANDA = ZoneId.of("Africa/Luanda");
    private static final DateTimeFormatter EMITIDO_EM = DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm").withZone(ZONA_LUANDA);
    private static final Color INK = new Color(17, 19, 24);
    private static final Color BLUE = new Color(18, 53, 91);
    private static final Color MIST = new Color(238, 243, 248);
    private static final Color LINE = new Color(216, 222, 232);
    private static final Color MUTED = new Color(82, 94, 111);
    private static final Color WHITE = Color.WHITE;

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
        Document document = new Document(PageSize.A4, 42, 42, 54, 54);
        PdfWriter writer = PdfWriter.getInstance(document, out);
        writer.setPageEvent(new ReportFooter());
        document.open();
        addBrandingHeader(document, "SGFE - Resumo Financeiro");
        addReportTitle(document, "Resumo financeiro", "Ano fiscal " + ano + " | Contexto " + (gestor ? "Unidade Orcamental" : "Nacional") + " | Emitido em " + EMITIDO_EM.format(Instant.now()));

        PdfPTable table = new PdfPTable(2);
        table.setWidthPercentage(100);
        table.setSpacingBefore(12);
        table.setWidths(new float[] {1.2f, 1f});
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
        Document document = new Document(PageSize.A4.rotate(), 36, 36, 54, 54);
        PdfWriter writer = PdfWriter.getInstance(document, out);
        writer.setPageEvent(new ReportFooter());
        document.open();
        addBrandingHeader(document, "SGFE - Despesa por Natureza");
        addReportTitle(document, "Despesa por natureza", "Ano fiscal " + ano + " | Registos pagos | Emitido em " + EMITIDO_EM.format(Instant.now()));
        PdfPTable table = new PdfPTable(4);
        table.setWidthPercentage(100);
        table.setSpacingBefore(12);
        table.setWidths(new float[] {1.1f, 4f, 1f, 1.6f});
        addHeader(table, "Rubrica");
        addHeader(table, "Descricao");
        addHeader(table, "Quantidade");
        addHeader(table, "Total pago");
        for (Map<String, Object> row : rows) {
            addDataCell(table, String.valueOf(row.get("cod_classe")), Element.ALIGN_LEFT);
            addDataCell(table, String.valueOf(row.get("descricao")), Element.ALIGN_LEFT);
            addDataCell(table, String.valueOf(row.get("qtd")), Element.ALIGN_CENTER);
            addDataCell(table, moeda((BigDecimal) row.get("total")), Element.ALIGN_RIGHT);
        }
        if (rows.isEmpty()) {
            PdfPCell empty = bodyCell("Sem despesas pagas para o periodo.", Element.ALIGN_CENTER);
            empty.setColspan(4);
            table.addCell(empty);
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

    private void addBrandingHeader(Document document, String reportName) {
        PdfPTable table = new PdfPTable(2);
        table.setWidthPercentage(100);
        table.setSpacingAfter(10);
        table.setWidths(new float[] {1.2f, 2.8f});

        PdfPCell republicCell = new PdfPCell();
        republicCell.setBorder(0);
        republicCell.setPaddingBottom(8);
        republicCell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        loadRepublicInsignia().ifPresent(republicCell::addElement);

        PdfPCell ministryCell = new PdfPCell();
        ministryCell.setBorder(0);
        ministryCell.setPaddingBottom(8);
        ministryCell.setHorizontalAlignment(Element.ALIGN_RIGHT);
        ministryCell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        loadMinfinLogo().ifPresent(ministryCell::addElement);
        Paragraph context = new Paragraph("Ministerio das Financas de Angola", new Font(Font.HELVETICA, 10, Font.BOLD, INK));
        context.setAlignment(Element.ALIGN_RIGHT);
        ministryCell.addElement(context);
        Paragraph subtitle = new Paragraph(reportName, new Font(Font.HELVETICA, 9, Font.NORMAL, MUTED));
        subtitle.setAlignment(Element.ALIGN_RIGHT);
        ministryCell.addElement(subtitle);

        table.addCell(republicCell);
        table.addCell(ministryCell);
        document.add(table);
    }

    private void addReportTitle(Document document, String title, String subtitle) {
        PdfPTable table = new PdfPTable(1);
        table.setWidthPercentage(100);
        PdfPCell cell = new PdfPCell();
        cell.setBorder(0);
        cell.setPadding(14);
        cell.setBackgroundColor(BLUE);

        Paragraph titleParagraph = new Paragraph(title, new Font(Font.HELVETICA, 18, Font.BOLD, WHITE));
        titleParagraph.setSpacingAfter(4);
        cell.addElement(titleParagraph);
        cell.addElement(new Paragraph(subtitle, new Font(Font.HELVETICA, 9, Font.NORMAL, new Color(229, 234, 242))));
        table.addCell(cell);
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
        PdfPCell labelCell = new PdfPCell(new Phrase(label, new Font(Font.HELVETICA, 10, Font.BOLD, BLUE)));
        labelCell.setBackgroundColor(MIST);
        labelCell.setBorderColor(LINE);
        labelCell.setPadding(8);
        table.addCell(labelCell);
        table.addCell(bodyCell(value, Element.ALIGN_RIGHT));
    }

    private void addHeader(PdfPTable table, String value) {
        PdfPCell cell = new PdfPCell(new Phrase(value, new Font(Font.HELVETICA, 9, Font.BOLD, WHITE)));
        cell.setHorizontalAlignment(Element.ALIGN_LEFT);
        cell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        cell.setBackgroundColor(BLUE);
        cell.setBorderColor(BLUE);
        cell.setPadding(8);
        table.addCell(cell);
    }

    private void addDataCell(PdfPTable table, String value, int alignment) {
        table.addCell(bodyCell(value, alignment));
    }

    private PdfPCell bodyCell(String value, int alignment) {
        PdfPCell cell = new PdfPCell(new Phrase(value == null ? "" : value, new Font(Font.HELVETICA, 9, Font.NORMAL, INK)));
        cell.setHorizontalAlignment(alignment);
        cell.setVerticalAlignment(Element.ALIGN_MIDDLE);
        cell.setBorderColor(LINE);
        cell.setPadding(8);
        return cell;
    }

    private String moeda(BigDecimal value) {
        NumberFormat formatter = NumberFormat.getCurrencyInstance(LOCALE_PT_AO);
        formatter.setCurrency(Currency.getInstance("AOA"));
        return formatter.format(value == null ? BigDecimal.ZERO : value);
    }

    private static class ReportFooter extends PdfPageEventHelper {
        @Override
        public void onEndPage(PdfWriter writer, Document document) {
            PdfContentByte canvas = writer.getDirectContent();
            canvas.setColorStroke(LINE);
            canvas.moveTo(document.left(), document.bottom() - 8);
            canvas.lineTo(document.right(), document.bottom() - 8);
            canvas.stroke();

            Phrase footer = new Phrase(
                "SGFE | Documento institucional | Pagina " + writer.getPageNumber(),
                new Font(Font.HELVETICA, 8, Font.NORMAL, MUTED)
            );
            ColumnText.showTextAligned(
                canvas,
                Element.ALIGN_CENTER,
                footer,
                (document.left() + document.right()) / 2,
                document.bottom() - 24,
                0
            );
        }
    }
}
