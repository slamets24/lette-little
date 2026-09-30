<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTest extends TestCase
{
    public function test_robots_txt_is_served_as_plain_text(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringStartsWith('text/plain', $response->headers->get('Content-Type'));
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_robots_txt_blocks_private_areas(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertSee("User-agent: *\n", false);

        foreach (['/cart', '/checkout', '/orders', '/admin', '/dashboard', '/settings', '/login', '/register'] as $path) {
            $response->assertSee("Disallow: {$path}\n", false);
        }
    }

    public function test_robots_txt_welcomes_ai_crawlers(): void
    {
        $response = $this->get('/robots.txt');

        foreach (['GPTBot', 'PerplexityBot', 'ClaudeBot', 'Google-Extended'] as $crawler) {
            $response->assertSee("User-agent: {$crawler}\nAllow: /\n", false);
        }
    }

    public function test_robots_txt_points_to_the_absolute_sitemap_url(): void
    {
        $this->get('/robots.txt')
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }
}
