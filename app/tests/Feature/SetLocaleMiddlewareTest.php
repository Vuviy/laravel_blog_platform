<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    public function testSetsLocaleFromUrlWhenValid(): void
    {
        config()->set('app.available_locales', ['en', 'uk']);

        session()->forget('locale');

        Route::middleware(SetLocale::class)
            ->get('/{locale}/home', fn () => 'ok')
            ->name('test.locale');

        $response = $this->get('/uk/home');

        $response->assertOk();

        $this->assertEquals('uk', app()->getLocale());
        $this->assertEquals('uk', session('locale'));
    }


    public function testUsesSessionLocaleWhenUrlIsInvalid(): void
    {
        config()->set('app.available_locales', ['en', 'uk']);
        config()->set('app.locale', 'en');

        session(['locale' => 'uk']);

        Route::middleware(SetLocale::class)
            ->get('/{locale}/home', fn () => 'ok')->name('test.locale');

        $this->get('/fr/home')
            ->assertRedirect();

        $this->assertEquals('uk', app()->getLocale());
    }

    public function testUsesSessionOrDefaultWhenLocaleNotInUrl(): void
    {
        config()->set('app.available_locales', ['en', 'uk']);
        config()->set('app.locale', 'en');

        session(['locale' => 'uk']);

        Route::middleware(SetLocale::class)
            ->get('/home', fn () => 'ok')->name('testlocale');

        $this->get('/home')
            ->assertRedirect();

        $this->assertEquals('uk', app()->getLocale());
    }
}
