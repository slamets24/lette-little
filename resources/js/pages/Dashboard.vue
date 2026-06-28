<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ClipboardList, CreditCard, Package, PackageCheck, ShoppingBag, Truck } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as ordersIndex, show as orderShow } from '@/routes/admin/orders';
import { index as productsIndex } from '@/routes/admin/products';

type DashboardStats = {
    orders: number;
    pending_payment: number;
    ready_to_pack: number;
    in_fulfillment: number;
    completed: number;
    paid_revenue: number;
};

type NeedsAttention = {
    ready_to_pack: number;
    pending_payment: number;
    low_stock: number;
};

type RecentOrder = {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    status: string;
    payment_status: string | null;
    total: number;
    created_at: string | null;
};

type LowStockVariant = {
    id: number;
    product_name: string;
    sku: string;
    size: string;
    color: string;
    stock: number;
};

const props = defineProps<{
    stats: DashboardStats;
    needsAttention: NeedsAttention;
    recentOrders: RecentOrder[];
    lowStockVariants: LowStockVariant[];
}>();

const moneyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});
const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

const statCards = [
    {
        label: 'Orders',
        value: props.stats.orders,
        detail: 'Total order records',
        icon: ShoppingBag,
    },
    {
        label: 'Pending Payment',
        value: props.stats.pending_payment,
        detail: 'Waiting for Midtrans',
        icon: CreditCard,
    },
    {
        label: 'Ready to Pack',
        value: props.stats.ready_to_pack,
        detail: 'Paid orders',
        icon: PackageCheck,
    },
    {
        label: 'In Fulfillment',
        value: props.stats.in_fulfillment,
        detail: 'Packed or shipped',
        icon: Truck,
    },
];

function formatMoney(value: number): string {
    return moneyFormatter.format(value);
}

function formatDate(value: string | null): string {
    return value ? dateFormatter.format(new Date(value)) : 'No date';
}

function readableStatus(value: string | null): string {
    if (!value) {
        return 'No Payment';
    }

    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function orderBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (status === 'paid' || status === 'packed' || status === 'shipped') {
        return 'default';
    }

    if (status === 'cancelled') {
        return 'destructive';
    }

    if (status === 'completed') {
        return 'outline';
    }

    return 'secondary';
}

function paymentBadgeVariant(status: string | null): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (status === 'completed') {
        return 'default';
    }

    if (status === 'failed') {
        return 'destructive';
    }

    if (status === 'refunded') {
        return 'outline';
    }

    return 'secondary';
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    Operations Dashboard
                </h1>
                <p class="text-sm text-muted-foreground">
                    Review order flow, payment attention, and stock risk from one workspace.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button as-child>
                    <Link :href="ordersIndex()">
                        <ClipboardList />
                        Orders
                    </Link>
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="productsIndex()">
                        <Package />
                        Products
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Card v-for="card in statCards" :key="card.label">
                <CardHeader class="flex flex-row items-center justify-between gap-3 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ card.label }}
                    </CardTitle>
                    <component :is="card.icon" class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold tracking-normal text-foreground">
                        {{ card.value }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ card.detail }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
            <div class="grid gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between gap-3">
                        <CardTitle class="text-base">Recent Orders</CardTitle>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="ordersIndex()">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentOrders.length" class="overflow-x-auto">
                            <table class="w-full min-w-[720px] text-left text-sm">
                                <thead class="border-b text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="py-3 pr-4 font-medium">Order</th>
                                        <th class="px-4 py-3 font-medium">Customer</th>
                                        <th class="px-4 py-3 font-medium">Status</th>
                                        <th class="px-4 py-3 font-medium">Payment</th>
                                        <th class="px-4 py-3 text-right font-medium">Total</th>
                                        <th class="py-3 pl-4 text-right font-medium">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="order in recentOrders" :key="order.id" class="align-middle hover:bg-muted/30">
                                        <td class="py-3 pr-4">
                                            <Link :href="orderShow.url(order.order_number)" class="font-medium text-foreground hover:underline">
                                                {{ order.order_number }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="grid gap-0.5">
                                                <span class="font-medium text-foreground">{{ order.customer_name }}</span>
                                                <span class="text-xs text-muted-foreground">{{ order.customer_email }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="orderBadgeVariant(order.status)">
                                                {{ readableStatus(order.status) }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="paymentBadgeVariant(order.payment_status)">
                                                {{ readableStatus(order.payment_status) }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium">
                                            {{ formatMoney(order.total) }}
                                        </td>
                                        <td class="py-3 pl-4 text-right text-muted-foreground">
                                            {{ formatDate(order.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="rounded-md border border-dashed p-8 text-center text-sm text-muted-foreground">
                            No orders yet.
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between gap-3">
                        <CardTitle class="text-base">Low Stock Variants</CardTitle>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="productsIndex()">Manage stock</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="lowStockVariants.length" class="grid gap-2">
                            <div
                                v-for="variant in lowStockVariants"
                                :key="variant.id"
                                class="flex items-center justify-between gap-3 rounded-md border p-3 text-sm"
                            >
                                <div class="grid gap-0.5">
                                    <strong>{{ variant.product_name }}</strong>
                                    <span class="text-xs text-muted-foreground">
                                        {{ variant.sku }} - {{ variant.color }} / {{ variant.size }}
                                    </span>
                                </div>
                                <Badge variant="destructive">{{ variant.stock }} left</Badge>
                            </div>
                        </div>
                        <div v-else class="rounded-md border border-dashed p-8 text-center text-sm text-muted-foreground">
                            Stock levels look healthy.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <aside class="grid content-start gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <AlertTriangle class="size-4" />
                            Needs Attention
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm">
                        <Link
                            :href="ordersIndex({ query: { status: 'paid' } })"
                            class="flex items-center justify-between gap-3 rounded-md border p-3 hover:bg-muted/40"
                        >
                            <span>Ready to pack</span>
                            <strong>{{ needsAttention.ready_to_pack }}</strong>
                        </Link>
                        <Link
                            :href="ordersIndex({ query: { status: 'pending_payment' } })"
                            class="flex items-center justify-between gap-3 rounded-md border p-3 hover:bg-muted/40"
                        >
                            <span>Pending payment</span>
                            <strong>{{ needsAttention.pending_payment }}</strong>
                        </Link>
                        <Link
                            :href="productsIndex()"
                            class="flex items-center justify-between gap-3 rounded-md border p-3 hover:bg-muted/40"
                        >
                            <span>Low stock variants</span>
                            <strong>{{ needsAttention.low_stock }}</strong>
                        </Link>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Paid Revenue</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold tracking-normal text-foreground">
                            {{ formatMoney(stats.paid_revenue) }}
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Completed Midtrans payment amount recorded in the system.
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Completed Orders</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold tracking-normal text-foreground">
                            {{ stats.completed }}
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Orders that reached the final fulfillment state.
                        </p>
                    </CardContent>
                </Card>
            </aside>
        </div>
    </div>
</template>
