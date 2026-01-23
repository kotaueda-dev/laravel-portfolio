'use client';

import { apiClient } from '@/lib/api-client';
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
      <div>
        <label htmlFor="message" className="block text-sm font-medium text-zinc-900">
          コメント
        </label>
        <textarea
          id="message"
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          placeholder="ここにコメントを入力してください..."
          rows={4}
          disabled={isLoading}
          className="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-zinc-100 disabled:text-zinc-500"
        />
        <div className="mt-1 flex justify-between">
          <p className="text-xs text-zinc-500">
            {message.length} / 500 文字
          </p>
          {message.length > 500 && (
            <p className="text-xs text-red-600">
              500文字を超えています
            </p>
          )}
        </div>
      </div>

      {/* エラーメッセージ */}
      {error && (
        <div className="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700 border border-red-200">
          <p className="font-medium">エラー</p>
          <p>{error}</p>
        </div>
      )}

      {/* 成功メッセージ */}
      {success && (
        <div className="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 border border-green-200">
          <p className="font-medium">成功</p>
          <p>コメントが投稿されました。</p>
        </div>
      )}

      {/* 送信ボタン */}
      <button
        type="submit"
        disabled={isLoading || message.trim().length === 0 || message.length > 500}
        className="w-full rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:bg-zinc-300 disabled:cursor-not-allowed transition-colors"
      >
        {isLoading ? 'コメント投稿中...' : 'コメントを投稿'}
      </button>
    </form>
  );
}
