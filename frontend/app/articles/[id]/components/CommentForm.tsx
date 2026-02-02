'use client';

import { apiClient } from '@/lib/api-client';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useRouter } from 'next/navigation';
import { useState } from 'react';

interface CommentFormProps {
  articleId: number;
}

export function CommentForm({ articleId }: CommentFormProps) {
  const router = useRouter();
  const [message, setMessage] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccess(false);

    // フロントエンドバリデーション
    if (!message.trim()) {
      setError('コメントを入力してください。');
      return;
    }

    if (message.length > 500) {
      setError('コメントは500文字以内にしてください。');
      return;
    }

    setIsLoading(true);

    try {
      await apiClient.addComment(articleId, message.trim());
      setMessage('');
      setSuccess(true);

      // ページをリフレッシュしてサーバーキャッシュを無効化
      router.refresh();

      // 3秒後に成功メッセージを消す
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('コメント投稿に失敗しました。');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="space-y-2">
        <label htmlFor="message" className="text-sm font-medium">
          コメント
        </label>
        <Textarea
          id="message"
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          placeholder="ここにコメントを入力してください..."
          rows={4}
          disabled={isLoading}
          className="resize-none"
        />
        <div className="flex justify-between text-xs">
          <span className="text-muted-foreground">
            {message.length} / 500 文字
          </span>
          {message.length > 500 && (
            <span className="text-destructive font-medium">
              500文字を超えています
            </span>
          )}
        </div>
      </div>

      {/* エラーメッセージ */}
      {error && (
        <div className="rounded-lg border border-destructive/50 bg-destructive/10 px-4 py-3 text-sm">
          <p className="font-medium text-destructive">エラー</p>
          <p className="text-destructive/90">{error}</p>
        </div>
      )}

      {/* 成功メッセージ */}
      {success && (
        <div className="rounded-lg border border-green-500/50 bg-green-50 px-4 py-3 text-sm">
          <p className="font-medium text-green-700">成功</p>
          <p className="text-green-600">コメントが投稿されました。</p>
        </div>
      )}

      {/* 送信ボタン */}
      <Button
        type="submit"
        disabled={isLoading || message.trim().length === 0 || message.length > 500}
        className="w-full"
      >
        {isLoading ? 'コメント投稿中...' : 'コメントを投稿'}
      </Button>
    </form>
  );
}
