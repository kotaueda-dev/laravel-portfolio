import { apiClient, type ArticleSummary } from '@/lib/api-client';
import { LikeButton } from '@/app/components/LikeButton';
import Link from 'next/link';

export const dynamic = 'force-dynamic';

export default async function ArticlesPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const params = await searchParams;
  const page = Number(params.page) || 1;
  
  let articles: ArticleSummary[] = [];
  let currentPage = 1;
  let lastPage = 1;

  try {
    const { data, meta } = await apiClient.getArticles(page);
    articles = data;
    currentPage = meta.current_page;
    lastPage = meta.last_page;
  } catch (error) {
    return (
      <main className="mx-auto flex min-h-screen max-w-5xl flex-col gap-6 px-6 py-10">
        <h1 className="text-2xl font-bold">記事一覧</h1>
        <p className="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
          記事の取得に失敗しました。バックエンドが起動しているか確認してください。
        </p>
        {error instanceof Error && (
          <code className="rounded-md bg-zinc-900 px-3 py-2 text-xs text-zinc-100">{error.message}</code>
        )}
      </main>
    );
  }

  return (
    <main className="mx-auto flex min-h-screen max-w-5xl flex-col gap-6 px-6 py-10">
      
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm uppercase tracking-wide text-zinc-500">Articles</p>
          <h1 className="text-3xl font-bold text-zinc-900">記事一覧</h1>
        </div>
        <div className="rounded-full bg-zinc-100 px-4 py-2 text-sm text-zinc-700">
          ページ {currentPage} / {lastPage}
        </div>
      </div>

      {/* ページネーション（上部） */}
      <Pagination currentPage={currentPage} lastPage={lastPage} />

      {articles.length === 0 ? (
        <p className="text-sm text-zinc-600">記事がありません。</p>
      ) : (
        <ul className="space-y-4">
          {articles.map((article) => (
            <li
              key={article.id}
              className="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm hover:border-zinc-300"
            >
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-xs text-zinc-500">ID: {article.id}</p>
                  <Link href={`/articles/${article.id}`}>
                    <h2 className="text-lg font-semibold text-zinc-900 hover:text-blue-600 hover:underline">{article.title}</h2>
                  </Link>
                  <p className="text-xs text-zinc-500">
                    作成: {new Date(article.created_at).toLocaleString('ja-JP')}
                  </p>
                </div>
                <LikeButton articleId={article.id} initialLikes={article.like} size="sm" />
              </div>
            </li>
          ))}
        </ul>
      )}
      
      {/* ページネーション（下部） */}
      <Pagination currentPage={currentPage} lastPage={lastPage} />
    </main>
  );
}

function Pagination({ currentPage, lastPage }: { currentPage: number; lastPage: number }) {
  const pages = Array.from({ length: lastPage }, (_, i) => i + 1);
  
  return (
    <nav className="flex items-center justify-center gap-2">
      {/* 最初へボタン */}
      {currentPage > 1 ? (
        <Link
          href="/articles?page=1"
          className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          最初へ
        </Link>
      ) : (
        <span className="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
          最初へ
        </span>
      )}

      {/* 前へボタン */}
      {currentPage > 1 ? (
        <Link
          href={`/articles?page=${currentPage - 1}`}
          className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          前へ
        </Link>
      ) : (
        <span className="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
          前へ
        </span>
      )}

      {/* ページ番号 */}
      <div className="flex gap-2">
        {pages.map((page) => (
          page === currentPage ? (
            <span
              key={page}
              className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
            >
              {page}
            </span>
          ) : (
            <Link
              key={page}
              href={`/articles?page=${page}`}
              className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              {page}
            </Link>
          )
        ))}
      </div>

      {/* 次へボタン */}
      {currentPage < lastPage ? (
        <Link
          href={`/articles?page=${currentPage + 1}`}
          className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          次へ
        </Link>
      ) : (
        <span className="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
          次へ
        </span>
      )}

      {/* 最後へボタン */}
      {currentPage < lastPage ? (
        <Link
          href={`/articles?page=${lastPage}`}
          className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          最後へ
        </Link>
      ) : (
        <span className="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
          最後へ
        </span>
      )}
    </nav>
  );
}
