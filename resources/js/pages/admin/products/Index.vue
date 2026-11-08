<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { PackagePlus, Pencil, Plus, Shirt, Tags } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { create, edit, index } from '@/routes/admin/products';
import type { AdminProduct } from '@/types/admin/product';

const props = defineProps<{
    products: AdminProduct[];
    stats: {
        products: number;
        active_products: number;
        total_stock: number;
    };
}>();

const numberFormatter = new Intl.NumberFormat('id-ID');

const statCards = [
    {
        label: 'Products',
        value: props.stats.products,
        icon: Tags,
    },
    {
        label: 'Active',
        value: props.stats.active_products,
        icon: Shirt,
    },
    {
        label: 'Stock Units',
        value: props.stats.total_stock,
        icon: PackagePlus,
    },
];

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Products',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Products" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    Products
                </h1>
                <p class="text-sm text-muted-foreground">
                    Manage catalog items, prices, variants, and stock.
                </p>
            </div>

            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Add Product
                </Link>
            </Button>
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

        <div class="overflow-hidden rounded-lg border bg-background">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b bg-muted/40 text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Variants</th>
                            <th class="px-4 py-3 font-medium">Stock</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="align-middle hover:bg-muted/30"
                        >
                            <td class="px-4 py-3">
                                <div class="grid gap-1">
                                    <span class="font-medium text-foreground">
                                        {{ product.name }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">
                                        /shop/{{ product.slug }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ product.category.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ numberFormatter.format(product.variants_count) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium">
                                    {{ numberFormatter.format(product.total_stock) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                        {{ product.is_active ? 'Active' : 'Hidden' }}
                                    </Badge>
                                    <Badge v-if="product.is_featured" variant="outline">
                                        Featured
                                    </Badge>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="edit(product.slug)">
                                        <Pencil />
                                        Edit
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!products.length">
                            <td colspan="6" class="px-4 py-12 text-center text-muted-foreground">
                                No products yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
