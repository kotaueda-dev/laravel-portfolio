import { apiClient, type ArticleDetail, type CommentResponse } from '@/lib/api-client';
import Link from 'next/link';
import { CommentForm } from './components/CommentForm';

export const dynamic = 'force-dynamic';

export default async function ArticleDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const articleId = Number(id);

  let article: ArticleDetail | null = null;
  let comments: CommentResponse[] = [];

  try {
    article = await apiClient.getArticle(articleId);
    comments = article.comments || [];
  } catch (error) {
    return (
      <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-6 py-10">
        <Link href="/articles" className="text-sm text-blue-600 hover:underline">
          ← 一覧に戻る
        </Link>
        <p className="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
          記事の取得に失敗しました。
        </p>
        {error instanceof Error && (
          <code className="rounded-md bg-zinc-900 px-3 py-2 text-xs text-zinc-100">{error.message}</code>
        )}
      </main>
    );
  }

  if (!article) {
    return (
      <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-6 py-10">
        <Link href="/articles" className="text-sm text-blue-600 hover:underline">
          ← 一覧に戻る
        </Link>
        <p className="text-sm text-zinc-600">記事が見つかりません。</p>
      </main>
    );
  }

  return (
    <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-6 py-10">
      {/* 戻るリンク */}
      <Link href="/articles" className="text-sm text-blue-600 hover:underline">
        ← 一覧に戻る
      </Link>

      {/* 記事情報 */}
      <article className="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <div className="mb-4 flex items-start justify-between">
          <div>
            <p className="text-xs text-zinc-500">ID: {article.id}</p>
            <h1 className="text-3xl font-bold text-zinc-900">{article.title}</h1>
            <p className="mt-2 text-sm text-zinc-600">
              作成: {new Date(article.created_at).toLocaleString('ja-JP')}
            </p>
          </div>
          <div className="flex items-center gap-2 rounded-full bg-pink-50 px-4 py-2 text-lg text-pink-600">
            <span role="img" aria-label="likes">
              ❤️
            </span>
            <span className="font-semibold">{article.like}</span>
          </div>
        </div>

        {/* 記事内容 */}
        <div className="prose prose-sm max-w-none border-t border-zinc-200 pt-6">
          <div className="whitespace-pre-wrap text-zinc-700">{article.content}</div>
        </div>
      </article>

      {/* コメントセクション */}
      <section className="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <h2 className="mb-4 text-xl font-bold text-zinc-900">
          コメント ({comments.length})
        </h2>

        {comments.length === 0 ? (
          <p className="text-sm text-zinc-600">コメントはまだありません。</p>
        ) : (
          <ul className="space-y-4">
            {comments.map((comment) => (
              <li
                key={comment.id}
                className="border-l-4 border-blue-500 bg-zinc-50 p-4"
              >
                <p className="mb-2 text-sm font-semibold text-zinc-900">
                  コメント #{comment.id}
                </p>
                <p className="mb-3 text-zinc-700">{comment.message}</p>
                <p className="text-xs text-zinc-500">
                  {new Date(comment.created_at).toLocaleString('ja-JP')}
                </p>
              </li>
            ))}
          </ul>
        )}
      </section>

      {/* コメント投稿フォーム */}
      <section className="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <h3 className="mb-4 text-lg font-bold text-zinc-900">コメントを追加</h3>
        <CommentForm articleId={articleId} />
      </section>
    </main>
  );
}
