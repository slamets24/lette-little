<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2 } from '@lucide/vue';
import { show as checkout } from '@/actions/App/Http/Controllers/CheckoutController';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import type { CartItem } from '@/composables/useCart';
import { useCart } from '@/composables/useCart';
import { shop } from '@/routes';

type Cart = {
    items: CartItem[];
    count: number;
    subtotal: number;
};

defineProps<{
    cart: Cart;
    cartAdjustments?: string[];
}>();

const { updateQuantity, removeItem, formatMoney } = useCart();

</script>

<template>
    <StorefrontLayout active="cart">
        <Head title="Cart" />

        <section class="cart-shell">
            <div class="cart-heading">
                <p>CART</p>
                <h1>Your gentle picks.</h1>
                <span>
                    Review sizes, colors, and quantity before placing your order
                    securely.
                </span>
            </div>

            <div v-if="cart.items.length" class="cart-layout">
                <section class="cart-items" aria-label="Cart items">
                    <article
                        v-for="item in cart.items"
                        :key="item.id"
                        class="cart-item"
                    >
                        <Link
                            :href="`/shop/${item.product.slug}`"
                            class="item-image"
                            :aria-label="`View ${item.product.name}`"
                        >
                            <img
                                :src="item.product.image"
                                :alt="item.product.name"
                            />
                        </Link>

                        <div class="item-copy">
                            <Link :href="`/shop/${item.product.slug}`">
                                {{ item.product.name }}
                            </Link>
                            <span>
                                {{ item.variant.color }} / {{ item.variant.size }}
                            </span>
                            <small>SKU {{ item.variant.sku }}</small>
                            <strong>{{ formatMoney(item.variant.price) }}</strong>
                        </div>

                        <div class="quantity-control">
                            <button
                                type="button"
                                aria-label="Decrease quantity"
                                @click="updateQuantity(item.id, item.quantity - 1)"
                            >
                                <Minus />
                            </button>
                            <span>{{ item.quantity }}</span>
                            <button
                                type="button"
                                aria-label="Increase quantity"
                                @click="updateQuantity(item.id, item.quantity + 1)"
                            >
                                <Plus />
                            </button>
                        </div>

                        <strong class="line-total">
                            {{ formatMoney(item.line_total) }}
                        </strong>

                        <button
                            type="button"
                            class="remove-button"
                            :aria-label="`Remove ${item.product.name}`"
                            @click="removeItem(item.id)"
                        >
                            <Trash2 />
                        </button>
                    </article>
                </section>

                <aside class="summary-card">
                    <p>Order Summary</p>
                    <div>
                        <span>Subtotal</span>
                        <strong>{{ formatMoney(cart.subtotal) }}</strong>
                    </div>
                    <small>
                        Shipping and payment will be confirmed in the secure
                        checkout flow.
                    </small>
                    <Link :href="checkout.url()" class="checkout-link">
                        Checkout
                    </Link>
                    <Link :href="shop.url()">Continue Shopping</Link>
                </aside>
            </div>

            <section v-else class="empty-cart">
                <ShoppingBag />
                <h2>Your cart is empty.</h2>
                <p>
                    Add your favorite soft essentials first, then continue to
                    checkout.
                </p>
                <Link :href="shop.url()">Shop Now</Link>
            </section>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.cart-shell {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
    padding: 70px 0 86px;
}

.cart-heading,
.empty-cart {
    display: grid;
    justify-items: center;
    text-align: center;
}

.cart-heading {
    gap: 14px;
    margin-bottom: 36px;
}

.cart-heading p,
.summary-card p {
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.cart-heading h1,
.empty-cart h2 {
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-weight: 400;
}

.cart-heading h1 {
    font-size: clamp(38px, 5vw, 62px);
    line-height: 1.12;
}

.cart-heading span,
.empty-cart p,
.summary-card small,
.item-copy span,
.item-copy small {
    color: var(--lette-muted);
    font-size: 13px;
    line-height: 1.75;
}

.cart-heading span {
    max-width: 560px;
}

.cart-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 28px;
    align-items: start;
}

.cart-items {
    display: grid;
    gap: 14px;
}

.cart-item {
    display: grid;
    grid-template-columns: 112px minmax(0, 1fr) auto auto 42px;
    gap: 18px;
    align-items: center;
    border: 1px solid var(--lette-line);
    background: var(--lette-surface);
    padding: 14px;
}

.item-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--lette-placeholder);
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-copy {
    display: grid;
    gap: 4px;
}

.item-copy a {
    width: fit-content;
    color: var(--lette-text);
    font-family: var(--lette-heading-font);
    font-size: 18px;
}

.item-copy strong,
.line-total,
.summary-card strong {
    color: var(--lette-ink);
    font-weight: 600;
}

.item-copy strong {
    font-size: 13px;
}

.quantity-control {
    display: grid;
    grid-template-columns: 36px 42px 36px;
    min-height: 38px;
    border: 1px solid var(--lette-border);
    background: var(--lette-canvas);
}

.quantity-control button,
.quantity-control span,
.remove-button {
    display: grid;
    place-items: center;
    color: var(--lette-text);
}

.quantity-control span {
    border-inline: 1px solid var(--lette-line);
    font-size: 13px;
    font-weight: 700;
}

.quantity-control svg,
.remove-button svg,
.empty-cart svg {
    width: 17px;
    height: 17px;
    stroke-width: 1.5;
}

.line-total {
    min-width: 104px;
    text-align: right;
    font-size: 13px;
}

.remove-button {
    width: 42px;
    height: 42px;
    border: 1px solid var(--lette-line);
    background: var(--lette-canvas);
}

.summary-card {
    position: sticky;
    top: 128px;
    display: grid;
    gap: 18px;
    border: 1px solid var(--lette-border);
    background: var(--lette-sand);
    padding: 24px;
}

.summary-card div {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    border-top: 1px solid var(--lette-line);
    border-bottom: 1px solid var(--lette-line);
    padding: 16px 0;
    color: var(--lette-text);
    font-size: 14px;
}

.summary-card button,
.summary-card a,
.empty-cart a {
    display: inline-flex;
    min-height: var(--lette-cta-height);
    align-items: center;
    justify-content: center;
    border: 1px solid var(--lette-brand);
    padding: 0 var(--lette-cta-padding);
    font-size: var(--lette-cta-size);
    font-weight: 700;
    letter-spacing: var(--lette-cta-tracking);
    text-transform: uppercase;
}

.summary-card button,
.summary-card a.checkout-link,
.empty-cart a {
    background: var(--lette-brand);
    color: var(--lette-surface);
}

.summary-card a {
    background: var(--lette-surface);
    color: var(--lette-text);
}

.summary-card a.checkout-link {
    color: var(--lette-surface);
}

.empty-cart {
    gap: 14px;
    max-width: 440px;
    margin: 0 auto;
    border: 1px solid var(--lette-line);
    background: var(--lette-surface);
    padding: 54px 24px;
}

.empty-cart svg {
    width: 32px;
    height: 32px;
    color: var(--lette-brand);
}

.empty-cart h2 {
    font-size: 30px;
}

@media (max-width: 900px) {
    .cart-shell {
        width: min(100% - 32px, 1220px);
        padding-top: 52px;
    }

    .cart-layout {
        grid-template-columns: 1fr;
    }

    .summary-card {
        position: static;
    }
}

@media (max-width: 640px) {
    .cart-item {
        grid-template-columns: 86px minmax(0, 1fr) 42px;
        gap: 12px;
    }

    .quantity-control {
        grid-column: 2 / 3;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: min(100%, 156px);
    }

    .line-total {
        grid-column: 2 / 3;
        min-width: 0;
        text-align: left;
    }

    .remove-button {
        grid-column: 3 / 4;
        grid-row: 1 / 2;
    }
}
</style>
