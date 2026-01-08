<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Web リクエスト時のみロケールを動的設定
        if ($this->app->runningInConsole()) {
            return;
        }

        // 対応言語リスト
        $supportedLocales = ['ja', 'en'];

        $request = $this->app['request'];

        // Accept-Language ヘッダーから言語を取得
        $locale = $request->getPreferredLanguage($supportedLocales) ?? config('app.locale');

        App::setLocale($locale);
    }
}
