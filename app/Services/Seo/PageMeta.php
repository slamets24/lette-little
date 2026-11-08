<?php

namespace App\Services\Seo;

class PageMeta
{
    /**
     * The fallback social sharing image for every page.
     */
    public const DEFAULT_IMAGE = '/images/lette/stitch-hero.jpg';

    /**
     * The description used when a page does not provide its own.
     */
    public const DEFAULT_DESCRIPTION = 'Lette Little makes soft, breathable essentials for little ones: dresses, rompers, sets, and sleepwear. Made light. Made gentle. Ships across Indonesia.';

    /**
     * Build the search engine and social metadata for a page.
     *
     * When no canonical URL is given the page falls back to the current URL
     * without its query string.
     *
     * @param  array<string, mixed>|null  $jsonLd
     * @return array{title: string, description: string, image: string, robots: string, canonical: string|null, json_ld: array<string, mixed>|null}
     */
    public static function make(
        ?string $title = null,
        ?string $description = null,
        ?string $image = null,
        bool $noindex = false,
        ?array $jsonLd = null,
        ?string $canonical = null,
    ): array {
        $siteName = config('app.name');

        return [
            'title' => $title ? "{$title} - {$siteName}" : $siteName,
            'description' => self::trimDescription($description ?? self::DEFAULT_DESCRIPTION),
            'image' => url($image ?? self::DEFAULT_IMAGE),
            'robots' => $noindex ? 'noindex, nofollow' : 'index, follow',
            'canonical' => $canonical,
            'json_ld' => $jsonLd,
        ];
    }

    /**
     * Keep descriptions within the length search engines display.
     */
    private static function trimDescription(string $description): string
    {
        return str(strip_tags($description))->squish()->limit(160)->value();
    }
}
