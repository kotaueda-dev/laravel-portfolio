import { apiClient, type ArticleSummary } from '@/lib/api-client';

export const dynamic = 'force-dynamic';

export default async function ArticlesPage() {
  let articles: ArticleSummary[] = [];
  let currentPage = 1;
  let lastPage = 1;

  try {
    const { data, meta } = await apiClient.getArticles();
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
                  <h2 className="text-lg font-semibold text-zinc-900">{article.title}</h2>
                  <p className="text-xs text-zinc-500">
                    作成: {new Date(article.created_at).toLocaleString('ja-JP')}
                  </p>
                </div>
                <div className="flex items-center gap-2 rounded-full bg-pink-50 px-3 py-1 text-sm text-pink-600">
                  <span aria-label="likes" role="img">
                    ❤️
                  </span>
                  <span className="font-semibold">{article.like}</span>
                </div>
              </div>
            </li>
          ))}
        </ul>
      )}
    </main>
  );
}
