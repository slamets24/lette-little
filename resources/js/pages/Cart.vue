<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2 } from '@lucide/vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';

const {
    items,
    subtotal,
    updateQuantity,
    removeItem,
    formatMoney,
    whatsAppCheckoutUrl,
} = useCart();

function checkout() {
    if (!items.value.length) {
        return;
    }

    window.open(whatsAppCheckoutUrl(), '_blank', 'noopener,noreferrer');
}
</script>

<template>
    <StorefrontLayout active="cart">
        <Head title="Cart" />

        <section class="cart-shell">
            <div class="cart-heading">
                <p>CART</p>
                <h1>Your gentle picks.</h1>
                <span>
                    Review sizes, colors, and quantity before sending your order
                    to WhatsApp.
                </span>
            </div>

            <div v-if="items.length" class="cart-layout">
                <section class="cart-items" aria-label="Cart items">
                    <article
                        v-for="item in items"
                        :key="item.key"
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
                                @click="
                                    updateQuantity(item.key, item.quantity - 1)
                                "
                            >
                                <Minus />
                            </button>
                            <span>{{ item.quantity }}</span>
                            <button
                                type="button"
                                aria-label="Increase quantity"
                                @click="
                                    updateQuantity(item.key, item.quantity + 1)
                                "
                            >
                                <Plus />
                            </button>
                        </div>

                        <strong class="line-total">
                            {{ formatMoney(item.variant.price * item.quantity) }}
                        </strong>

                        <button
                            type="button"
                            class="remove-button"
                            :aria-label="`Remove ${item.product.name}`"
                            @click="removeItem(item.key)"
                        >
                            <Trash2 />
                        </button>
                    </article>
                </section>

                <aside class="summary-card">
                    <p>Order Summary</p>
                    <div>
                        <span>Subtotal</span>
                        <strong>{{ formatMoney(subtotal) }}</strong>
                    </div>
                    <small>
                        Shipping and final availability will be confirmed by our
                        team on WhatsApp.
                    </small>
                    <button type="button" @click="checkout">
                        Checkout via WhatsApp
                    </button>
                    <Link href="/shop">Continue Shopping</Link>
                </aside>
            </div>

            <section v-else class="empty-cart">
                <ShoppingBag />
                <h2>Your cart is empty.</h2>
                <p>
                    Add your favorite soft essentials first, then checkout by
                    WhatsApp.
                </p>
                <Link href="/shop">Shop Now</Link>
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
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.cart-heading h1,
.empty-cart h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
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
    color: #80756e;
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
    border: 1px solid #e5ded6;
    background: #fffdf8;
    padding: 14px;
}

.item-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f0eee8;
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
    color: #4e453f;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 18px;
}

.item-copy strong,
.line-total,
.summary-card strong {
    color: #1c1c18;
    font-weight: 600;
}

.item-copy strong {
    font-size: 13px;
}

.quantity-control {
    display: grid;
    grid-template-columns: 36px 42px 36px;
    min-height: 38px;
    border: 1px solid #d1c4bc;
    background: #fcf9f3;
}

.quantity-control button,
.quantity-control span,
.remove-button {
    display: grid;
    place-items: center;
    color: #4e453f;
}

.quantity-control span {
    border-inline: 1px solid #e5ded6;
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
    border: 1px solid #e5ded6;
    background: #fcf9f3;
}

.summary-card {
    position: sticky;
    top: 128px;
    display: grid;
    gap: 18px;
    border: 1px solid #d1c4bc;
    background: #f6f3ed;
    padding: 24px;
}

.summary-card div {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    border-top: 1px solid #e5ded6;
    border-bottom: 1px solid #e5ded6;
    padding: 16px 0;
    color: #4e453f;
    font-size: 14px;
}

.summary-card button,
.summary-card a,
.empty-cart a {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    border: 1px solid #6c5b4e;
    padding: 0 18px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.summary-card button,
.empty-cart a {
    background: #6c5b4e;
    color: #fffdf8;
}

.summary-card a {
    background: #fffdf8;
    color: #4e453f;
}

.empty-cart {
    gap: 14px;
    max-width: 440px;
    margin: 0 auto;
    border: 1px solid #e5ded6;
    background: #fffdf8;
    padding: 54px 24px;
}

.empty-cart svg {
    width: 32px;
    height: 32px;
    color: #6c5b4e;
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
