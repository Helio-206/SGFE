package ao.gov.minfin.sgfe.receitas;

import ao.gov.minfin.sgfe.auditoria.AuditService;
import ao.gov.minfin.sgfe.auth.UserPrincipal;
import ao.gov.minfin.sgfe.common.FiscalYearService;
import ao.gov.minfin.sgfe.common.RegraNegocioException;
import ao.gov.minfin.sgfe.instituicoes.Instituicao;
import ao.gov.minfin.sgfe.instituicoes.InstituicaoRepository;
import ao.gov.minfin.sgfe.users.User;
import ao.gov.minfin.sgfe.users.UserRepository;
import com.lowagie.text.Document;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
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
import java.time.Instant;
import java.time.LocalDate;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.Map;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class ReceitaAutorizacaoService {
    static final ZoneId ZONA_SISTEMA = ZoneId.of("Africa/Luanda");
    private static final DateTimeFormatter DATA_FORMAT = DateTimeFormatter.ofPattern("dd/MM/yyyy");
    private static final DateTimeFormatter EMITIDO_EM = DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm").withZone(ZONA_SISTEMA);
    private static final Color INK = new Color(17, 19, 24);
    private static final Color BLUE = new Color(18, 53, 91);
    private static final Color MIST = new Color(238, 243, 248);
    private static final Color LINE = new Color(216, 222, 232);
    private static final Color MUTED = new Color(82, 94, 111);
    private static final Color WHITE = Color.WHITE;

    private final AutorizacaoReceitaRetroativaRepository autorizacoes;
    private final InstituicaoRepository instituicoes;
    private final UserRepository users;
    private final FiscalYearService fiscalYear;
    private final AuditService auditService;

    public ReceitaAutorizacaoService(
        AutorizacaoReceitaRetroativaRepository autorizacoes,
        InstituicaoRepository instituicoes,
        UserRepository users,
        FiscalYearService fiscalYear,
        AuditService auditService
    ) {
        this.autorizacoes = autorizacoes;
        this.instituicoes = instituicoes;
        this.users = users;
        this.fiscalYear = fiscalYear;
        this.auditService = auditService;
    }

    @Transactional(readOnly = true)
    public Page<ReceitaAutorizacaoDtos.Response> listar(UserPrincipal principal, Pageable pageable) {
        Page<AutorizacaoReceitaRetroativa> page = principal.isGestor()
            ? autorizacoes.findByInstituicaoId(principal.idInst(), pageable)
            : autorizacoes.findAll(pageable);
        return page.map(ReceitaAutorizacaoDtos.Response::from);
    }

    @Transactional
    public ReceitaAutorizacaoDtos.Response solicitar(
        ReceitaAutorizacaoDtos.SolicitarRequest request,
        UserPrincipal principal,
        HttpServletRequest http
    ) {
        LocalDate hoje = hoje();
        fiscalYear.validarDataNoAnoFiscalCorrente(request.dataRegistro());
        if (!request.dataRegistro().isBefore(hoje)) {
            throw new RegraNegocioException("A autorizacao retroativa e apenas para datas anteriores a data corrente.");
        }

        Long idInst = principal.isAdmin() ? request.idInst() : principal.idInst();
        if (idInst == null) {
            throw new RegraNegocioException("Admin deve informar a Unidade Orcamental da autorizacao.");
        }

        Instituicao instituicao = instituicoes.findById(idInst)
            .orElseThrow(() -> new RegraNegocioException("Instituicao nao encontrada."));
        User solicitante = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));

        AutorizacaoReceitaRetroativa autorizacao = new AutorizacaoReceitaRetroativa();
        autorizacao.setInstituicao(instituicao);
        autorizacao.setSolicitante(solicitante);
        autorizacao.setDataRegistro(request.dataRegistro());
        autorizacao.setDiasAtraso((short) Math.toIntExact(ChronoUnit.DAYS.between(request.dataRegistro(), hoje)));
        autorizacao.setMotivo(request.motivo().trim());
        AutorizacaoReceitaRetroativa salva = autorizacoes.save(autorizacao);

        auditService.registrar(solicitante, instituicao, "SOLICITAR_RECEITA_RETROATIVA", "AUTORIZACAO_RECEITA_RETROATIVA",
            String.valueOf(salva.getId()), "SUCESSO", "ALERTA", Map.of(
                "dataRegistro", salva.getDataRegistro(),
                "diasAtraso", salva.getDiasAtraso()
            ), http);

        return ReceitaAutorizacaoDtos.Response.from(salva);
    }

    @Transactional
    public byte[] autorizarEGerarPdf(Long id, UserPrincipal principal, HttpServletRequest http) {
        User auditor = users.findById(principal.id())
            .orElseThrow(() -> new RegraNegocioException("Utilizador nao encontrado."));
        AutorizacaoReceitaRetroativa autorizacao = autorizacoes.findById(id)
            .orElseThrow(() -> new RegraNegocioException("Autorizacao retroativa nao encontrada."));
        if (autorizacao.getStatus() != AutorizacaoReceitaRetroativaStatus.PENDENTE) {
            throw new RegraNegocioException("Esta autorizacao ja foi processada.");
        }

        Instant agora = Instant.now();
        autorizacao.setAuditor(auditor);
        autorizacao.setStatus(AutorizacaoReceitaRetroativaStatus.AUTORIZADA);
        autorizacao.setAutorizadoAt(agora);
        autorizacao.setPdfGeradoAt(agora);
        AutorizacaoReceitaRetroativa salva = autorizacoes.save(autorizacao);
        byte[] pdf = gerarPdf(salva);

        auditService.registrar(auditor, salva.getInstituicao(), "AUTORIZAR_RECEITA_RETROATIVA", "AUTORIZACAO_RECEITA_RETROATIVA",
            String.valueOf(salva.getId()), "SUCESSO", "CRITICO", Map.of(
                "dataRegistro", salva.getDataRegistro(),
                "diasAtraso", salva.getDiasAtraso()
            ), http);

        return pdf;
    }

    LocalDate hoje() {
        return LocalDate.now(ZONA_SISTEMA);
    }

    private byte[] gerarPdf(AutorizacaoReceitaRetroativa autorizacao) {
        ByteArrayOutputStream out = new ByteArrayOutputStream();
        Document document = new Document(PageSize.A4, 42, 42, 54, 54);
        PdfWriter writer = PdfWriter.getInstance(document, out);
        writer.setPageEvent(new AuthorizationFooter());
        document.open();
        addDocumentHeader(document, autorizacao);
        Paragraph intro = new Paragraph(
            "Este documento autoriza uma unica criacao de Receita RUPE com data anterior a data corrente.",
            new Font(Font.HELVETICA, 10, Font.NORMAL, MUTED)
        );
        intro.setSpacingBefore(12);
        intro.setSpacingAfter(12);
        document.add(intro);

        PdfPTable table = new PdfPTable(2);
        table.setWidthPercentage(100);
        table.setWidths(new float[] {1.1f, 2.4f});
        addRow(table, "Autorizacao", String.valueOf(autorizacao.getId()));
        addRow(table, "Unidade Orcamental", autorizacao.getInstituicao().getCodigo() + " - " + autorizacao.getInstituicao().getNome());
        addRow(table, "Solicitante", autorizacao.getSolicitante().getNome());
        addRow(table, "Auditor", autorizacao.getAuditor().getNome());
        addRow(table, "Data pretendida", DATA_FORMAT.format(autorizacao.getDataRegistro()));
        addRow(table, "Dias de atraso", String.valueOf(autorizacao.getDiasAtraso()));
        addRow(table, "Motivo", autorizacao.getMotivo());
        addRow(table, "Estado", String.valueOf(autorizacao.getStatus()));
        document.add(table);
        Paragraph notice = new Paragraph(
            "A autorizacao encerra automaticamente apos a criacao da receita correspondente.",
            new Font(Font.HELVETICA, 9, Font.BOLD, BLUE)
        );
        notice.setSpacingBefore(14);
        document.add(notice);
        document.close();
        return out.toByteArray();
    }

    private void addDocumentHeader(Document document, AutorizacaoReceitaRetroativa autorizacao) {
        PdfPTable table = new PdfPTable(1);
        table.setWidthPercentage(100);
        PdfPCell cell = new PdfPCell();
        cell.setBorder(0);
        cell.setPadding(14);
        cell.setBackgroundColor(BLUE);

        Paragraph institution = new Paragraph("Republica de Angola | Ministerio das Financas", new Font(Font.HELVETICA, 9, Font.BOLD, new Color(229, 234, 242)));
        institution.setSpacingAfter(5);
        cell.addElement(institution);

        Paragraph title = new Paragraph("Autorizacao de Receita Retroativa", new Font(Font.HELVETICA, 17, Font.BOLD, WHITE));
        title.setSpacingAfter(5);
        cell.addElement(title);

        cell.addElement(new Paragraph(
            "SGFE #" + autorizacao.getId() + " | Emitido em " + EMITIDO_EM.format(Instant.now()),
            new Font(Font.HELVETICA, 9, Font.NORMAL, new Color(229, 234, 242))
        ));
        table.addCell(cell);
        document.add(table);
    }

    private void addRow(PdfPTable table, String label, String value) {
        PdfPCell labelCell = new PdfPCell(new Phrase(label, new Font(Font.HELVETICA, 10, Font.BOLD, BLUE)));
        labelCell.setBackgroundColor(MIST);
        labelCell.setBorderColor(LINE);
        labelCell.setPadding(8);
        table.addCell(labelCell);

        PdfPCell valueCell = new PdfPCell(new Phrase(value == null ? "" : value, new Font(Font.HELVETICA, 10, Font.NORMAL, INK)));
        valueCell.setBorderColor(LINE);
        valueCell.setPadding(8);
        table.addCell(valueCell);
    }

    private static class AuthorizationFooter extends PdfPageEventHelper {
        @Override
        public void onEndPage(PdfWriter writer, Document document) {
            PdfContentByte canvas = writer.getDirectContent();
            canvas.setColorStroke(LINE);
            canvas.moveTo(document.left(), document.bottom() - 8);
            canvas.lineTo(document.right(), document.bottom() - 8);
            canvas.stroke();

            Phrase footer = new Phrase(
                "SGFE | Autorizacao institucional | Pagina " + writer.getPageNumber(),
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
