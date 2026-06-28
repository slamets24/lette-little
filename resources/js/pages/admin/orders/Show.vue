<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, MapPin, Package, UserRound } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index } from '@/routes/admin/orders';
import type { AdminOrderDetail, OrderFilters, OrderStatus, PaymentStatus } from '@/types/admin/order';

defineProps<{
    order: AdminOrderDetail;
    filters: OrderFilters;
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

function cleanFilters(filters: OrderFilters): Partial<OrderFilters> {
    return Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== ''),
    ) as Partial<OrderFilters>;
}

function formatMoney(value: number): string {
    return moneyFormatter.format(value);
}

function formatDate(value: string | null): string {
    return value ? dateFormatter.format(new Date(value)) : 'Not settled';
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

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Orders',
                href: index(),
            },
            {
                title: 'Order Detail',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid gap-1">
                <Button variant="ghost" size="sm" class="w-fit px-0" as-child>
                    <Link :href="index({ query: cleanFilters(filters) })">
                        <ArrowLeft />
                        Back to Orders
                    </Link>
                </Button>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                        {{ order.order_number }}
                    </h1>
                    <Badge :variant="orderBadgeVariant(order.status)">
                        {{ readableStatus(order.status) }}
                    </Badge>
                    <Badge :variant="paymentBadgeVariant(order.payment_status)">
                        {{ readableStatus(order.payment_status) }}
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground">
                    Created {{ formatDate(order.created_at) }}. Last updated {{ formatDate(order.updated_at) }}.
                </p>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
            <div class="grid gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between gap-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Package class="size-4" />
                            Items
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-left text-sm">
                                <thead class="border-b text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="py-3 pr-4 font-medium">Product</th>
                                        <th class="px-4 py-3 font-medium">SKU</th>
                                        <th class="px-4 py-3 font-medium">Variant</th>
                                        <th class="px-4 py-3 text-right font-medium">Qty</th>
                                        <th class="px-4 py-3 text-right font-medium">Unit</th>
                                        <th class="py-3 pl-4 text-right font-medium">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="item in order.items" :key="item.id">
                                        <td class="py-3 pr-4 font-medium text-foreground">
                                            {{ item.product_name }}
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ item.variant_sku }}
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ item.variant_size }} / {{ item.variant_color }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ formatMoney(item.unit_price) }}
                                        </td>
                                        <td class="py-3 pl-4 text-right font-medium">
                                            {{ formatMoney(item.line_total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="order.notes">
                    <CardHeader>
                        <CardTitle class="text-base">Customer Notes</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">
                        {{ order.notes }}
                    </CardContent>
                </Card>
            </div>

            <aside class="grid gap-4 content-start">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <UserRound class="size-4" />
                            Customer
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-2 text-sm">
                        <strong>{{ order.customer.name }}</strong>
                        <span class="text-muted-foreground">{{ order.customer.email }}</span>
                        <span class="text-muted-foreground">{{ order.customer.phone }}</span>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <MapPin class="size-4" />
                            Address
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-1 text-sm text-muted-foreground">
                        <span>{{ order.address.address_line_1 }}</span>
                        <span v-if="order.address.address_line_2">{{ order.address.address_line_2 }}</span>
                        <span>{{ order.address.city }}, {{ order.address.province }} {{ order.address.postal_code }}</span>
                        <span>{{ order.address.country }}</span>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <CreditCard class="size-4" />
                            Payment
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm">
                        <template v-if="order.payment">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Provider</span>
                                <strong class="uppercase">{{ order.payment.provider }}</strong>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Status</span>
                                <Badge :variant="paymentBadgeVariant(order.payment.status)">
                                    {{ readableStatus(order.payment.status) }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Amount</span>
                                <strong>{{ formatMoney(order.payment.amount) }}</strong>
                            </div>
                            <div class="grid gap-1">
                                <span class="text-muted-foreground">Reference</span>
                                <span class="break-all font-medium">{{ order.payment.external_reference ?? 'Not available' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Settled</span>
                                <span>{{ formatDate(order.payment.settled_at) }}</span>
                            </div>
                        </template>
                        <p v-else class="text-muted-foreground">No payment record is attached.</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Totals</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">Subtotal</span>
                            <strong>{{ formatMoney(order.subtotal) }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">Shipping</span>
                            <strong>{{ formatMoney(order.shipping_cost) }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t pt-3 text-base">
                            <span>Total</span>
                            <strong>{{ formatMoney(order.total) }}</strong>
                        </div>
                    </CardContent>
                </Card>
            </aside>
        </div>
    </div>
</template>