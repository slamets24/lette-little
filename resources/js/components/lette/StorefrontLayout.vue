<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronDown,
    Cloud,
    Mail,
    Menu,
    Search,
    ShoppingBag,
    UserRound,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import { useCart } from '@/composables/useCart';

defineProps<{
    active: 'shop' | 'collection' | 'about' | 'journal' | 'home' | 'cart';
}>();

const menuOpen = ref(false);
const { cartCount } = useCart();

const navItems = [
    { label: 'SHOP', href: '/shop', key: 'shop' },
    { label: 'COLLECTION', href: '/collection', key: 'collection' },
    { label: 'ABOUT', href: '/about', key: 'about' },
    { label: 'JOURNAL', href: '/journal', key: 'journal' },
] as const;
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="lette-site">
        <div class="shipping-bar">FREE SHIPPING ON ORDERS OVER IDR 500.000</div>

        <header class="site-header">
            <nav class="nav-shell" aria-label="Primary navigation">
                <button
                    class="mobile-menu-button"
                    type="button"
                    aria-label="Open menu"
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
                    href="/"
                    class="brand-lockup"
                    :class="{ active: active === 'home' }"
                    aria-label="Lette Little home"
                >
                    <Cloud class="brand-cloud" />
                    <span>Lette Little</span>
                    <small>MADE LIGHT. MADE GENTLE.</small>
                </Link>

                <div class="nav-actions" aria-label="Shop tools">
                    <button type="button" class="currency-button">
                        IDR <ChevronDown />
                    </button>
                    <Link href="/shop" aria-label="Search products"
                        ><Search
                    /></Link>
                    <Link href="/login" aria-label="Account"
                        ><UserRound
                    /></Link>
                    <Link href="/cart" class="cart-link" aria-label="Cart"
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
        >
            <div class="mobile-drawer-panel">
                <div class="mobile-drawer-top">
                    <Link
                        href="/"
                        class="mobile-brand"
                        @click="menuOpen = false"
                        ><Cloud /> Lette Little</Link
                    >
                    <button
                        type="button"
                        aria-label="Close menu"
                        @click="menuOpen = false"
                    >
                        <X />
                    </button>
                </div>
                <Link
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.href"
                    :class="{ active: active === item.key }"
                    @click="menuOpen = false"
                >
                    {{ item.label }}
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
                            ><img src="/images/lette/instagram.webp" alt=""
                        /></a>
                        <a href="https://facebook.com" aria-label="Facebook"
                            ><img src="/images/lette/facebook.webp" alt=""
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
                    <Link href="/shop">All Products</Link>
                    <Link href="/collection">Dresses</Link>
                    <Link href="/collection">Rompers</Link>
                    <Link href="/collection">Sets</Link>
                    <Link href="/collection">Sleepwear</Link>
                </section>

                <section class="footer-group">
                    <h3>INFO</h3>
                    <a href="#">Size Guide</a>
                    <a href="#">Care Guide</a>
                    <a href="#">Shipping & Returns</a>
                    <a href="#">FAQ</a>
                </section>

                <section class="footer-group">
                    <h3>ABOUT</h3>
                    <Link href="/about">Our Story</Link>
                    <Link href="/journal">Journal</Link>
                    <a href="mailto:hello@lettelittle.id">Contact Us</a>
                </section>

                <section class="newsletter-box">
                    <h3>NEWSLETTER</h3>
                    <p>
                        Be the first to know about new collections and special
                        offers.
                    </p>
                    <form @submit.prevent>
                        <input
                            type="email"
                            placeholder="Your email"
                            aria-label="Your email"
                        />
                        <button type="submit" aria-label="Subscribe">+</button>
                    </form>
                </section>
            </div>
            <div class="legal-row">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.lette-site {
    min-height: 100vh;
    background: #fcf9f3;
    color: #4e453f;
    font-family: 'Be Vietnam Pro', ui-sans-serif, system-ui, sans-serif;
}

.lette-site :deep(h1),
.lette-site :deep(h2),
.lette-site :deep(h3),
.brand-lockup span {
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
}

.shipping-bar {
    display: grid;
    min-height: 25px;
    place-items: center;
    background: #e8dfd4;
    color: #6c5b4e;
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
.nav-actions a,
.currency-button {
    color: #4e453f;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.14em;
    transition:
        color 180ms ease,
        opacity 180ms ease;
}

.nav-left a.active,
.nav-left a:hover,
.nav-actions a:hover,
.currency-button:hover {
    color: #1c1c18;
}

.brand-lockup {
    display: grid;
    justify-items: center;
    color: #6c5b4e;
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
    color: #6c5b4e;
    font-size: 26px;
    font-weight: 400;
    letter-spacing: 0.08em;
    white-space: nowrap;
}

.brand-lockup small {
    margin-top: 6px;
    color: #80756e;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.22em;
}

.nav-actions {
    justify-content: flex-end;
    gap: 18px;
}

.nav-actions svg,
.currency-button svg,
.mobile-menu-button svg,
.mobile-drawer svg,
.footer-brand svg,
.social-row svg {
    width: 18px;
    height: 18px;
    stroke-width: 1.45;
}

.currency-button,
.cart-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.mobile-menu-button {
    display: none;
    color: #6c5b4e;
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
    background: #fcf9f3;
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
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 18px;
}

.mobile-drawer-panel > a:not(.mobile-brand) {
    border-bottom: 1px solid #d1c4bc;
    padding: 16px 0;
    color: #4e453f;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.12em;
}

.mobile-drawer-panel > a.active {
    color: #1c1c18;
}

.site-footer {
    border-top: 1px solid #e5ded6;
    background: #f6f3ed;
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
    color: #6c5b4e;
    font-size: 24px;
    font-weight: 400;
    letter-spacing: 0.08em;
}

.footer-brand p,
.newsletter-box p,
.footer-brand small,
.legal-row a {
    color: #80756e;
    font-size: 12px;
    line-height: 1.75;
}

.social-row {
    display: flex;
    gap: 12px;
    margin: 18px 0;
}

.social-row a,
.footer-brand > svg {
    color: #6c5b4e;
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
    color: #6c5b4e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.16em;
}

.footer-group a {
    width: fit-content;
    color: #4e453f;
    font-size: 12px;
    line-height: 1.5;
}

.newsletter-box form {
    display: grid;
    grid-template-columns: 1fr 38px;
    margin-top: 8px;
    border: 1px solid #d1c4bc;
    background: #fcf9f3;
}

.newsletter-box input {
    min-width: 0;
    border: 0;
    background: transparent;
    padding: 13px 14px;
    color: #4e453f;
    font-size: 12px;
}

.newsletter-box button {
    color: #6c5b4e;
    font-size: 18px;
}

.legal-row {
    display: flex;
    justify-content: flex-end;
    gap: 32px;
    max-width: 1220px;
    margin: 0 auto;
    border-top: 1px solid #e5ded6;
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
    .nav-actions a,
    .currency-button {
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

    .nav-actions svg,
    .currency-button svg {
        width: 16px;
        height: 16px;
    }
}

@media (max-width: 680px) {
    .nav-shell {
        min-height: 72px;
        grid-template-columns: 40px 1fr 40px;
        padding: 0 18px;
    }

    .nav-left,
    .currency-button,
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
        top: -10px;
        right: -10px;
        display: grid;
        min-width: 18px;
        height: 18px;
        place-items: center;
        border-radius: 999px;
        background: #6c5b4e;
        color: #fffdf8;
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
