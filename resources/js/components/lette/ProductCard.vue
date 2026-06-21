<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

export type ProductCardProduct = {
    name: string;
    category?: string | { name: string; slug?: string };
    price: string | number;
    image: string;
    swatches?: string[];
    available_sizes?: string[];
    stock_label?: string;
    is_low_stock?: boolean;
    href?: string;
};

const props = defineProps<{
    product: ProductCardProduct;
}>();

const priceLabel = computed(() => {
    if (typeof props.product.price === 'string') {
        return props.product.price;
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(props.product.price);
});

const categoryLabel = computed(() => {
    if (!props.product.category) {
        return '';
    }

    return typeof props.product.category === 'string'
        ? props.product.category
        : props.product.category.name;
});

const sizeLabel = computed(
    () => props.product.available_sizes?.join(', ') ?? '',
);
</script>

<template>
    <article class="product-card">
        <Link
            class="product-image"
            :href="product.href ?? '/shop'"
            :aria-label="`View ${product.name}`"
        >
            <img :src="product.image" :alt="product.name" loading="lazy" />
        </Link>
        <div class="product-meta">
            <span v-if="categoryLabel" class="product-category">{{
                categoryLabel
            }}</span>
            <h3>{{ product.name }}</h3>
            <p>{{ priceLabel }}</p>
            <small v-if="sizeLabel">{{ sizeLabel }}</small>
            <div
                v-if="product.swatches?.length"
                class="swatch-row"
                aria-label="Available colors"
            >
                <span
                    v-for="swatch in product.swatches"
                    :key="swatch"
                    :style="{ background: swatch }"
                />
            </div>
            <strong
                v-if="product.stock_label"
                :class="{ low: product.is_low_stock }"
                >{{ product.stock_label }}</strong
            >
        </div>
    </article>
</template>

<style scoped>
.product-card {
    min-width: 0;
    text-align: center;
}

.product-image {
    display: block;
    aspect-ratio: 1;
    overflow: hidden;
    background: #f0eee8;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition:
        transform 420ms ease,
        filter 420ms ease;
}

.product-card:hover .product-image img {
    filter: saturate(0.96) brightness(1.03);
    transform: scale(1.045);
}

.product-meta {
    display: grid;
    justify-items: center;
    gap: 4px;
    padding-top: 12px;
}

.product-category {
    color: #80756e;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

h3 {
    color: #4e453f;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.35;
}

p {
    color: #1c1c18;
    font-size: 11px;
    font-weight: 500;
}

small {
    color: #80756e;
    font-size: 10px;
    line-height: 1.4;
}

strong {
    margin-top: 3px;
    color: #5d6c4e;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

strong.low {
    color: #9a6a35;
}

.swatch-row {
    display: flex;
    gap: 6px;
    margin-top: 3px;
}

.swatch-row span {
    width: 11px;
    height: 11px;
    border: 1px solid rgba(108, 91, 78, 0.18);
    border-radius: 999px;
}
</style>
