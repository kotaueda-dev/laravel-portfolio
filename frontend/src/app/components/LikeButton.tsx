'use client';

import { apiClient } from '@/lib/api-client';
import { Button } from '@/components/ui/button';
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

  const sizeMap = {
    sm: 'sm' as const,
    md: 'default' as const,
    lg: 'lg' as const,
  };

  return (
    <Button
      onClick={handleLike}
      disabled={isLiking}
      variant="secondary"
      size={sizeMap[size]}
      className="gap-2"
    >
      <span
        className={`inline-block text-lg transition-transform duration-300 ${
          liked ? 'animate-bounce' : ''
        }`}
        role="img"
        aria-label="likes"
      >
        ❤️
      </span>
      <span className={`tabular-nums font-semibold ${liked ? 'animate-pulse' : ''}`}>
        {likes}
      </span>
    </Button>
  );
}
