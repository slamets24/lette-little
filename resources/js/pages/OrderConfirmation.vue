<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, CreditCard, Home, PackageCheck } from '@lucide/vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';
import { home, shop } from '@/routes';

type OrderItem = {
    id: number;
    product_name: string;
    variant_sku: string;
    variant_size: string;
    variant_color: string;
    unit_price: number;
    quantity: number;
    line_total: number;
};

type Order = {
    id: number;
    order_number: string;
    status: string;
    subtotal: number;
    shipping_cost: number;
    total: number;
    notes: string | null;
    created_at: string | null;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    address: {
        id: number;
        address_line_1: string;
        address_line_2: string | null;
        city: string;
        province: string;
        postal_code: string;
        country: string;
    };
    items: OrderItem[];
    payment: {
        provider: string | null;
        status: string | null;
        amount: number | null;
        snap_url: string | null;
    };
};

const props = defineProps<{
    order: Order;
}>();

const { formatMoney } = useCart();

function readableStatus(value: string | null) {
    if (!value) {
        return 'Waiting';
    }

    return value
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

const addressLines = [
    props.order.address.address_line_1,
    props.order.address.address_line_2,
    `${props.order.address.city}, ${props.order.address.province} ${props.order.address.postal_code}`,
    props.order.address.country,
].filter((line): line is string => Boolean(line));
</script>

<template>
    <StorefrontLayout active="cart">
        <Head :title="`Order ${order.order_number}`" />

        <section class="confirmation-shell">
            <div class="success-card">
                <CheckCircle2 />
                <p>ORDER RECEIVED</p>
                <h1>Thank you, {{ order.customer.name }}.</h1>
                <span v-if="order.payment.status === 'pending' && order.payment.snap_url">
                    Your order {{ order.order_number }} has been created. Complete
                    payment through the secure Midtrans page so we can prepare it
                    for fulfilment.
                </span>
                <span v-else-if="order.payment.status === 'pending'">
                    Your order {{ order.order_number }} has been created, but the
                    payment page is temporarily unavailable. Contact us and we will
                    help continue the payment manually.
                </span>
                <span v-else>
                    Your order {{ order.order_number }} has been created. Shipping
                    will be confirmed after we review your delivery details.
                </span>
            </div>

            <div class="confirmation-layout">
                <section class="details-column">
                    <article class="info-card">
                        <div class="card-heading">
                            <PackageCheck />
                            <div>
                                <p>Order Details</p>
                                <span>{{ order.order_number }}</span>
                            </div>
                        </div>

                        <div class="status-grid">
                            <div>
                                <span>Order Status</span>
                                <strong>{{ readableStatus(order.status) }}</strong>
                            </div>
                            <div>
                                <span>Payment Status</span>
                                <strong>{{ readableStatus(order.payment.status) }}</strong>
                            </div>
                        </div>

                        <div class="item-list">
                            <article v-for="item in order.items" :key="item.id">
                                <div>
                                    <strong>{{ item.product_name }}</strong>
                                    <span>
                                        {{ item.variant_color }} /
                                        {{ item.variant_size }} · SKU
                                        {{ item.variant_sku }} · Qty
                                        {{ item.quantity }}
                                    </span>
                                </div>
                                <em>{{ formatMoney(item.line_total) }}</em>
                            </article>
                        </div>
                    </article>

                    <article class="info-card">
                        <div class="card-heading">
                            <Home />
                            <div>
                                <p>Delivery Details</p>
                                <span>We will confirm shipping before fulfilment.</span>
                            </div>
                        </div>

                        <div class="customer-grid">
                            <div>
                                <span>Name</span>
                                <strong>{{ order.customer.name }}</strong>
                            </div>
                            <div>
                                <span>Email</span>
                                <strong>{{ order.customer.email }}</strong>
                            </div>
                            <div>
                                <span>Phone</span>
                                <strong>{{ order.customer.phone }}</strong>
                            </div>
                        </div>

                        <address>
                            <span v-for="line in addressLines" :key="line">
                                {{ line }}
                            </span>
                        </address>

                        <p v-if="order.notes" class="order-notes">
                            “{{ order.notes }}”
                        </p>
                    </article>
                </section>

                <aside class="payment-card">
                    <CreditCard />
                    <p>Payment</p>
                    <h2>{{ formatMoney(order.total) }}</h2>
                    <span v-if="order.payment.status === 'completed'">
                        Payment received through Midtrans. We will prepare the order
                        and confirm shipping before fulfilment.
                    </span>
                    <span v-else-if="order.payment.status === 'failed'">
                        Payment was not completed. Please contact us if you still want
                        to continue this order.
                    </span>
                    <span v-else-if="order.payment.snap_url">
                        Complete your payment through the secure Midtrans page. If you
                        already paid, this status will update after Midtrans confirms
                        the payment.
                    </span>
                    <span v-else>
                        The payment page is temporarily unavailable. Contact us and
                        share your order number so we can help continue this order.
                    </span>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>{{ formatMoney(order.subtotal) }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <strong>
                            {{ order.shipping_cost > 0 ? formatMoney(order.shipping_cost) : 'Confirmed later' }}
                        </strong>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total</span>
                        <strong>{{ formatMoney(order.total) }}</strong>
                    </div>

                    <a
                        v-if="order.payment.status === 'pending' && order.payment.snap_url"
                        :href="order.payment.snap_url"
                        class="primary-link"
                    >
                        Continue Payment
                    </a>
                    <a
                        v-else-if="order.payment.status === 'pending'"
                        href="mailto:hello@lettelittle.id"
                        class="primary-link"
                    >
                        Contact Support
                    </a>
                    <Link v-else :href="shop.url()" class="primary-link">
                        Continue Shopping
                    </Link>
                    <Link :href="home.url()" class="secondary-link">Back Home</Link>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.confirmation-shell {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
    padding: 70px 0 86px;
}

.success-card {
    display: grid;
    justify-items: center;
    gap: 14px;
    margin-bottom: 30px;
    border: 1px solid #d1c4bc;
    background: #fffdf8;
    padding: 48px 24px;
    text-align: center;
}

.success-card > svg {
    width: 42px;
    height: 42px;
    color: #5d6c4e;
    stroke-width: 1.45;
}

.success-card p,
.info-card p,
.payment-card p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.success-card h1,
.payment-card h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-weight: 400;
}

.success-card h1 {
    max-width: 720px;
    font-size: clamp(38px, 5vw, 62px);
    line-height: 1.12;
}

.success-card span,
.card-heading span,
.item-list span,
.customer-grid span,
.payment-card > span,
.summary-row,
address,
.order-notes {
    color: #80756e;
    font-size: 13px;
    line-height: 1.75;
}

.success-card > span {
    max-width: 650px;
}

.confirmation-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 28px;
    align-items: start;
}

.details-column,
.info-card,
.item-list,
.payment-card {
    display: grid;
    gap: 16px;
}

.info-card,
.payment-card {
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

.card-heading svg,
.payment-card > svg {
    width: 20px;
    height: 20px;
    color: #6c5b4e;
    stroke-width: 1.5;
}

.status-grid,
.customer-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.status-grid div,
.customer-grid div {
    display: grid;
    gap: 3px;
    border: 1px solid #e5ded6;
    background: #fcf9f3;
    padding: 14px;
}

.status-grid strong,
.customer-grid strong,
.item-list strong,
.summary-row strong {
    color: #1c1c18;
    font-weight: 600;
}

.item-list {
    border-top: 1px solid #e5ded6;
    padding-top: 16px;
}

.item-list article {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 18px;
}

.item-list div {
    display: grid;
    gap: 3px;
}

.item-list em {
    color: #1c1c18;
    font-size: 13px;
    font-style: normal;
    font-weight: 600;
}

address {
    display: grid;
    gap: 2px;
    border-top: 1px solid #e5ded6;
    padding-top: 16px;
    font-style: normal;
}

.order-notes {
    border-left: 2px solid #d1c4bc;
    padding-left: 12px;
}

.payment-card {
    position: sticky;
    top: 128px;
    background: #f6f3ed;
}

.payment-card > svg {
    width: 28px;
    height: 28px;
}

.payment-card h2 {
    font-size: 34px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    border-top: 1px solid #e5ded6;
    padding-top: 12px;
}

.total-row strong,
.total-row span {
    color: #1c1c18;
}

.primary-link,
.secondary-link {
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

.primary-link {
    background: #6c5b4e;
    color: #fffdf8;
}

.secondary-link {
    background: #fffdf8;
    color: #4e453f;
}

@media (max-width: 900px) {
    .confirmation-shell {
        width: min(100% - 32px, 1220px);
        padding-top: 52px;
    }

    .confirmation-layout,
    .status-grid,
    .customer-grid {
        grid-template-columns: 1fr;
    }

    .payment-card {
        position: static;
    }
}

@media (max-width: 560px) {
    .success-card,
    .info-card,
    .payment-card {
        padding: 18px;
    }

    .item-list article {
        grid-template-columns: 1fr;
        gap: 6px;
    }
}
</style>
