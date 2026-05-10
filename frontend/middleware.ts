import { NextResponse, type NextRequest } from "next/server";

const ACCESS_COOKIE = "SGFE_ACCESS_TOKEN";

function isProtectedPath(pathname: string) {
  return ["/dashboard", "/admin", "/gestao", "/relatorios", "/auditoria", "/perfil"].some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`)
  );
}

export function middleware(request: NextRequest) {
  const { pathname, search } = request.nextUrl;
  const hasSession = Boolean(request.cookies.get(ACCESS_COOKIE)?.value);

  if (pathname === "/login" && hasSession) {
    return NextResponse.redirect(new URL("/dashboard", request.url));
  }

  if (isProtectedPath(pathname) && !hasSession) {
    const loginUrl = new URL("/login", request.url);
    loginUrl.searchParams.set("next", `${pathname}${search}`);
    return NextResponse.redirect(loginUrl);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/dashboard/:path*", "/admin/:path*", "/gestao/:path*", "/relatorios/:path*", "/auditoria/:path*", "/perfil/:path*", "/login"]
};