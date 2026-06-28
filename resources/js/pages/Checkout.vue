<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, LockKeyhole, PackageCheck } from '@lucide/vue';
import CheckoutController from '@/actions/App/Http/Controllers/CheckoutController';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import type { CartItem } from '@/composables/useCart';
import { useCart } from '@/composables/useCart';
import { cart as cartRoute } from '@/routes';

type Cart = {
    items: CartItem[];
    count: number;
    subtotal: number;
};

defineProps<{
    cart: Cart;
    cartAdjustments?: string[];
}>();

const { formatMoney } = useCart();
</script>

<template>
    <StorefrontLayout active="cart">
        <Head title="Checkout" />

        <section class="checkout-shell">
            <div class="checkout-heading">
                <p>CHECKOUT</p>
                <h1>Almost ready for your little one.</h1>
                <span>
                    Share your contact and delivery details. We will reserve stock
                    from your cart and prepare the payment step securely.
                </span>
            </div>

            <div v-if="cartAdjustments?.length" class="adjustment-list">
                <p v-for="adjustment in cartAdjustments" :key="adjustment">
                    {{ adjustment }}
                </p>
            </div>

            <Form
                v-bind="CheckoutController.store.form()"
                class="checkout-layout"
                v-slot="{ errors, processing }"
            >
                <div class="checkout-form">
                    <section class="form-card">
                        <div class="card-heading">
                            <PackageCheck />
                            <div>
                                <p>Customer Details</p>
                                <span>Used only for confirming your order.</span>
                            </div>
                        </div>

                        <div class="field-grid two-columns">
                            <label>
                                <span>Name</span>
                                <input
                                    name="name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    placeholder="Maya Lestari"
                                />
                                <small v-if="errors.name">{{ errors.name }}</small>
                            </label>

                            <label>
                                <span>Email</span>
                                <input
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    placeholder="maya@example.com"
                                />
                                <small v-if="errors.email">{{ errors.email }}</small>
                            </label>
                        </div>

                        <label>
                            <span>Phone</span>
                            <input
                                name="phone"
                                type="tel"
                                autocomplete="tel"
                                required
                                placeholder="081234567890"
                            />
                            <small v-if="errors.phone">{{ errors.phone }}</small>
                        </label>
                    </section>

                    <section class="form-card">
                        <div class="card-heading">
                            <LockKeyhole />
                            <div>
                                <p>Delivery Address</p>
                                <span>Shipping cost will be confirmed after review.</span>
                            </div>
                        </div>

                        <label>
                            <span>Address line 1</span>
                            <input
                                name="address_line_1"
                                type="text"
                                autocomplete="address-line1"
                                required
                                placeholder="Jl. Melati No. 10"
                            />
                            <small v-if="errors.address_line_1">
                                {{ errors.address_line_1 }}
                            </small>
                        </label>

                        <label>
                            <span>Address line 2</span>
                            <input
                                name="address_line_2"
                                type="text"
                                autocomplete="address-line2"
                                placeholder="Apartment, landmark, or notes"
                            />
                            <small v-if="errors.address_line_2">
                                {{ errors.address_line_2 }}
                            </small>
                        </label>

                        <div class="field-grid two-columns">
                            <label>
                                <span>City</span>
                                <input
                                    name="city"
                                    type="text"
                                    autocomplete="address-level2"
                                    required
                                    placeholder="Bandung"
                                />
                                <small v-if="errors.city">{{ errors.city }}</small>
                            </label>

                            <label>
                                <span>Province</span>
                                <input
                                    name="province"
                                    type="text"
                                    autocomplete="address-level1"
                                    required
                                    placeholder="Jawa Barat"
                                />
                                <small v-if="errors.province">
                                    {{ errors.province }}
                                </small>
                            </label>
                        </div>

                        <div class="field-grid two-columns compact-columns">
                            <label>
                                <span>Postal code</span>
                                <input
                                    name="postal_code"
                                    type="text"
                                    autocomplete="postal-code"
                                    required
                                    placeholder="40111"
                                />
                                <small v-if="errors.postal_code">
                                    {{ errors.postal_code }}
                                </small>
                            </label>

                            <label>
                                <span>Country</span>
                                <input
                                    name="country"
                                    type="text"
                                    value="ID"
                                    maxlength="2"
                                    required
                                />
                                <small v-if="errors.country">
                                    {{ errors.country }}
                                </small>
                            </label>
                        </div>

                        <label>
                            <span>Notes</span>
                            <textarea
                                name="notes"
                                rows="4"
                                placeholder="Gift wrap, delivery notes, or anything we should know."
                            />
                            <small v-if="errors.notes">{{ errors.notes }}</small>
                        </label>
                    </section>

                    <p v-if="errors.cart" class="cart-error">{{ errors.cart }}</p>

                    <div class="mobile-submit">
                        <button type="submit" :disabled="processing">
                            {{ processing ? 'Creating order...' : 'Place Order' }}
                        </button>
                    </div>
                </div>

                <aside class="summary-card">
                    <p>Order Summary</p>

                    <div class="summary-items">
                        <article v-for="item in cart.items" :key="item.id">
                            <img :src="item.product.image" :alt="item.product.name" />
                            <div>
                                <strong>{{ item.product.name }}</strong>
                                <span>
                                    {{ item.variant.color }} / {{ item.variant.size }} ·
                                    Qty {{ item.quantity }}
                                </span>
                            </div>
                            <em>{{ formatMoney(item.line_total) }}</em>
                        </article>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>{{ formatMoney(cart.subtotal) }}</strong>
                    </div>
                    <div class="summary-row muted">
                        <span>Shipping</span>
                        <strong>Confirmed later</strong>
                    </div>

                    <div class="desktop-submit">
                        <button type="submit" :disabled="processing">
                            {{ processing ? 'Creating order...' : 'Place Order' }}
                        </button>
                    </div>

                    <Link :href="cartRoute.url()" class="back-link">
                        <ArrowLeft /> Back to Cart
                    </Link>
                </aside>
            </Form>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.checkout-shell {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
    padding: 70px 0 86px;
}

.checkout-heading {
    display: grid;
    justify-items: center;
    gap: 14px;
    margin-bottom: 34px;
    text-align: center;
}

.checkout-heading p,
.summary-card > p,
.card-heading p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.checkout-heading h1 {
    max-width: 720px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(38px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.12;
}

.checkout-heading span,
.card-heading span,
.summary-items span,
.summary-row,
.back-link {
    color: #80756e;
    font-size: 13px;
    line-height: 1.75;
}

.checkout-heading span {
    max-width: 590px;
}

.adjustment-list {
    display: grid;
    gap: 8px;
    margin-bottom: 20px;
    border: 1px solid #d8c2a9;
    background: #fff7e8;
    padding: 16px;
    color: #6c5b4e;
    font-size: 13px;
}

.checkout-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 28px;
    align-items: start;
}

.checkout-form,
.form-card,
.summary-card,
.summary-items {
    display: grid;
    gap: 16px;
}

.form-card,
.summary-card {
    border: 1px solid #d1c4bc;
    background: #fffdf8;
    padding: 24px;
}

.card-heading {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    border-bottom: 1px solid #e5ded6;
    padding-bottom: 16px;
}

.card-heading svg {
    width: 20px;
    height: 20px;
    color: #6c5b4e;
    stroke-width: 1.5;
}

.field-grid {
    display: grid;
    gap: 14px;
}

.two-columns {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.compact-columns {
    grid-template-columns: minmax(0, 1fr) 120px;
}

label {
    display: grid;
    gap: 7px;
}

label span {
    color: #4e453f;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

input,
textarea {
    width: 100%;
    border: 1px solid #d1c4bc;
    background: #fcf9f3;
    padding: 13px 14px;
    color: #4e453f;
    font-size: 13px;
}

textarea {
    resize: vertical;
}

input:focus,
textarea:focus {
    outline: 2px solid rgba(108, 91, 78, 0.22);
    outline-offset: 1px;
}

label small,
.cart-error {
    color: #a4412f;
    font-size: 12px;
    line-height: 1.5;
}

.summary-card {
    position: sticky;
    top: 128px;
    background: #f6f3ed;
}

.summary-items {
    border-top: 1px solid #e5ded6;
    border-bottom: 1px solid #e5ded6;
    padding: 16px 0;
}

.summary-items article {
    display: grid;
    grid-template-columns: 64px minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
}

.summary-items img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    background: #f0eee8;
}

.summary-items strong,
.summary-row strong,
.summary-items em {
    color: #1c1c18;
    font-style: normal;
    font-weight: 600;
}

.summary-items div {
    display: grid;
    gap: 3px;
}

.summary-items em {
    font-size: 12px;
    text-align: right;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
}

.summary-row.muted strong {
    color: #80756e;
    font-size: 12px;
}

.desktop-submit button,
.mobile-submit button {
    display: inline-flex;
    width: 100%;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    border: 1px solid #6c5b4e;
    background: #6c5b4e;
    color: #fffdf8;
    padding: 0 18px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.desktop-submit button:disabled,
.mobile-submit button:disabled {
    cursor: not-allowed;
    opacity: 0.58;
}

.mobile-submit {
    display: none;
}

.back-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.back-link svg {
    width: 15px;
    height: 15px;
}

@media (max-width: 900px) {
    .checkout-shell {
        width: min(100% - 32px, 1220px);
        padding-top: 52px;
    }

    .checkout-layout,
    .two-columns,
    .compact-columns {
        grid-template-columns: 1fr;
    }

    .summary-card {
        position: static;
    }

    .desktop-submit {
        display: none;
    }

    .mobile-submit {
        display: block;
    }
}

@media (max-width: 560px) {
    .form-card,
    .summary-card {
        padding: 18px;
    }

    .summary-items article {
        grid-template-columns: 54px minmax(0, 1fr);
    }

    .summary-items em {
        grid-column: 2 / 3;
        text-align: left;
    }
}
</style>
