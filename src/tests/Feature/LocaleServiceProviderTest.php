<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocaleServiceProviderTest extends TestCase
{
    private const NOT_FOUND_JA = 'リソースが見つかりません。';

    private const NOT_FOUND_EN = 'Not found.';

    #[Test]
    public function accept_language_ja_returns_japanese_error_message(): void
    {
        Config::set('app.locale', 'ja');

        $response = $this->getJson('/api/articles/99999999', [
            'Accept-Language' => 'ja',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => self::NOT_FOUND_JA]);
    }

    #[Test]
    public function accept_language_en_returns_english_error_message(): void
    {
        Config::set('app.locale', 'en');

        $response = $this->getJson('/api/articles/99999999', [
            'Accept-Language' => 'en',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => self::NOT_FOUND_EN]);
    }

    #[Test]
    public function unsupported_language_falls_back_to_default_locale(): void
    {
        Config::set('app.locale', 'ja');

        $response = $this->getJson('/api/articles/99999999', [
            'Accept-Language' => 'fr',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => self::NOT_FOUND_JA]);
    }

    #[Test]
    public function no_accept_language_header_uses_default_locale(): void
    {
        Config::set('app.locale', 'ja');

        $response = $this->getJson('/api/articles/99999999');

        $response->assertStatus(404);
        $response->assertJson(['message' => self::NOT_FOUND_JA]);
    }
}
