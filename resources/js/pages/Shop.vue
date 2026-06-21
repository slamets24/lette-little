<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from '@lucide/vue';
import { computed, reactive } from 'vue';
import ProductCard from '@/components/lette/ProductCard.vue';
import type { ProductCardProduct } from '@/components/lette/ProductCard.vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import {
    categories as fallbackCategories,
    products as fallbackProducts,
} from '@/data/lette';
import { shop } from '@/routes';

type CategoryOption = {
    id: number;
    name: string;
    slug: string;
};

type ShopFilters = {
    q: string;
    category: string;
    size: string;
    in_stock: boolean;
    min_price: number | null;
    max_price: number | null;
};

const props = defineProps<{
    categories?: CategoryOption[];
    products?: ProductCardProduct[];
    sizes?: string[];
    filters?: ShopFilters;
}>();

const defaultFilters: ShopFilters = {
    q: '',
    category: '',
    size: '',
    in_stock: false,
    min_price: null,
    max_price: null,
};

const form = reactive<ShopFilters>({ ...defaultFilters, ...props.filters });
const fallbackCategoryOptions = fallbackCategories.map((category, index) => ({
    id: index + 1,
    name: category.name,
    slug: category.slug,
}));
const categoryOptions = computed<CategoryOption[]>(
    () => props.categories ?? fallbackCategoryOptions,
);
const productOptions = computed<ProductCardProduct[]>(
    () => props.products ?? fallbackProducts,
);
const sizeOptions = computed(
    () => props.sizes ?? ['0-3M', '3-6M', '6-12M', '12-18M'],
);

function cleanFilters() {
    return Object.fromEntries(
        Object.entries(form).filter(
            ([, value]) => value !== '' && value !== null && value !== false,
        ),
    );
}

function applyFilters() {
    router.get(shop.url(), cleanFilters(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function setCategory(category: string) {
    form.category = category;
    applyFilters();
}

function setSize(size: string) {
    form.size = form.size === size ? '' : size;
    applyFilters();
}

function resetFilters() {
    form.q = '';
    form.category = '';
    form.size = '';
    form.in_stock = false;
    form.min_price = null;
    form.max_price = null;
    applyFilters();
}
</script>

<template>
    <StorefrontLayout active="shop">
        <Head title="Shop" />

        <section class="shop-hero">
            <p>SHOP</p>
            <h1>Soft essentials for every little beginning.</h1>
            <span
                >Browse calm, breathable pieces designed for newborn gifting,
                daily dressing, and slow family mornings.</span
            >
        </section>

        <form class="shop-toolbar" @submit.prevent="applyFilters">
            <label>
                <Search />
                <input
                    v-model="form.q"
                    type="search"
                    placeholder="Search dresses, rompers, sets..."
                />
            </label>
            <label class="stock-toggle">
                <input
                    v-model="form.in_stock"
                    type="checkbox"
                    @change="applyFilters"
                />
                In stock
            </label>
            <button type="submit"><SlidersHorizontal /> FILTER</button>
        </form>

        <section class="category-tabs" aria-label="Product categories">
            <button
                type="button"
                :class="{ active: form.category === '' }"
                @click="setCategory('')"
            >
                All Products
            </button>
            <button
                v-for="category in categoryOptions"
                :key="category.slug"
                type="button"
                :class="{ active: form.category === category.slug }"
                @click="setCategory(category.slug)"
            >
                {{ category.name }}
            </button>
        </section>

        <section class="size-tabs" aria-label="Product sizes">
            <button
                v-for="size in sizeOptions"
                :key="size"
                type="button"
                :class="{ active: form.size === size }"
                @click="setSize(size)"
            >
                {{ size }}
            </button>
        </section>

        <section
            v-if="productOptions.length"
            class="product-grid"
            aria-label="All products"
        >
            <ProductCard
                v-for="product in productOptions"
                :key="product.name"
                :product="product"
            />
        </section>

        <section v-else class="empty-state">
            <h2>No products found.</h2>
            <p>Try a different category, size, or search term.</p>
            <button type="button" @click="resetFilters">Reset filters</button>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.shop-hero,
.shop-toolbar,
.category-tabs,
.size-tabs,
.product-grid {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.shop-hero {
    display: grid;
    justify-items: center;
    padding: 74px 0 38px;
    text-align: center;
}

.shop-hero p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.shop-hero h1 {
    max-width: 720px;
    margin-top: 18px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(36px, 5vw, 64px);
    font-weight: 400;
    line-height: 1.16;
}

.shop-hero span {
    max-width: 590px;
    margin-top: 18px;
    color: #4e453f;
    font-size: 15px;
    line-height: 1.8;
}

.shop-toolbar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px;
    margin-bottom: 20px;
}

.shop-toolbar label:not(.stock-toggle),
.shop-toolbar button,
.category-tabs button,
.size-tabs button,
.empty-state button {
    border: 1px solid #d1c4bc;
    background: #fffdf8;
    color: #4e453f;
}

.shop-toolbar label:not(.stock-toggle) {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 16px;
}

.shop-toolbar svg {
    width: 18px;
    height: 18px;
    color: #6c5b4e;
    stroke-width: 1.4;
}

.shop-toolbar input {
    min-width: 0;
    flex: 1;
    border: 0;
    background: transparent;
    color: #4e453f;
    font-size: 13px;
}

.shop-toolbar button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 18px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

.stock-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #4e453f;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.stock-toggle input {
    width: 15px;
    height: 15px;
    accent-color: #6c5b4e;
}

.category-tabs,
.size-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.category-tabs {
    margin-bottom: 34px;
}

.size-tabs {
    margin-top: -18px;
    margin-bottom: 34px;
}

.category-tabs button,
.size-tabs button {
    min-height: 38px;
    padding: 0 15px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}

.category-tabs button.active,
.size-tabs button.active {
    border-color: #6c5b4e;
    background: #6c5b4e;
    color: #fffdf8;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 28px 16px;
    padding-bottom: 72px;
}

.empty-state {
    display: grid;
    justify-items: center;
    gap: 12px;
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
    padding: 64px 0 92px;
    text-align: center;
}

.empty-state h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 28px;
    font-weight: 400;
}

.empty-state p {
    color: #80756e;
    font-size: 13px;
}

.empty-state button {
    min-height: 42px;
    margin-top: 8px;
    padding: 0 18px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

@media (max-width: 780px) {
    .shop-hero,
    .shop-toolbar,
    .category-tabs,
    .size-tabs,
    .product-grid {
        width: min(100% - 32px, 1220px);
    }

    .shop-toolbar,
    .product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .shop-toolbar label:not(.stock-toggle) {
        grid-column: 1 / -1;
    }

    .stock-toggle {
        min-height: 42px;
    }
}
</style>
