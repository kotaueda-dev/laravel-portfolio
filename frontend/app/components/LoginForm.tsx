"use client";

import { useState } from "react";
import * as z from "zod";
import { LockKeyhole, Loader2, Eye, EyeOff } from "lucide-react";
import Cookies from "js-cookie";

import { login } from "@/lib/api-client";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Field,
  FieldContent,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";

const loginSchema = z.object({
  email: z.email({ message: "有効なメールアドレスを入力してください" }),
  password: z
    .string()
    .min(1, { message: "パスワードを入力してください" })
    .min(8, { message: "パスワードは8文字以上で入力してください" }),
});

type LoginFormData = z.infer<typeof loginSchema>;

type FormErrors = {
  email?: string;
  password?: string;
};

export default function LoginForm() {
  const [formData, setFormData] = useState<LoginFormData>({
    email: "",
    password: "",
  });
  const [errors, setErrors] = useState<FormErrors>({});
  const [authError, setAuthError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    // Clear field error when user starts typing
    if (errors[name as keyof FormErrors]) {
      setErrors((prev) => ({ ...prev, [name]: undefined }));
    }
    // Clear auth error when user modifies form
    if (authError) {
      setAuthError(null);
    }
  };

  const handleSubmit = async (e: React.SubmitEvent<HTMLFormElement>) => {
    e.preventDefault();
    setErrors({});
    setAuthError(null);

    const result = loginSchema.safeParse(formData);
    if (!result.success) {
      const flattened = z.flattenError(result.error);
      setErrors({
        email: flattened.fieldErrors.email?.[0],
        password: flattened.fieldErrors.password?.[0],
      });
      return;
    }

    setIsLoading(true);
    try {
      const response = await login({
        email: formData.email,
        password: formData.password,
      });

      // レスポンスが成功（200）であることを確認
      if (!response.token) {
        throw new Error("トークンを取得できませんでした");
      }

      // トークンをクッキーに保存
      Cookies.set("token", response.token, {
        expires: 7, // 7日間有効
        secure: process.env.NODE_ENV === "production", // 本番環境のみHTTPSを強制
        sameSite: "strict", // CSRF 対策
      });

      // ログイン成功 - ホームページへリダイレクト
      window.location.href = "/dashboard";
    } catch (error) {
      // API エラーメッセージを表示
      const message =
        error instanceof Error
          ? error.message
          : "ログインに失敗しました。もう一度お試しください。";
      setAuthError(message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <Card className="mx-auto w-full max-w-sm">
        <CardHeader className="space-y-2 text-center">
          <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-primary">
            <LockKeyhole className="size-7 text-primary-foreground" />
          </div>
          <CardTitle className="text-xl">ログイン</CardTitle>
        </CardHeader>
        <FieldSet className="w-full">
          <FieldGroup className="w-full">
            <CardContent>
              <form
                id="login-form"
                className="space-y-4"
                onSubmit={handleSubmit}
                aria-label="ログインフォーム"
              >
                <div role="alert" aria-live="polite" aria-atomic="true">
                  {authError && (
                    <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                      {authError}
                    </div>
                  )}
                </div>
                <Field>
                  <FieldLabel htmlFor="email">メールアドレス</FieldLabel>
                  <FieldContent>
                    <Input
                      id="email"
                      name="email"
                      type="email"
                      placeholder="example@email.com"
                      value={formData.email}
                      onChange={handleChange}
                      autoComplete="email"
                      aria-invalid={!!errors.email}
                      aria-describedby={
                        errors.email ? "email-error" : undefined
                      }
                      required
                    />
                    {errors.email && (
                      <p
                        id="email-error"
                        className="text-sm text-destructive"
                        role="alert"
                      >
                        {errors.email}
                      </p>
                    )}
                  </FieldContent>
                </Field>
                <Field>
                  <FieldLabel htmlFor="password">パスワード</FieldLabel>
                  <FieldContent>
                    <div className="relative">
                      <Input
                        id="password"
                        name="password"
                        type={showPassword ? "text" : "password"}
                        placeholder="••••••••"
                        value={formData.password}
                        onChange={handleChange}
                        autoComplete="current-password"
                        aria-invalid={!!errors.password}
                        aria-describedby={
                          errors.password ? "password-error" : undefined
                        }
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                        aria-label={
                          showPassword
                            ? "パスワードを非表示"
                            : "パスワードを表示"
                        }
                        tabIndex={-1}
                      >
                        {showPassword ? (
                          <EyeOff className="size-5" aria-hidden="true" />
                        ) : (
                          <Eye className="size-5" aria-hidden="true" />
                        )}
                      </button>
                    </div>
                    {errors.password && (
                      <p
                        id="password-error"
                        className="text-sm text-destructive"
                        role="alert"
                      >
                        {errors.password}
                      </p>
                    )}
                  </FieldContent>
                </Field>
                <Button
                  className="w-full mt-4"
                  form="login-form"
                  type="submit"
                  disabled={isLoading}
                  aria-label={isLoading ? "ログイン処理中" : "ログイン"}
                >
                  {isLoading ? (
                    <>
                      <Loader2 className="animate-spin" aria-hidden="true" />
                      ログイン中...
                    </>
                  ) : (
                    "ログイン"
                  )}
                </Button>
              </form>
            </CardContent>
          </FieldGroup>
        </FieldSet>
      </Card>
    </>
  );
}
