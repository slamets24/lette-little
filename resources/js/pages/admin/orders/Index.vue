<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ClipboardList, CreditCard, Eye, PackageCheck, RotateCcw, Search } from '@lucide/vue';
import { computed, reactive } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { index, show } from '@/routes/admin/orders';
import type { AdminOrder, OrderFilters, OrderOption, OrderStatus, PaymentStatus } from '@/types/admin/order';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedOrders = {
    data: AdminOrder[];
    links: PaginationLink[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

const props = defineProps<{
    orders: PaginatedOrders;
    filters: OrderFilters;
    stats: {
        orders: number;
        pending_payment: number;
        paid: number;
        fulfillment: number;
    };
    statusOptions: OrderOption[];
    paymentStatusOptions: OrderOption[];
}>();

const numberFormatter = new Intl.NumberFormat('id-ID');
const moneyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});
const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

const form = reactive<OrderFilters>({ ...props.filters });

const statCards = computed(() => [
    {
        label: 'Orders',
        value: props.stats.orders,
        icon: ClipboardList,
    },
    {
        label: 'Pending Payment',
        value: props.stats.pending_payment,
        icon: CreditCard,
    },
    {
        label: 'Paid',
        value: props.stats.paid,
        icon: PackageCheck,
    },
]);

const activeFilters = computed(() => cleanFilters(form));
const hasFilters = computed(() => Object.keys(activeFilters.value).length > 0);

function cleanFilters(filters: OrderFilters): Partial<OrderFilters> {
    return Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== ''),
    ) as Partial<OrderFilters>;
}

function applyFilters(): void {
    router.get(index.url(), activeFilters.value, {
        preserveState: true,
        replace: true,
    });
}

function resetFilters(): void {
    form.q = '';
    form.status = '';
    form.payment_status = '';
    form.date_from = '';
    form.date_to = '';

    router.get(index.url(), {}, {
        preserveState: true,
        replace: true,
    });
}

function formatMoney(value: number): string {
    return moneyFormatter.format(value);
}

function formatDate(value: string): string {
    return dateFormatter.format(new Date(value));
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

function orderBadgeVariant(status: OrderStatus): 'default' | 'secondary' | 'destructive' | 'outline' {
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

function paymentBadgeVariant(status: PaymentStatus | null): 'default' | 'secondary' | 'destructive' | 'outline' {
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

function paginationLabel(label: string): string {
    return label
        .replace('&laquo; Previous', 'Previous')
        .replace('Next &raquo;', 'Next');
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Orders',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Orders" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    Orders
                </h1>
                <p class="text-sm text-muted-foreground">
                    Review customer orders, payment state, and fulfillment readiness.
                </p>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <Card v-for="stat in statCards" :key="stat.label">
                <CardHeader class="flex flex-row items-center justify-between gap-3 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ stat.label }}
                    </CardTitle>
                    <component :is="stat.icon" class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold tracking-normal">
                        {{ numberFormatter.format(stat.value) }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <form class="grid gap-3 rounded-lg border bg-background p-4 lg:grid-cols-[minmax(220px,1fr)_160px_170px_150px_150px_auto]" @submit.prevent="applyFilters">
            <label class="grid gap-1.5 text-sm font-medium">
                <span>Search</span>
                <Input v-model="form.q" type="search" placeholder="Order, customer, email" />
            </label>

            <label class="grid gap-1.5 text-sm font-medium">
                <span>Order Status</span>
                <select v-model="form.status" class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    <option value="">All</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </label>

            <label class="grid gap-1.5 text-sm font-medium">
                <span>Payment</span>
                <select v-model="form.payment_status" class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    <option value="">All</option>
                    <option v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </label>

            <label class="grid gap-1.5 text-sm font-medium">
                <span>From</span>
                <Input v-model="form.date_from" type="date" />
            </label>

            <label class="grid gap-1.5 text-sm font-medium">
                <span>To</span>
                <Input v-model="form.date_to" type="date" />
            </label>

            <div class="flex items-end gap-2">
                <Button type="submit" class="flex-1 lg:flex-none">
                    <Search />
                    Filter
                </Button>
                <Button type="button" variant="outline" :disabled="!hasFilters" @click="resetFilters">
                    <RotateCcw />
                    Reset
                </Button>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border bg-background">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead class="border-b bg-muted/40 text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">Order</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Payment</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="order in orders.data" :key="order.id" class="align-middle hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <span class="font-medium text-foreground">{{ order.order_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="grid gap-1">
                                    <span class="font-medium text-foreground">{{ order.customer.name }}</span>
                                    <span class="text-xs text-muted-foreground">{{ order.customer.email }}</span>
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
                            <td class="px-4 py-3 font-medium">
                                {{ formatMoney(order.total) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(order.created_at) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="show(order.order_number, { query: activeFilters })">
                                        <Eye />
                                        View
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!orders.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                No orders match the current filters.
                                <Button v-if="hasFilters" type="button" variant="link" class="ml-1 px-0" @click="resetFilters">
                                    Reset filters
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-muted-foreground">
                Showing {{ orders.from ?? 0 }}-{{ orders.to ?? 0 }} of {{ orders.total }} orders.
            </p>
            <div class="flex flex-wrap gap-2">
                <template v-for="link in orders.links" :key="link.label">
                    <Button v-if="link.url" :variant="link.active ? 'default' : 'outline'" size="sm" as-child>
                        <Link :href="link.url" preserve-state>
                            {{ paginationLabel(link.label) }}
                        </Link>
                    </Button>
                    <Button v-else variant="outline" size="sm" disabled>
                        {{ paginationLabel(link.label) }}
                    </Button>
                </template>
            </div>
        </div>
    </div>
</template>