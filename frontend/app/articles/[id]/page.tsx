import {
  getArticle,
  type ArticleDetail,
  type CommentResponse,
} from "@/lib/api-client";
import { LikeButton } from "@/components/LikeButton";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import Link from "next/link";
import { CommentForm } from "./components/CommentForm";

export const dynamic = "force-dynamic";

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
    article = await getArticle(articleId);
    comments = article.comments || [];
  } catch (error) {
    return (
      <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-6 py-10">
        <Link
          href="/articles"
          className="text-sm text-blue-600 hover:underline"
        >
          ← 一覧に戻る
        </Link>
        <p className="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
          記事の取得に失敗しました。
        </p>
        {error instanceof Error && (
          <code className="rounded-md bg-zinc-900 px-3 py-2 text-xs text-zinc-100">
            {error.message}
          </code>
        )}
      </main>
    );
  }

  if (!article) {
    return (
      <main className="mx-auto flex min-h-screen max-w-3xl flex-col gap-6 px-6 py-10">
        <Link
          href="/articles"
          className="text-sm text-blue-600 hover:underline"
        >
          ← 一覧に戻る
        </Link>
        <p className="text-sm text-zinc-600">記事が見つかりません。</p>
      </main>
    );
  }

  return (
    <main className="mx-auto flex min-h-screen max-w-4xl flex-col gap-8 px-6 py-10">
      {/* 戻るリンク */}
      <Button variant="ghost" size="sm" asChild>
        <Link href="/articles">← 一覧に戻る</Link>
      </Button>

      {/* 記事情報 */}
      <Card>
        <CardHeader>
          <div className="mb-4 flex items-start justify-between gap-4">
            <div className="flex-1 space-y-3">
              <Badge variant="outline">ID: {article.id}</Badge>
              <CardTitle className="text-3xl tracking-tight">
                {article.title}
              </CardTitle>
              <p className="text-sm text-muted-foreground">
                {new Date(article.created_at).toLocaleString("ja-JP")}
              </p>
            </div>
            <LikeButton
              articleId={article.id}
              initialLikes={article.like}
              size="lg"
            />
          </div>
        </CardHeader>
        <Separator />
        <CardContent className="pt-6">
          <div className="prose prose-sm max-w-none">
            <p className="whitespace-pre-wrap leading-relaxed text-foreground/90">
              {article.content}
            </p>
          </div>
        </CardContent>
      </Card>

      {/* コメントセクション */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            コメント
            <Badge variant="secondary">{comments.length}</Badge>
          </CardTitle>
        </CardHeader>
        <CardContent>
          {comments.length === 0 ? (
            <p className="text-center text-sm text-muted-foreground py-8">
              コメントはまだありません。
            </p>
          ) : (
            <div className="space-y-4">
              {comments.map((comment) => (
                <div
                  key={comment.id}
                  className="rounded-lg border-l-4 border-primary/50 bg-muted/30 p-4"
                >
                  <div className="mb-2 flex items-center gap-2">
                    <Badge variant="outline" className="text-xs">
                      #{comment.id}
                    </Badge>
                    <span className="text-xs text-muted-foreground">
                      {new Date(comment.created_at).toLocaleString("ja-JP")}
                    </span>
                  </div>
                  <p className="leading-relaxed">{comment.message}</p>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* コメント投稿フォーム */}
      <Card>
        <CardHeader>
          <CardTitle>コメントを追加</CardTitle>
        </CardHeader>
        <CardContent>
          <CommentForm articleId={articleId} />
        </CardContent>
      </Card>
    </main>
  );
}
