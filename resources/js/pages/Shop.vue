<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from '@lucide/vue';
import { computed, onMounted, reactive, ref } from 'vue';
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
const searchInput = ref<HTMLInputElement | null>(null);
const resultLabel = computed(() => {
    const total = productOptions.value.length;

    return total === 1 ? '1 piece' : `${total} pieces`;
});
const hasActiveFilters = computed(
    () =>
        Boolean(form.q) ||
        Boolean(form.category) ||
        Boolean(form.size) ||
        form.in_stock ||
        Boolean(form.min_price) ||
        Boolean(form.max_price),
);

onMounted(() => {
    if (window.location.hash === '#shop-search') {
        searchInput.value?.focus();
    }
});

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
            <label class="shop-search-field">
                <Search />
                <span class="sr-only">Search products</span>
                <input
                    id="shop-search"
                    ref="searchInput"
                    v-model="form.q"
                    type="search"
                    placeholder="Search dresses, rompers, sets..."
                />
            </label>

            <div class="price-field">
                <label>
                    <span class="sr-only">Minimum price</span>
                    <input
                        v-model="form.min_price"
                        type="number"
                        min="0"
                        step="10000"
                        inputmode="numeric"
                        placeholder="Min price"
                    />
                </label>
                <span aria-hidden="true">–</span>
                <label>
                    <span class="sr-only">Maximum price</span>
                    <input
                        v-model="form.max_price"
                        type="number"
                        min="0"
                        step="10000"
                        inputmode="numeric"
                        placeholder="Max price"
                    />
                </label>
            </div>

            <label class="stock-toggle">
                <input
                    v-model="form.in_stock"
                    type="checkbox"
                    @change="applyFilters"
                />
                In stock
            </label>

            <button type="submit"><SlidersHorizontal /> Apply</button>
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

        <div class="shop-results">
            <p aria-live="polite">Showing {{ resultLabel }}</p>
            <button v-if="hasActiveFilters" type="button" @click="resetFilters">
                Clear filters
            </button>
        </div>

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
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.shop-hero h1 {
    max-width: 720px;
    margin-top: 18px;
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: clamp(36px, 5vw, 64px);
    font-weight: 400;
    line-height: 1.16;
}

.shop-hero span {
    max-width: 590px;
    margin-top: 18px;
    color: var(--lette-text);
    font-size: 15px;
    line-height: 1.8;
}

.shop-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}

.shop-search-field,
.price-field label,
.shop-toolbar button,
.category-tabs button,
.size-tabs button,
.empty-state button {
    border: 1px solid var(--lette-border);
    background: var(--lette-surface);
    color: var(--lette-text);
}

.shop-search-field,
.price-field label {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: var(--lette-cta-height);
    padding: 0 16px;
}

.shop-search-field {
    flex: 1 1 240px;
}

.price-field {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--lette-muted);
}

.price-field label {
    width: 128px;
    padding: 0 12px;
}

.price-field input::-webkit-outer-spin-button,
.price-field input::-webkit-inner-spin-button {
    margin: 0;
    -webkit-appearance: none;
    appearance: none;
}

.shop-results {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: min(100% - 48px, 1220px);
    margin: 0 auto 20px;
    color: var(--lette-muted);
    font-size: 12px;
    letter-spacing: 0.04em;
}

.shop-results button {
    min-height: 32px;
    border: 0;
    background: transparent;
    color: var(--lette-text);
    font-size: 12px;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.shop-toolbar svg {
    width: 18px;
    height: 18px;
    color: var(--lette-brand);
    stroke-width: 1.4;
}

.shop-toolbar input {
    min-width: 0;
    flex: 1;
    border: 0;
    background: transparent;
    color: var(--lette-text);
    font-size: 13px;
}

.shop-toolbar button {
    display: inline-flex;
    min-height: var(--lette-cta-height);
    align-items: center;
    gap: 8px;
    padding: 0 var(--lette-cta-padding);
    font-size: var(--lette-cta-size);
    font-weight: 700;
    letter-spacing: var(--lette-cta-tracking);
    text-transform: uppercase;
}

.stock-toggle {
    display: inline-flex;
    min-height: var(--lette-cta-height);
    align-items: center;
    gap: 8px;
    padding: 0 4px;
    color: var(--lette-text);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.stock-toggle input {
    width: 15px;
    height: 15px;
    accent-color: var(--lette-brand);
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
    border-color: var(--lette-brand);
    background: var(--lette-brand);
    color: var(--lette-surface);
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
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: 28px;
    font-weight: 400;
}

.empty-state p {
    color: var(--lette-muted);
    font-size: 13px;
}

.empty-state button {
    min-height: 42px;
    margin-top: 8px;
    padding: 0 var(--lette-cta-padding);
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
