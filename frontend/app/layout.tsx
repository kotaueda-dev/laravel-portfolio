import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Laravel Portfolio",
  description: "Laravel 12 API + Next.js 14 モノレポプロジェクト",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-gradient-to-br from-gray-50 to-gray-100`}
      >
        {/* ヘッダー */}
        <header className="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
          <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div className="flex items-center gap-8">
              <Link href="/" className="flex items-center gap-2">
                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                  <span className="text-lg font-bold">L</span>
                </div>
                <span className="text-xl font-bold">Laravel Portfolio</span>
              </Link>
              <nav className="hidden md:flex md:gap-6">
                <Link 
                  href="/articles" 
                  className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                >
                  記事一覧
                </Link>
              </nav>
            </div>
            <div className="flex items-center gap-4">
              <Button variant="outline" size="sm" asChild>
                <Link href="/articles">記事を見る</Link>
              </Button>
              <Button size="sm">ログイン</Button>
            </div>
          </div>
        </header>

        {/* メインコンテンツ */}
        <main className="min-h-screen">
          {children}
        </main>

        {/* フッター */}
        <footer className="border-t bg-background">
          <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
              <Card>
                <CardHeader>
                  <CardTitle className="text-base">Laravel Portfolio</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-muted-foreground">
                    Laravel REST API + Next.js のモノレポプロジェクト
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-base">リンク</CardTitle>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-2 text-sm">
                    <li>
                      <Link 
                        href="/articles" 
                        className="text-muted-foreground transition-colors hover:text-primary"
                      >
                        記事一覧
                      </Link>
                    </li>
                    <li>
                      <Link 
                        href="/" 
                        className="text-muted-foreground transition-colors hover:text-primary"
                      >
                        ホーム
                      </Link>
                    </li>
                  </ul>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="text-base">技術スタック</CardTitle>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-2 text-sm text-muted-foreground">
                    <li>• Laravel 12 + PHP 8.5</li>
                    <li>• Next.js 14 + TypeScript</li>
                    <li>• Tailwind CSS + shadcn/ui</li>
                    <li>• Docker + Nginx + PHP-FPM</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <Separator className="my-8" />
            
            <div className="text-center text-sm text-muted-foreground">
              © 2026 Laravel Portfolio. All rights reserved.
            </div>
          </div>
        </footer>
      </body>
    </html>
  );
}
