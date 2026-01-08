<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Web リクエスト時のみロケールを動的設定
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app['request'];

        // Accept-Language ヘッダーから言語を取得
        $locale = $request->getPreferredLanguage(['ja', 'en']);

        // 対応言語リスト
        $supportedLocales = ['en', 'ja'];

        // サポートされている言語かチェック
        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale'));
        }
    }
}
