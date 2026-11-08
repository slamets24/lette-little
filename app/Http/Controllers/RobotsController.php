<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Paths that crawlers should not visit.
     *
     * @var list<string>
     */
    private const DISALLOWED_PATHS = [
        '/cart',
        '/checkout',
        '/orders',
        '/admin',
        '/dashboard',
        '/settings',
        '/login',
        '/register',
    ];

    /**
     * AI crawlers that are explicitly welcome to read the storefront.
     *
     * @var list<string>
     */
    private const ALLOWED_AI_CRAWLERS = [
        'GPTBot',
        'PerplexityBot',
        'ClaudeBot',
        'Google-Extended',
    ];

    /**
     * Render robots.txt with an absolute sitemap URL.
     */
    public function __invoke(): Response
    {
        $lines = ['User-agent: *'];

        foreach (self::DISALLOWED_PATHS as $path) {
            $lines[] = "Disallow: {$path}";
        }

        foreach (self::ALLOWED_AI_CRAWLERS as $crawler) {
            $lines[] = '';
            $lines[] = "User-agent: {$crawler}";
            $lines[] = 'Allow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
