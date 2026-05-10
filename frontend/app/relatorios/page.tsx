"use client";

import { Download, FileSpreadsheet, FileText } from "lucide-react";
import { AppShell } from "@/components/layout/app-shell";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { downloadFile } from "@/lib/api";

const reports = [
  { title: "Resumo financeiro", icon: FileText, action: "Exportar PDF", path: "/relatorios/exportar/resumo-financeiro.pdf", file: "resumo-financeiro.pdf" },
  { title: "Despesa por natureza", icon: FileText, action: "Exportar PDF", path: "/relatorios/exportar/despesa-por-natureza.pdf", file: "despesa-por-natureza.pdf" },
  { title: "Mapa de receitas RUPE", icon: FileSpreadsheet, action: "Exportar Excel", path: "/relatorios/exportar/receitas-rupe.xlsx", file: "receitas-rupe.xlsx" }
];

async function downloadReport(path: string, fileName: string) {
  await downloadFile(path, fileName);
}

export default function RelatoriosPage() {
  return (
    <AppShell title="Relatorios financeiros">
      <div className="grid gap-5 md:grid-cols-3">
        {reports.map((item) => (
          <Card key={item.title}>
            <CardHeader>
              <CardTitle>{item.title}</CardTitle>
            </CardHeader>
            <CardContent>
              <item.icon className="h-8 w-8 text-institutional-gold" />
              <p className="mt-4 text-sm leading-6 text-muted-foreground">
                Exportacao gerada pelo backend com controlo de acesso e audit log.
              </p>
              <Button className="mt-5" variant="secondary" onClick={() => downloadReport(item.path, item.file)}>
                <Download className="h-4 w-4" />
                {item.action}
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>
    </AppShell>
  );
}
