import { createInertiaApp } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/**
 * Admin, auth and settings layouts are split into their own chunks so that
 * storefront pages (which bring their own StorefrontLayout) never download
 * them. Inertia's `layout` resolver is synchronous, so the layouts are wrapped
 * with `defineAsyncComponent`: the wrapper is a stable component (persistent
 * layouts keep working), Vue's SSR renderer awaits it, and client hydration
 * waits for the chunk before hydrating the subtree.
 */
const AppLayout = defineAsyncComponent(() => import('@/layouts/AppLayout.vue'));
const AuthLayout = defineAsyncComponent(
    () => import('@/layouts/AuthLayout.vue'),
);
const SettingsLayout = defineAsyncComponent(
    () => import('@/layouts/settings/Layout.vue'),
);

/**
 * Pages that live inside the admin app shell. Everything else is a storefront
 * page and brings its own StorefrontLayout, so new storefront pages do not
 * have to be registered here.
 */
const appShellPages = ['Dashboard'];

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            case name.startsWith('admin/') || appShellPages.includes(name):
                return AppLayout;
            default:
                return null;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

/**
 * This file is also the SSR entry (the @inertiajs/vite plugin wraps the
 * createInertiaApp call above with the server bootstrap), so browser-only
 * setup must not run under Node.
 */
if (typeof window !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
