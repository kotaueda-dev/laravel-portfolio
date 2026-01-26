import { apiClient, type ArticleSummary } from '@/lib/api-client';
import { LikeButton } from '@/components/LikeButton';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    <main className="mx-auto flex min-h-screen max-w-5xl flex-col gap-8 px-6 py-10">
      
      <div className="flex items-center justify-between">
        <div>
          <Badge variant="secondary" className="mb-2">Articles</Badge>
          <h1 className="text-4xl font-bold tracking-tight">記事一覧</h1>
        </div>
        <Badge variant="outline" className="h-9 px-4">
          ページ {currentPage} / {lastPage}
        </Badge>
      </div>

      {/* ページネーション（上部） */}
      <Pagination currentPage={currentPage} lastPage={lastPage} />

      {articles.length === 0 ? (
        <Card>
          <CardContent className="py-10 text-center">
            <p className="text-muted-foreground">記事がありません。</p>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-4">
          {articles.map((article) => (
            <Card
              key={article.id}
              className="transition-all hover:shadow-md"
            >
              <CardHeader>
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 space-y-2">
                    <Badge variant="outline" className="text-xs">
                      ID: {article.id}
                    </Badge>
                    <Link href={`/articles/${article.id}`}>
                      <h2 className="text-xl font-semibold tracking-tight transition-colors hover:text-primary">
                        {article.title}
                      </h2>
                    </Link>
                    <p className="text-sm text-muted-foreground">
                      {new Date(article.created_at).toLocaleString('ja-JP')}
                    </p>
                  </div>
                  <LikeButton articleId={article.id} initialLikes={article.like} size="sm" />
                </div>
              </CardHeader>
            </Card>
          ))}
        </div>
      )}
      
      {/* ページネーション（下部） */}
      <Pagination currentPage={currentPage} lastPage={lastPage} />
    </main>
  );
}

function Pagination({ currentPage, lastPage }: { currentPage: number; lastPage: number }) {
  const pages = Array.from({ length: lastPage }, (_, i) => i + 1);
  
  return (
    <nav className="flex items-center justify-center gap-2 flex-wrap">
      {/* 最初へボタン */}
      <Button variant="outline" size="sm" asChild disabled={currentPage === 1}>
        <Link href="/articles?page=1">最初へ</Link>
      </Button>

      {/* 前へボタン */}
      <Button variant="outline" size="sm" asChild disabled={currentPage === 1}>
        <Link href={`/articles?page=${currentPage - 1}`}>前へ</Link>
      </Button>

      {/* ページ番号 */}
      <div className="flex gap-2">
        {pages.map((page) => (
          page === currentPage ? (
            <Button
              key={page}
              size="sm"
              disabled
            >
              {page}
            </Button>
          ) : (
            <Button
              key={page}
              variant="outline"
              size="sm"
              asChild
            >
              <Link href={`/articles?page=${page}`}>{page}</Link>
            </Button>
          )
        ))}
      </div>

      {/* 次へボタン */}
      <Button variant="outline" size="sm" asChild disabled={currentPage === lastPage}>
        <Link href={`/articles?page=${currentPage + 1}`}>次へ</Link>
      </Button>

      {/* 最後へボタン */}
      <Button variant="outline" size="sm" asChild disabled={currentPage === lastPage}>
        <Link href={`/articles?page=${lastPage}`}>最後へ</Link>
      </Button>
    </nav>
  );
}
