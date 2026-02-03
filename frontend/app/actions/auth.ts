"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export interface LoginResponse {
  message: string;
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
}

/**
 * ログイン処理（Server Action）
 * httpOnly クッキーでトークンを保存することでXSS対策
 */
export async function loginAction(email: string, password: string) {
  try {
    const res = await fetch(`${API_URL}/login`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email, password }),
      cache: "no-store",
    });

    if (!res.ok) {
      let message = "ログインに失敗しました";
      try {
        const data = await res.json();
        message = data.message || message;
      } catch {}
      return { success: false, error: message };
    }

    const data: LoginResponse = await res.json();

    if (!data.token) {
      return { success: false, error: "トークンを取得できませんでした" };
    }

    // httpOnly フラグ付きでクッキーに保存（JavaScript からアクセス不可）
    const cookieStore = await cookies();
    cookieStore.set("token", data.token, {
      httpOnly: true, // XSS 対策：JavaScript からアクセス不可
      secure: process.env.NODE_ENV === "production", // 本番環境のみ HTTPS
      sameSite: "strict", // CSRF 対策
      maxAge: 60 * 60 * 24 * 7, // 7日間
      path: "/dashboard", // ダッシュボード配下で有効
    });

    return { success: true };
  } catch (error) {
    return {
      success: false,
      error:
        error instanceof Error
          ? error.message
          : "ログインに失敗しました。もう一度お試しください。",
    };
  }
}

/**
 * ログアウト処理（Server Action）
 */
export async function logoutAction() {
  const cookieStore = await cookies();
  cookieStore.delete("token");
  redirect("/login");
}
