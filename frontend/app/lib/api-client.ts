/**
 * Laravel REST API クライアント
 */

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

// APIのスキーマ定義
export interface ArticleSummary {
  id: number;
  user_id: number;
  title: string;
  like: number;
  created_at: string;
  updated_at: string;
}

export interface ArticleDetail extends ArticleSummary {
  content: string;
  comments?: CommentResponse[];
}

export interface CommentResponse {
  id: number;
  message: string;
  article_id: number;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: unknown;
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page?: number;
    to: number | null;
    total?: number;
  };
}

export interface LoginResponse {
  message: string;
  token: string;
  user: {
    id: number;
    name: string;
    email: string;
  };
}

/**
 * ログイン（Laravel /api/login）
 */
export async function login({
  email,
  password,
}: {
  email: string;
  password: string;
}): Promise<LoginResponse> {
  const res = await fetch(`${API_URL}/login`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({ email, password }),
  });

  if (!res.ok) {
    let message = "ログインに失敗しました";
    try {
      const data = await res.json();
      message = data.message || message;
    } catch {}
    throw new Error(message);
  }
  return res.json();
}

/**
 * 全記事一覧を取得
 */
export async function getArticles(page = 1) {
  const res = await fetch(`${API_URL}/articles?page=${page}`, {
    cache: "no-store",
  });
  if (!res.ok) throw new Error("Failed to fetch articles");
  return res.json() as Promise<PaginatedResponse<ArticleSummary>>;
}

/**
 * 記事詳細を取得
 */
export async function getArticle(id: number) {
  const res = await fetch(`${API_URL}/articles/${id}`, { cache: "no-store" });
  if (!res.ok) throw new Error("Failed to fetch article");
  return res.json() as Promise<ArticleDetail>;
}

/**
 * 新規記事を作成
 */
export async function createArticle(data: {
  title: string;
  content: string;
  username: string;
}) {
  const res = await fetch(`${API_URL}/articles`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  });
  if (!res.ok) throw new Error("Failed to create article");
  return res.json() as Promise<ArticleDetail>;
}

/**
 * 記事のいいね数をインクリメント
 */
export async function likeArticle(id: number) {
  const res = await fetch(`${API_URL}/articles/${id}/likes`, {
    method: "POST",
  });
  if (!res.ok) throw new Error("Failed to like article");
  return res.json() as Promise<ArticleDetail>;
}

/**
 * 記事にコメントを追加
 */
export async function addComment(articleId: number, message: string) {
  const res = await fetch(`${API_URL}/articles/${articleId}/comments`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message }),
  });
  if (!res.ok) throw new Error("Failed to add comment");
  return res.json() as Promise<CommentResponse>;
}
