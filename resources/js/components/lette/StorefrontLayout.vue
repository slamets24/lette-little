<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Cloud,
    Mail,
    Menu,
    Search,
    ShoppingBag,
    UserRound,
    X,
} from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import {
    about,
    careGuide,
    cart,
    collection,
    faq,
    home,
    journal,
    login,
    privacy,
    shippingReturns,
    shop,
    sizeGuide,
    terms,
} from '@/routes';
import { index as ordersIndex } from '@/routes/orders';
import { category as shopCategory } from '@/routes/shop';

defineProps<{
    active:
        | 'shop'
        | 'collection'
        | 'about'
        | 'journal'
        | 'home'
        | 'cart'
        | 'orders'
        | 'info';
}>();

const page = usePage();
const menuOpen = ref(false);
const cartCount = computed(() => page.props.cart?.count ?? 0);
const accountHref = computed(() =>
    page.props.auth?.user ? ordersIndex.url() : login.url(),
);

const navItems = [
    { label: 'SHOP', href: shop.url(), key: 'shop' },
    { label: 'COLLECTION', href: collection.url(), key: 'collection' },
    { label: 'ABOUT', href: about.url(), key: 'about' },
    { label: 'JOURNAL', href: journal.url(), key: 'journal' },
] as const;

const menuButton = ref<HTMLButtonElement | null>(null);
const menuPanel = ref<HTMLElement | null>(null);

function focusableItems(): HTMLElement[] {
    if (!menuPanel.value) {
        return [];
    }

    return Array.from(
        menuPanel.value.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled])',
        ),
    );
}

function closeMenu() {
    menuOpen.value = false;
}

/**
 * Stop the page behind the drawer from scrolling while it is open.
 */
function lockPageScroll(locked: boolean) {
    const overflow = locked ? 'hidden' : '';

    document.documentElement.style.overflow = overflow;
    document.body.style.overflow = overflow;
}

/**
 * Keep keyboard focus inside the drawer and close it on Escape.
 */
function onDrawerKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        closeMenu();

        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const items = focusableItems();

    if (items.length === 0) {
        return;
    }

    const first = items[0];
    const last = items[items.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

watch(menuOpen, async (open) => {
    lockPageScroll(open);

    if (open) {
        await nextTick();
        focusableItems()[0]?.focus();

        return;
    }

    menuButton.value?.focus();
});

onBeforeUnmount(() => {
    lockPageScroll(false);
});
</script>

<template>
    <div class="lette-site">
        <div class="shipping-bar">
            SHIPS ACROSS INDONESIA · COURIER QUOTE AFTER CHECKOUT
        </div>

        <header class="site-header">
            <nav class="nav-shell" aria-label="Primary navigation">
                <button
                    ref="menuButton"
                    class="mobile-menu-button"
                    type="button"
                    aria-label="Open menu"
                    aria-haspopup="dialog"
                    :aria-expanded="menuOpen"
                    @click="menuOpen = true"
                >
                    <Menu />
                </button>

                <div class="nav-left">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="item.href"
                        :class="{ active: active === item.key }"
                    >
                        {{ item.label }}
                    </Link>
                </div>

                <Link
                    :href="home.url()"
                    class="brand-lockup"
                    :class="{ active: active === 'home' }"
                    aria-label="Lette Little home"
                >
                    <Cloud class="brand-cloud" />
                    <span>Lette Little</span>
                    <small>MADE LIGHT. MADE GENTLE.</small>
                </Link>

                <div class="nav-actions" aria-label="Shop tools">
                    <Link
                        :href="`${shop.url()}#shop-search`"
                        aria-label="Search products"
                        ><Search
                    /></Link>
                    <Link :href="accountHref" aria-label="Account"
                        ><UserRound
                    /></Link>
                    <Link :href="cart.url()" class="cart-link" aria-label="Cart"
                        ><ShoppingBag /><span>({{ cartCount }})</span></Link
                    >
                </div>
            </nav>
        </header>

        <div
            v-if="menuOpen"
            class="mobile-drawer"
            role="dialog"
            aria-modal="true"
            aria-label="Mobile navigation"
            @click.self="closeMenu"
            @keydown="onDrawerKeydown"
        >
            <div ref="menuPanel" class="mobile-drawer-panel">
                <div class="mobile-drawer-top">
                    <Link
                        :href="home.url()"
                        class="mobile-brand"
                        @click="closeMenu"
                        ><Cloud /> Lette Little</Link
                    >
                    <button
                        class="mobile-close-button"
                        type="button"
                        aria-label="Close menu"
                        @click="closeMenu"
                    >
                        <X />
                    </button>
                </div>
                <Link
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.href"
                    :class="{ active: active === item.key }"
                    @click="closeMenu"
                >
                    {{ item.label }}
                </Link>
                <Link
                    :href="accountHref"
                    class="mobile-account-link"
                    :class="{ active: active === 'orders' }"
                    @click="closeMenu"
                >
                    Account
                </Link>
            </div>
        </div>

        <main>
            <slot />
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <section class="footer-brand">
                    <Cloud />
                    <h2>Lette Little</h2>
                    <p>
                        Soft essentials for little ones. Made light. Made
                        gentle.
                    </p>
                    <div class="social-row">
                        <a
                            href="https://instagram.com/lettelittle.id"
                            aria-label="Instagram"
                            ><img
                                src="/images/lette/instagram.webp"
                                alt=""
                                width="96"
                                height="96"
                        /></a>
                        <a href="https://facebook.com" aria-label="Facebook"
                            ><img
                                src="/images/lette/facebook.webp"
                                alt=""
                                width="96"
                                height="96"
                        /></a>
                        <a href="mailto:hello@lettelittle.id" aria-label="Email"
                            ><Mail
                        /></a>
                    </div>
                    <small
                        >&copy; 2026 Lette Little. All rights reserved.</small
                    >
                </section>

                <section class="footer-group">
                    <h3>SHOP</h3>
                    <Link :href="shop.url()">All Products</Link>
                    <Link :href="shopCategory.url('dresses')">Dresses</Link>
                    <Link :href="shopCategory.url('rompers')">Rompers</Link>
                    <Link :href="shopCategory.url('sets')">Sets</Link>
                    <Link :href="shopCategory.url('sleepwear')">Sleepwear</Link>
                </section>

                <section class="footer-group">
                    <h3>INFO</h3>
                    <Link :href="sizeGuide.url()">Size Guide</Link>
                    <Link :href="careGuide.url()">Care Guide</Link>
                    <Link :href="shippingReturns.url()"
                        >Shipping & Returns</Link
                    >
                    <Link :href="faq.url()">FAQ</Link>
                </section>

                <section class="footer-group">
                    <h3>ABOUT</h3>
                    <Link :href="about.url()">Our Story</Link>
                    <Link :href="journal.url()">Journal</Link>
                    <a href="mailto:hello@lettelittle.id">Contact Us</a>
                </section>

                <section class="newsletter-box">
                    <h3>NEWSLETTER</h3>
                    <p>
                        Be the first to know about new collections and special
                        offers.
                    </p>
                    <a
                        href="mailto:hello@lettelittle.id?subject=Lette%20Little%20updates"
                    >
                        Email us for updates
                    </a>
                </section>
            </div>
            <div class="legal-row">
                <Link :href="privacy.url()">Privacy Policy</Link>
                <Link :href="terms.url()">Terms & Conditions</Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.lette-site {
    min-height: 100vh;
    background: var(--lette-canvas);
    color: var(--lette-text);
    font-family: var(--lette-body-font);
}

.lette-site :deep(h1),
.lette-site :deep(h2),
.lette-site :deep(h3),
.brand-lockup,
.mobile-brand {
    font-family: var(--lette-heading-font);
}

.shipping-bar {
    display: grid;
    min-height: 25px;
    place-items: center;
    background: #e8dfd4;
    color: var(--lette-brand);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.14em;
}

.site-header {
    position: sticky;
    top: 0;
    z-index: 30;
    border-bottom: 1px solid rgba(209, 196, 188, 0.54);
    background: rgba(252, 249, 243, 0.9);
    backdrop-filter: blur(16px);
}

.nav-shell {
    position: relative;
    display: grid;
    min-height: 80px;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 24px;
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 42px;
}

.nav-left,
.nav-actions {
    display: flex;
    align-items: center;
    gap: 34px;
}

.nav-left a,
.nav-actions a {
    color: var(--lette-text);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.14em;
    transition:
        color 180ms ease,
        opacity 180ms ease;
}

.nav-left a.active,
.nav-left a:hover,
.nav-actions a:hover {
    color: var(--lette-ink);
}

.brand-lockup {
    display: grid;
    min-height: 44px;
    align-content: center;
    justify-items: center;
    color: var(--lette-brand);
    text-align: center;
    line-height: 1;
}

.brand-cloud {
    width: 35px;
    height: 24px;
    margin-bottom: 6px;
    stroke-width: 1.35;
}

.brand-lockup span {
    color: var(--lette-brand);
    font-size: 26px;
    font-weight: 400;
    letter-spacing: 0.08em;
    white-space: nowrap;
}

.brand-lockup small {
    margin-top: 6px;
    color: var(--lette-muted);
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.22em;
}

.nav-actions {
    justify-content: flex-end;
    gap: 0;
}

/* Header icons keep a comfortable touch area without growing visually. */
.nav-actions a,
.mobile-menu-button,
.mobile-close-button {
    display: inline-flex;
    min-width: 44px;
    min-height: 44px;
    align-items: center;
    justify-content: center;
}

.nav-actions svg,
.mobile-menu-button svg,
.mobile-drawer svg,
.footer-brand svg,
.social-row svg {
    width: 18px;
    height: 18px;
    stroke-width: 1.45;
}

.cart-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.mobile-menu-button {
    display: none;
    color: var(--lette-brand);
}

.mobile-drawer {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(49, 49, 45, 0.24);
}

.mobile-drawer-panel {
    display: grid;
    gap: 6px;
    width: min(88vw, 360px);
    height: 100%;
    align-content: start;
    background: var(--lette-canvas);
    padding: 22px;
    box-shadow: 18px 0 50px rgba(49, 49, 45, 0.14);
}

.mobile-drawer-top,
.mobile-brand {
    display: flex;
    align-items: center;
}

.mobile-drawer-top {
    justify-content: space-between;
    margin-bottom: 20px;
}

.mobile-brand {
    gap: 8px;
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: 18px;
}

.mobile-drawer-panel > a:not(.mobile-brand) {
    border-bottom: 1px solid var(--lette-border);
    padding: 16px 0;
    color: var(--lette-text);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.12em;
}

.mobile-drawer-panel > a.active {
    color: var(--lette-ink);
}

.site-footer {
    border-top: 1px solid var(--lette-line);
    background: var(--lette-sand);
}

.footer-inner {
    display: grid;
    grid-template-columns: 1.6fr repeat(3, 1fr) 1.6fr;
    gap: 46px;
    max-width: 1220px;
    margin: 0 auto;
    padding: 52px 42px 30px;
}

.footer-brand h2 {
    margin-top: 8px;
    color: var(--lette-brand);
    font-size: 24px;
    font-weight: 400;
    letter-spacing: 0.08em;
}

.footer-brand p,
.newsletter-box p,
.footer-brand small,
.legal-row a {
    color: var(--lette-muted);
    font-size: 12px;
    line-height: 1.75;
}

.social-row {
    display: flex;
    gap: 4px;
    margin: 12px 0;
    margin-left: -10px;
}

.social-row a,
.footer-brand > svg {
    color: var(--lette-brand);
}

.social-row a {
    display: inline-flex;
    width: 40px;
    height: 40px;
    align-items: center;
    justify-content: center;
}

.social-row img {
    width: 18px;
    height: 18px;
    object-fit: contain;
}

.footer-group,
.newsletter-box {
    display: grid;
    align-content: start;
    gap: 10px;
}

.footer-group h3,
.newsletter-box h3 {
    color: var(--lette-brand);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.16em;
}

.footer-group a,
.legal-row a {
    display: inline-flex;
    width: fit-content;
    min-height: 24px;
    align-items: center;
    color: var(--lette-text);
    font-size: 12px;
    line-height: 1.5;
}

.newsletter-box a {
    display: inline-flex;
    width: fit-content;
    min-height: 24px;
    align-items: center;
    margin-top: 8px;
    color: var(--lette-text);
    font-size: 12px;
    line-height: 1.5;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.legal-row {
    display: flex;
    justify-content: flex-end;
    gap: 32px;
    max-width: 1220px;
    margin: 0 auto;
    border-top: 1px solid var(--lette-line);
    padding: 18px 42px 26px;
}

@media (min-width: 681px) and (max-width: 860px) {
    .nav-shell {
        min-height: 72px;
        gap: 14px;
        padding: 0 22px;
    }

    .nav-left {
        gap: 22px;
    }

    .nav-actions {
        gap: 12px;
    }

    .nav-left a,
    .nav-actions a {
        font-size: 10px;
        letter-spacing: 0.12em;
    }

    .brand-cloud {
        width: 29px;
        height: 20px;
        margin-bottom: 4px;
    }

    .brand-lockup span {
        font-size: 22px;
        letter-spacing: 0.07em;
    }

    .brand-lockup small {
        max-width: 118px;
        margin-top: 4px;
        font-size: 7px;
        letter-spacing: 0.18em;
        line-height: 1.15;
    }

    .nav-actions svg {
        width: 16px;
        height: 16px;
    }
}

@media (max-width: 680px) {
    .nav-shell {
        min-height: 72px;
        grid-template-columns: 44px 1fr 44px;
        padding: 0 12px;
    }

    .nav-left,
    .nav-actions a:not(.cart-link) {
        display: none;
    }

    .mobile-menu-button {
        display: inline-flex;
    }

    .nav-actions {
        gap: 0;
    }

    .cart-link {
        position: relative;
    }

    .cart-link span {
        position: absolute;
        top: 3px;
        right: 1px;
        display: grid;
        min-width: 18px;
        height: 18px;
        place-items: center;
        border-radius: 999px;
        background: var(--lette-brand);
        color: var(--lette-surface);
        font-size: 9px;
        line-height: 1;
        letter-spacing: 0;
    }

    .brand-lockup span {
        font-size: 22px;
    }

    .footer-inner {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        padding: 42px 22px 28px;
    }

    /* Roomier touch targets on phones, with tighter gaps so the footer stays short. */
    .footer-group,
    .newsletter-box {
        gap: 2px;
    }

    .footer-group a,
    .newsletter-box a,
    .legal-row a {
        min-height: 40px;
    }

    .footer-brand,
    .newsletter-box {
        grid-column: 1 / -1;
    }

    .legal-row {
        justify-content: flex-start;
        padding-inline: 22px;
    }
}

@media (max-width: 560px) {
    .shipping-bar {
        font-size: 8px;
    }

    .brand-cloud,
    .brand-lockup small {
        display: none;
    }

    .footer-inner {
        grid-template-columns: 1fr;
    }

    .footer-brand,
    .newsletter-box {
        grid-column: auto;
    }
}
</style>
