<?php

namespace App\Http\Middleware;

use App\Services\Cart\CartManager;
use App\Services\Seo\PageMeta;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'meta' => $this->defaultMeta($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'cart' => fn () => [
                'count' => app(CartManager::class)->count($request),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Route names of private pages mapped to their page titles.
     *
     * These pages are never indexed. Route names ending in `*` match any
     * route below that prefix.
     *
     * @var array<string, string|null>
     */
    private const PRIVATE_PAGES = [
        'login' => 'Log in',
        'register' => 'Register',
        'password.request' => 'Forgot password',
        'password.reset' => 'Reset password',
        'password.confirm' => 'Confirm password',
        'verification.notice' => 'Verify email',
        'two-factor.login' => 'Two-factor authentication',
        'dashboard' => 'Dashboard',
        'admin.*' => 'Admin',
        'profile.*' => 'Settings',
        'security.*' => 'Settings',
        'appearance.*' => 'Settings',
    ];

    /**
     * Build the fallback metadata for pages that do not pass their own.
     *
     * Authentication, account, and admin pages are marked noindex here so
     * every Fortify view and back-office page is covered in one place.
     *
     * @return array<string, mixed>
     */
    private function defaultMeta(Request $request): array
    {
        foreach (self::PRIVATE_PAGES as $routeName => $title) {
            if ($request->routeIs($routeName)) {
                return PageMeta::make(title: $title, noindex: true);
            }
        }

        if ($request->is('admin', 'admin/*', 'settings', 'settings/*')) {
            return PageMeta::make(noindex: true);
        }

        return PageMeta::make();
    }
}
