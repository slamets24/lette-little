<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, PackageCheck, ReceiptText, Truck } from '@lucide/vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';
import { shop } from '@/routes';
import { show as orderShow } from '@/routes/orders';

type OrderSummary = {
    id: number;
    order_number: string;
    status: string;
    subtotal: number;
    shipping_cost: number;
    total: number;
    created_at: string | null;
    items_count: number;
    items_summary: string;
    payment: {
        provider: string | null;
        status: string | null;
        amount: number | null;
        snap_url: string | null;
    };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedOrders = {
    data: OrderSummary[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

defineProps<{
    orders: PaginatedOrders;
}>();

const { formatMoney } = useCart();

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
});

function readableStatus(value: string | null) {
    if (!value) {
        return 'Waiting';
    }

    return value
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function formatDate(value: string | null) {
    return value ? dateFormatter.format(new Date(value)) : 'No date';
}

function paginationLabel(label: string) {
    return label
        .replace('&laquo; Previous', 'Previous')
        .replace('Next &raquo;', 'Next');
}

function statusIcon(status: string) {
    if (status === 'shipped' || status === 'completed') {
        return Truck;
    }

    if (status === 'paid' || status === 'packed') {
        return PackageCheck;
    }

    return CreditCard;
}
</script>

<template>
    <StorefrontLayout active="orders">
        <Head title="My Orders" />

        <section class="orders-hero">
            <p>MY ORDERS</p>
            <h1>Track every soft little package.</h1>
            <span>
                Review payment status, fulfilment progress, and order details
                from one calm place.
            </span>
        </section>

        <section class="orders-shell">
            <div v-if="orders.data.length" class="orders-list">
                <article
                    v-for="order in orders.data"
                    :key="order.id"
                    class="order-card"
                >
                    <div class="order-card-top">
                        <div class="order-icon">
                            <component :is="statusIcon(order.status)" />
                        </div>
                        <div>
                            <p>{{ order.order_number }}</p>
                            <h2>{{ readableStatus(order.status) }}</h2>
                            <span>{{ formatDate(order.created_at) }}</span>
                        </div>
                    </div>

                    <div class="order-meta">
                        <div>
                            <span>Payment</span>
                            <strong>{{
                                readableStatus(order.payment.status)
                            }}</strong>
                        </div>
                        <div>
                            <span>Items</span>
                            <strong>{{ order.items_count }}</strong>
                        </div>
                        <div>
                            <span>Total</span>
                            <strong>{{ formatMoney(order.total) }}</strong>
                        </div>
                    </div>

                    <p class="items-summary">
                        {{
                            order.items_summary ||
                            'Order items are being prepared.'
                        }}
                    </p>

                    <div class="order-actions">
                        <a
                            v-if="
                                order.payment.status === 'pending' &&
                                order.payment.snap_url
                            "
                            :href="order.payment.snap_url"
                            class="primary-link"
                        >
                            Continue Payment
                        </a>
                        <Link
                            :href="orderShow.url(order.order_number)"
                            class="secondary-link"
                        >
                            View Details
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="empty-orders">
                <ReceiptText />
                <h2>No orders yet.</h2>
                <p>
                    Your future checkout history will appear here after you
                    place an order while signed in.
                </p>
                <Link :href="shop.url()" class="primary-link">
                    Start Shopping
                </Link>
            </div>

            <nav
                v-if="orders.last_page > 1"
                class="pagination-row"
                aria-label="Order pages"
            >
                <Link
                    v-for="link in orders.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="{ active: link.active, disabled: !link.url }"
                    preserve-scroll
                >
                    {{ paginationLabel(link.label) }}
                </Link>
            </nav>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.orders-hero,
.orders-shell {
    width: min(100% - 48px, 1040px);
    margin: 0 auto;
}

.orders-hero {
    display: grid;
    justify-items: center;
    padding: 74px 0 34px;
    text-align: center;
}

.orders-hero p,
.order-card-top p {
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.orders-hero h1 {
    max-width: 700px;
    margin-top: 18px;
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: clamp(36px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.16;
}

.orders-hero > span {
    max-width: 560px;
    margin-top: 18px;
    color: var(--lette-text);
    font-size: 15px;
    line-height: 1.8;
}

.orders-shell {
    padding-bottom: 84px;
}

.orders-list {
    display: grid;
    gap: 16px;
}

.order-card,
.empty-orders {
    border: 1px solid var(--lette-border);
    background: var(--lette-surface);
}

.order-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 22px;
    align-items: center;
    padding: 22px;
}

.order-card-top {
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.order-icon {
    display: grid;
    width: 46px;
    height: 46px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid var(--lette-border);
    background: var(--lette-sand);
    color: var(--lette-brand);
}

.order-icon svg,
.empty-orders svg {
    width: 22px;
    height: 22px;
    stroke-width: 1.45;
}

.order-card h2,
.empty-orders h2 {
    color: var(--lette-ink);
    font-family: var(--lette-heading-font);
    font-weight: 500;
}

.order-card h2 {
    margin-top: 5px;
    font-size: 24px;
}

.order-card-top span,
.order-meta span,
.items-summary,
.empty-orders p {
    color: var(--lette-muted);
    font-size: 13px;
    line-height: 1.65;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.order-meta div {
    display: grid;
    gap: 3px;
    border: 1px solid var(--lette-line);
    background: var(--lette-canvas);
    padding: 12px;
}

.order-meta strong {
    color: var(--lette-ink);
    font-size: 13px;
    font-weight: 600;
}

.items-summary {
    grid-column: 1;
    border-top: 1px solid var(--lette-line);
    padding-top: 14px;
}

.order-actions {
    display: grid;
    grid-column: 2;
    grid-row: 1 / span 3;
    gap: 10px;
    align-self: stretch;
    align-content: center;
}

.primary-link,
.secondary-link,
.pagination-row a {
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

.primary-link {
    background: var(--lette-brand);
    color: var(--lette-surface);
}

.secondary-link,
.pagination-row a {
    background: var(--lette-surface);
    color: var(--lette-text);
}

.empty-orders {
    display: grid;
    justify-items: center;
    gap: 12px;
    padding: 56px 24px;
    text-align: center;
}

.empty-orders svg {
    width: 34px;
    height: 34px;
    color: var(--lette-brand);
}

.empty-orders h2 {
    font-size: 30px;
}

.empty-orders p {
    max-width: 440px;
}

.pagination-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
}

.pagination-row a {
    min-width: 46px;
    min-height: 40px;
}

.pagination-row a.active {
    background: var(--lette-brand);
    color: var(--lette-surface);
}

.pagination-row a.disabled {
    pointer-events: none;
    opacity: 0.45;
}

@media (max-width: 860px) {
    .orders-hero,
    .orders-shell {
        width: min(100% - 32px, 1040px);
    }

    .order-card {
        grid-template-columns: 1fr;
    }

    .order-actions {
        grid-column: auto;
        grid-row: auto;
        align-self: auto;
    }
}

@media (max-width: 560px) {
    .order-card {
        padding: 18px;
    }

    .order-meta {
        grid-template-columns: 1fr;
    }
}
</style>
