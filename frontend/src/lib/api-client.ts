/**
 * Laravel REST API クライアント
 */

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

interface ArticleResponse {
  id: number;
  title: string;
  content: string;
  username: string;
  like_count: number;
  created_at: string;
  updated_at: string;
}

interface CommentResponse {
  id: number;
  message: string;
  article_id: number;
  created_at: string;
  updated_at: string;
}

export const apiClient = {
  /**
   * 全記事一覧を取得
   */
  async getArticles() {
    const res = await fetch(`${API_URL}/articles`);
    if (!res.ok) throw new Error('Failed to fetch articles');
    return res.json() as Promise<ArticleResponse[]>;
  },

  /**
   * 記事詳細を取得
   */
  async getArticle(id: number) {
    const res = await fetch(`${API_URL}/articles/${id}`);
    if (!res.ok) throw new Error('Failed to fetch article');
    return res.json() as Promise<ArticleResponse>;
  },

  /**
   * 新規記事を作成
   */
  async createArticle(data: {
    title: string;
    content: string;
    username: string;
  }) {
    const res = await fetch(`${API_URL}/articles`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error('Failed to create article');
    return res.json() as Promise<ArticleResponse>;
  },

  /**
   * 記事のいいね数をインクリメント
   */
  async likeArticle(id: number) {
    const res = await fetch(`${API_URL}/articles/${id}/likes`, {
      method: 'POST',
    });
    if (!res.ok) throw new Error('Failed to like article');
    return res.json() as Promise<ArticleResponse>;
  },

  /**
   * 記事にコメントを追加
   */
  async addComment(articleId: number, message: string) {
    const res = await fetch(`${API_URL}/articles/${articleId}/comments`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message }),
    });
    if (!res.ok) throw new Error('Failed to add comment');
    return res.json() as Promise<CommentResponse>;
  },
};
