<?php

declare(strict_types=1);

use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    public function testSwitchSetsLocaleInSessionWhenValid()
    {
        config(['app.available_locales' => ['en', 'uk']]);

        $response = $this->from('/previous')
            ->get(route('locale.switch', ['localeForAdmin' => 'en']));

        $response->assertRedirect('/previous');
        $response->assertSessionHas('locale', 'en');
    }

    public function testSwitchDoesNotSetLocaleWhenInvalid()
    {
        config(['app.available_locales' => ['en', 'uk']]);

        $response = $this->from('/previous')
            ->get(route('locale.switch', ['localeForAdmin' => 'de']));

        $response->assertRedirect('/previous');
        $response->assertSessionMissing('locale');
    }

    public function testSwitchRedirectsBack()
    {
        config(['app.available_locales' => ['en', 'uk']]);

        $response = $this->from('/some-page')
            ->get(route('locale.switch', ['localeForAdmin' => 'en']));

        $response->assertRedirect('/some-page');
    }

    public function testSwitchOverwritesExistingLocale()
    {
        config(['app.available_locales' => ['en', 'uk']]);

        $this->withSession(['locale' => 'uk']);

        $response = $this->from('/previous')
            ->get(route('locale.switch', ['localeForAdmin' => 'en']));

        $response->assertRedirect('/previous');
        $response->assertSessionHas('locale', 'en');
    }

    public function testSwitchDoesNotOverrideSessionWhenInvalidLocale()
    {
        config(['app.available_locales' => ['en', 'uk']]);

        $this->withSession(['locale' => 'uk']);

        $response = $this->from('/previous')
            ->get(route('locale.switch', ['localeForAdmin' => 'de']));

        $response->assertRedirect('/previous');
        $response->assertSessionHas('locale', 'uk');
    }
}
