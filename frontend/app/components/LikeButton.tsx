'use client';

import { apiClient } from '@/lib/api-client';
import { useRouter } from 'next/navigation';
import { useState } from 'react';

interface LikeButtonProps {
  articleId: number;
  initialLikes: number;
  size?: 'sm' | 'md' | 'lg';
}

export function LikeButton({
  articleId,
  initialLikes,
  size = 'md',
}: LikeButtonProps) {
  const router = useRouter();
  const [isLiking, setIsLiking] = useState(false);
  const [liked, setLiked] = useState(false);
  const [likes, setLikes] = useState(initialLikes);

  const handleLike = async () => {
    if (isLiking) return;

    setIsLiking(true);
    setLiked(true);

    // 楽観的更新（即座にUI更新）
    setLikes((prev) => prev + 1);

    try {
      await apiClient.likeArticle(articleId);
      // サーバーデータを再取得
      router.refresh();

      // アニメーション後にリセット
      setTimeout(() => setLiked(false), 600);
    } catch (error) {
      // エラー時は元に戻す
      setLikes((prev) => prev - 1);
      setLiked(false);
      console.error('Failed to like article:', error);
    } finally {
      setIsLiking(false);
    }
  };

  const sizeClasses = {
    sm: 'gap-1 px-2 py-1 text-xs',
    md: 'gap-2 px-3 py-1 text-sm',
    lg: 'gap-2 px-4 py-2 text-base',
  };

  const heartSizeClasses = {
    sm: 'text-base',
    md: 'text-xl',
    lg: 'text-2xl',
  };

  return (
    <button
      onClick={handleLike}
      disabled={isLiking}
      className={`flex items-center rounded-full bg-pink-50 font-semibold text-pink-600 transition-all hover:bg-pink-100 disabled:opacity-70 ${sizeClasses[size]}`}
    >
      <span
        className={`inline-block transition-transform duration-300 ${
          liked ? 'animate-bounce' : ''
        } ${heartSizeClasses[size]}`}
        role="img"
        aria-label="likes"
      >
        ❤️
      </span>
      <span className={`tabular-nums ${liked ? 'animate-pulse' : ''}`}>
        {likes}
      </span>
    </button>
  );
}
