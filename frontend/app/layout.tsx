import type { Metadata } from "next";
import "./globals.css";
import { QueryProvider } from "@/components/providers";

export const metadata: Metadata = {
  title: "SGFE | Sistema de Gestao das Financas do Estado",
  description: "Plataforma institucional para execucao, monitorizacao e auditoria das financas publicas."
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="pt-AO" data-scroll-behavior="smooth">
      <body>
        <QueryProvider>{children}</QueryProvider>
      </body>
    </html>
  );
}
