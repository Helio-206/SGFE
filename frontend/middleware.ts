import { NextResponse, type NextRequest } from "next/server";
import { allowedRolesForPath, isRole } from "@/lib/rbac";

const ACCESS_COOKIE = "SGFE_ACCESS_TOKEN";

function isProtectedPath(pathname: string) {
  return ["/dashboard", "/admin", "/gestao", "/relatorios", "/auditoria", "/perfil"].some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`)
  );
}

function decodeRole(token: string) {
  try {
    const payload = token.split(".")[1];
    if (!payload) {
      return null;
    }

    const base64 = payload.replace(/-/g, "+").replace(/_/g, "/").padEnd(Math.ceil(payload.length / 4) * 4, "=");
    const parsed = JSON.parse(atob(base64)) as { role?: unknown };
    return isRole(parsed.role) ? parsed.role : null;
  } catch {
    return null;
  }
}

export function middleware(request: NextRequest) {
  const { pathname, search } = request.nextUrl;
  const accessToken = request.cookies.get(ACCESS_COOKIE)?.value;
  const hasSession = Boolean(accessToken);

  if (pathname === "/login" && hasSession) {
    return NextResponse.redirect(new URL("/dashboard", request.url));
  }

  if (isProtectedPath(pathname) && !hasSession) {
    const loginUrl = new URL("/login", request.url);
    loginUrl.searchParams.set("next", `${pathname}${search}`);
    return NextResponse.redirect(loginUrl);
  }

  const allowedRoles = allowedRolesForPath(pathname);
  if (allowedRoles && accessToken) {
    const role = decodeRole(accessToken);
    if (!role || !allowedRoles.includes(role)) {
      return NextResponse.redirect(new URL("/dashboard", request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/dashboard/:path*", "/admin/:path*", "/gestao/:path*", "/relatorios/:path*", "/auditoria/:path*", "/perfil/:path*", "/login"]
};
