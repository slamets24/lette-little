<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InertiaSsrTest extends TestCase
{
    use RefreshDatabase;

    private const RENDER_URL = 'http://127.0.0.1:13714/render';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'inertia.ssr.url' => 'http://127.0.0.1:13714',
            'inertia.ssr.ensure_bundle_exists' => false,
        ]);
    }

    public function test_ssr_is_disabled_by_default_in_the_test_suite(): void
    {
        Http::fake();

        $this->get(route('faq'))->assertOk();

        $this->assertFalse(config('inertia.ssr.enabled'));
        Http::assertNothingSent();
    }

    public function test_server_rendered_markup_is_served_when_ssr_is_enabled(): void
    {
        config(['inertia.ssr.enabled' => true]);

        Http::fake([
            self::RENDER_URL => Http::response([
                'head' => ['<title data-inertia="">FAQ - Lette Little</title>'],
                'body' => '<div data-server-rendered="true" id="app"><h1>How do I choose a size?</h1></div>',
            ]),
        ]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('<div data-server-rendered="true" id="app"><h1>How do I choose a size?</h1></div>', false)
            ->assertSee('<title data-inertia="">FAQ - Lette Little</title>', false);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::RENDER_URL
            && $request['component'] === 'Faq');
    }

    public function test_pages_fall_back_to_client_rendering_when_the_ssr_server_fails(): void
    {
        config(['inertia.ssr.enabled' => true]);

        Http::fake([
            self::RENDER_URL => Http::response(['error' => 'window is not defined', 'type' => 'browser-api'], 500),
        ]);

        $this->get(route('faq'))
            ->assertOk()
            ->assertDontSee('data-server-rendered', false)
            ->assertSee('<div id="app"></div>', false);
    }
}
