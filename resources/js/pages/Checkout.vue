<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Landmark, LockKeyhole, PackageCheck } from '@lucide/vue';
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

type BankTransfer = {
    bank: string | null;
    account_name: string | null;
    account_number: string | null;
};

defineProps<{
    cart: Cart;
    cartAdjustments?: string[];
    bankTransfer?: BankTransfer;
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
                    Share your contact, delivery, and payment details. We will
                    reserve stock from your cart. Courier shipping will be
                    quoted after this order.
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
                                <span
                                    >Used only for confirming your order.</span
                                >
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
                                <small v-if="errors.name">{{
                                    errors.name
                                }}</small>
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
                                <small v-if="errors.email">{{
                                    errors.email
                                }}</small>
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
                            <small v-if="errors.phone">{{
                                errors.phone
                            }}</small>
                        </label>
                    </section>

                    <section class="form-card">
                        <div class="card-heading">
                            <LockKeyhole />
                            <div>
                                <p>Delivery Address</p>
                                <span
                                    >Courier rate will be quoted after this
                                    order.</span
                                >
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
                                <small v-if="errors.city">{{
                                    errors.city
                                }}</small>
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
                            <small v-if="errors.notes">{{
                                errors.notes
                            }}</small>
                        </label>
                    </section>

                    <section class="form-card">
                        <div class="card-heading">
                            <Landmark />
                            <div>
                                <p>Payment</p>
                                <span>
                                    Midtrans for instant checkout, or bank
                                    transfer if you prefer to pay directly.
                                </span>
                            </div>
                        </div>

                        <label class="payment-option">
                            <input
                                type="radio"
                                name="payment_method"
                                value="midtrans"
                                checked
                            />
                            <span>
                                <strong>Midtrans</strong>
                                Cards, e-wallets, and other instant methods.
                            </span>
                        </label>

                        <label class="payment-option">
                            <input
                                type="radio"
                                name="payment_method"
                                value="bank_transfer"
                            />
                            <span>
                                <strong>Bank transfer</strong>
                                Pay to our account after the order is created.
                                <em
                                    v-if="
                                        bankTransfer?.bank &&
                                        bankTransfer.account_number
                                    "
                                >
                                    {{ bankTransfer.bank }}
                                    {{ bankTransfer.account_number }}
                                    a.n. {{ bankTransfer.account_name }}
                                </em>
                            </span>
                        </label>
                        <small v-if="errors.payment_method">
                            {{ errors.payment_method }}
                        </small>
                    </section>

                    <p v-if="errors.cart" class="cart-error">
                        {{ errors.cart }}
                    </p>
                </div>

                <aside class="summary-card">
                    <p>Order Summary</p>

                    <div class="summary-items">
                        <article v-for="item in cart.items" :key="item.id">
                            <img
                                :src="item.product.image"
                                :alt="item.product.name"
                            />
                            <div>
                                <strong>{{ item.product.name }}</strong>
                                <span>
                                    {{ item.variant.color }} /
                                    {{ item.variant.size }} · Qty
                                    {{ item.quantity }}
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
                        <span>Quoted after order</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <strong>{{ formatMoney(cart.subtotal) }}</strong>
                    </div>
                    <p class="total-note">
                        Shipping is added once we confirm your courier rate.
                    </p>

                    <div class="summary-submit">
                        <button type="submit" :disabled="processing">
                            {{
                                processing
                                    ? 'Creating order...'
                                    : `Place Order · ${formatMoney(cart.subtotal)}`
                            }}
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
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.checkout-heading h1 {
    max-width: 720px;
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: clamp(38px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.12;
}

.checkout-heading span,
.card-heading span,
.summary-items span,
.summary-row,
.back-link {
    color: var(--lette-muted);
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
    color: var(--lette-brand);
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
    border: 1px solid var(--lette-border);
    background: var(--lette-surface);
    padding: 24px;
}

.card-heading {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    border-bottom: 1px solid var(--lette-line);
    padding-bottom: 16px;
}

.card-heading svg {
    width: 20px;
    height: 20px;
    color: var(--lette-brand);
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
    color: var(--lette-text);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

input,
textarea {
    width: 100%;
    border: 1px solid var(--lette-border);
    background: var(--lette-canvas);
    padding: 13px 14px;
    color: var(--lette-text);
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
    color: var(--lette-danger);
    font-size: 12px;
    line-height: 1.5;
}

.payment-option {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
    border: 1px solid var(--lette-line);
    padding: 14px;
}

.payment-option input {
    width: 16px;
    margin-top: 3px;
    padding: 0;
    border: 0;
    background: transparent;
}

.payment-option span {
    display: grid;
    gap: 4px;
    color: var(--lette-text);
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 0;
    text-transform: none;
}

.payment-option strong {
    color: var(--lette-ink);
    font-size: 14px;
}

.payment-option em {
    color: var(--lette-muted);
    font-size: 12px;
    font-style: normal;
}

.summary-card {
    position: sticky;
    top: 128px;
    background: var(--lette-sand);
}

.summary-items {
    border-top: 1px solid var(--lette-line);
    border-bottom: 1px solid var(--lette-line);
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
    background: var(--lette-placeholder);
}

.summary-items strong,
.summary-row strong,
.summary-items em {
    color: var(--lette-ink);
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
    color: var(--lette-muted);
    font-size: 12px;
}

.summary-row.total {
    border-top: 1px solid var(--lette-line);
    padding-top: 14px;
    color: var(--lette-ink);
    font-size: 15px;
}

.summary-row.total strong {
    font-size: 17px;
}

.total-note {
    margin-top: -8px;
    color: var(--lette-muted);
    font-size: 12px;
    font-weight: 400;
    letter-spacing: normal;
    line-height: 1.6;
    text-transform: none;
}

.summary-submit button {
    display: inline-flex;
    width: 100%;
    min-height: var(--lette-cta-height);
    align-items: center;
    justify-content: center;
    border: 1px solid var(--lette-brand);
    background: var(--lette-brand);
    color: var(--lette-surface);
    padding: 0 var(--lette-cta-padding);
    font-size: var(--lette-cta-size);
    font-weight: 700;
    letter-spacing: var(--lette-cta-tracking);
    text-transform: uppercase;
}

.summary-submit button:disabled {
    cursor: not-allowed;
    opacity: 0.58;
}

.back-link {
    display: inline-flex;
    min-height: 40px;
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
