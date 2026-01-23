import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import Link from "next/link";

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
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-gray-50`}
      >
        {/* ヘッダー */}
        <header className="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
          <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div className="flex items-center gap-8">
              <Link href="/" className="text-2xl font-bold text-gray-900">
                Laravel Portfolio
              </Link>
              {/* <nav className="hidden md:flex md:gap-6">
                <a href="/articles" className="text-sm font-medium text-gray-700 hover:text-gray-900">
                  記事一覧
                </a>
              </nav> */}
            </div>
            <div className="flex items-center gap-4">
              <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                ログイン
              </button>
            </div>
          </div>
        </header>

        {/* メインコンテンツ */}
        <main className="min-h-screen">
          {children}
        </main>

        {/* フッター */}
        <footer className="border-t border-gray-200 bg-white">
          <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
              <div>
                <h3 className="mb-4 text-sm font-semibold text-gray-900">Laravel Portfolio</h3>
                <p className="text-sm text-gray-600">
                  Laravel REST API + Next.js
                </p>
              </div>
              <div>
                <h3 className="mb-4 text-sm font-semibold text-gray-900">リンク</h3>
                <ul className="space-y-2 text-sm text-gray-600">
                  <li>
                    <Link href="/articles" className="hover:text-gray-900">記事一覧</Link>
                  </li>
                </ul>
              </div>
              <div>
                <h3 className="mb-4 text-sm font-semibold text-gray-900">技術スタック</h3>
                <ul className="space-y-2 text-sm text-gray-600">
                  <li>Laravel 12 + PHP 8.5</li>
                  <li>Next.js 14 + TypeScript</li>
                  <li>Tailwind CSS</li>
                </ul>
              </div>
            </div>
            <div className="mt-8 border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
              © 2026 Laravel Portfolio. All rights reserved.
            </div>
          </div>
        </footer>
      </body>
    </html>
  );
}
