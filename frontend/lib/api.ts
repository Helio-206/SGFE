export const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8080/api";

let refreshInFlight: Promise<void> | null = null;

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number
  ) {
    super(message);
  }
}

export type DashboardData = {
  anoFiscal: number;
  contexto: "UO" | "NACIONAL";
  tectoTotal: number;
  valorComprometido: number;
  valorPago: number;
  totalReceita: number;
  saldoDisponivel: number;
  percentualExecucao: number;
  riscoOrcamental: "CONTROLADO" | "MODERADO" | "ALTO" | "CRITICO";
  topUos: Array<{ codigo: string; nome: string; tecto: number; comprometido: number; percentual: number }>;
};

export type PageResponse<T> = {
  content: T[];
  totalElements: number;
  totalPages: number;
  number: number;
  size: number;
};

export type Instituicao = {
  id: number;
  codigo: string;
  nome: string;
  tipo: string;
  responsavel: string;
  status: string;
};

export type Orcamento = {
  id: number;
  idInst: number;
  codigoUo: string;
  instituicao: string;
  valorTotal: number;
  valorComprometido: number;
  saldoDisponivel: number;
  anoFiscal: number;
};

export type Classificacao = {
  id: number;
  codigo: string;
  descricao: string;
  tipo: string;
};

export type User = {
  id: number;
  nome: string;
  username: string;
  email: string;
  role: "ADMIN" | "GESTOR" | "AUDITOR";
  status: "ATIVO" | "INATIVO";
  idInst: number;
  codigoUo: string;
  instituicao: string;
};

export type Receita = {
  id: number;
  idInst: number;
  codigoUo: string;
  instituicao: string;
  fonteReceita: "PETROLIFERA" | "NAO_PETROLIFERA" | "PATRIMONIAL";
  codigoRupe: string;
  dataRegistro: string;
  valorArrecadado: number;
  idClasse: number;
  codigoClasse: string;
};

export type Despesa = {
  id: number;
  idInst: number;
  codigoUo: string;
  instituicao: string;
  descricao: string;
  valorBruto: number;
  dataRegistro: string;
  estado: import("@/lib/finance").FinancialState;
  idClasse: number;
  codigoClasse: string;
};

export type AuditLog = {
  id: number;
  idUser?: number;
  usuario?: string;
  idInst?: number;
  codigoUo?: string;
  acao: string;
  entidade?: string;
  entidadeId?: string;
  resultado: string;
  severidade: "INFO" | "ALERTA" | "CRITICO";
  ipAddress?: string;
  contexto?: string;
  createdAt: string;
};

function buildHeaders(headers?: HeadersInit, hasBody = false) {
  const merged = new Headers(headers);
  merged.set("X-Requested-With", "XMLHttpRequest");
  if (hasBody && !merged.has("Content-Type")) {
    merged.set("Content-Type", "application/json");
  }
  return merged;
}

async function readErrorMessage(response: Response) {
  try {
    const payload = (await response.json()) as { message?: string };
    if (typeof payload.message === "string" && payload.message.trim().length > 0) {
      return payload.message;
    }
  } catch {
    // noop
  }

  if (response.status === 401) {
    return "Sessao expirada. Inicie sessao novamente.";
  }

  return "Nao foi possivel concluir a operacao.";
}

async function refreshSession() {
  if (!refreshInFlight) {
    refreshInFlight = (async () => {
      const response = await fetch(`${API_BASE_URL}/auth/refresh`, {
        method: "POST",
        credentials: "include",
        cache: "no-store",
        headers: buildHeaders(undefined, true),
        body: "{}"
      });

      if (!response.ok) {
        throw new ApiError(await readErrorMessage(response), response.status);
      }
    })().finally(() => {
      refreshInFlight = null;
    });
  }

  return refreshInFlight;
}

export async function apiFetch<T>(path: string, init?: RequestInit, retryOnUnauthorized = true): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...init,
    credentials: "include",
    cache: "no-store",
    headers: buildHeaders(init?.headers, init?.body !== undefined)
  });

  if (response.status === 401 && retryOnUnauthorized && path !== "/auth/refresh" && path !== "/auth/login") {
    await refreshSession();
    return apiFetch<T>(path, init, false);
  }

  if (!response.ok) {
    throw new ApiError(await readErrorMessage(response), response.status);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return response.json() as Promise<T>;
}

export async function logoutRequest() {
  const response = await fetch(`${API_BASE_URL}/auth/logout`, {
    method: "POST",
    credentials: "include",
    cache: "no-store",
    headers: buildHeaders(undefined, true),
    body: "{}"
  });

  if (!response.ok && response.status !== 401) {
    throw new ApiError(await readErrorMessage(response), response.status);
  }
}

export async function downloadFile(path: string, fileName: string, retryOnUnauthorized = true) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: "include",
    cache: "no-store",
    headers: buildHeaders()
  });

  if (response.status === 401 && retryOnUnauthorized) {
    await refreshSession();
    return downloadFile(path, fileName, false);
  }

  if (!response.ok) {
    throw new ApiError(await readErrorMessage(response), response.status);
  }

  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement("a");
  anchor.href = url;
  anchor.download = fileName;
  anchor.click();
  URL.revokeObjectURL(url);
}

export const emptyDashboard: DashboardData = {
  anoFiscal: new Date().getFullYear(),
  contexto: "NACIONAL",
  tectoTotal: 0,
  valorComprometido: 0,
  valorPago: 0,
  totalReceita: 0,
  saldoDisponivel: 0,
  percentualExecucao: 0,
  riscoOrcamental: "CONTROLADO",
  topUos: []
};
