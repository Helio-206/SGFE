export type Role = "ADMIN" | "GESTOR" | "AUDITOR";

export const ALL_ROLES: Role[] = ["ADMIN", "GESTOR", "AUDITOR"];

export function isRole(value: unknown): value is Role {
  return value === "ADMIN" || value === "GESTOR" || value === "AUDITOR";
}

export function allowedRolesForPath(pathname: string): Role[] | null {
  if (pathname === "/login") {
    return null;
  }

  if (pathname === "/dashboard" || pathname.startsWith("/dashboard/")) {
    return ALL_ROLES;
  }

  if (pathname === "/admin" || pathname.startsWith("/admin/")) {
    return ["ADMIN"];
  }

  if (pathname === "/gestao" || pathname.startsWith("/gestao/")) {
    return ["ADMIN", "GESTOR"];
  }

  if (pathname === "/relatorios" || pathname.startsWith("/relatorios/")) {
    return ALL_ROLES;
  }

  if (pathname === "/auditoria" || pathname.startsWith("/auditoria/")) {
    return ["ADMIN", "AUDITOR"];
  }

  if (pathname === "/perfil" || pathname.startsWith("/perfil/")) {
    return ALL_ROLES;
  }

  return null;
}
