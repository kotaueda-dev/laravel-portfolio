import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

export function middleware(request: NextRequest) {
  // クッキーから認証トークンを取得
  const token = request.cookies.get("token")?.value;

  // /dashboard/* パスへのアクセスで、トークンがない場合はログインページへリダイレクト
  if (request.nextUrl.pathname.startsWith("/dashboard")) {
    if (!token) {
      const loginUrl = new URL("/login", request.url);
      // リダイレクト後に元のページに戻るためのパラメータを追加（オプション）
      loginUrl.searchParams.set("redirect", request.nextUrl.pathname);
      return NextResponse.redirect(loginUrl);
    }
  }

  // /login ページへのアクセスで、すでにログイン済みの場合は /dashboard にリダイレクト
  if (request.nextUrl.pathname === "/login") {
    if (token) {
      return NextResponse.redirect(new URL("/dashboard", request.url));
    }
  }

  return NextResponse.next();
}

// Middleware を適用するパスを指定
export const config = {
  matcher: [
    "/dashboard/:path*", // /dashboard 配下すべて
    "/login", // ログインページ
  ],
};
