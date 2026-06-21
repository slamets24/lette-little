<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Check,
    Leaf,
    Package,
    Ruler,
    ShieldCheck,
    ShoppingBag,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ProductCard from '@/components/lette/ProductCard.vue';
import type { ProductCardProduct } from '@/components/lette/ProductCard.vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';

type ProductImage = {
    id: number;
    path: string;
    alt_text: string;
    is_primary: boolean;
};

type ProductVariant = {
    id: number;
    sku: string;
    size: string;
    color: string;
    color_hex: string | null;
    price: number;
    stock: number;
    is_available: boolean;
};

type ProductDetail = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    materials: string | null;
    care_instructions: string | null;
    category: {
        name: string;
        slug: string;
    };
    images: ProductImage[];
    variants: ProductVariant[];
};

const props = defineProps<{
    product: ProductDetail;
    relatedProducts: ProductCardProduct[];
}>();

const fallbackImage = '/images/lette/stitch-product-dress.jpg';
const currentImage = ref(props.product.images[0]?.path ?? fallbackImage);
const quantity = ref(1);
const justAdded = ref(false);
const { addItem } = useCart();
const selectedVariantId = ref<number | null>(
    props.product.variants.find((variant) => variant.is_available)?.id ??
        props.product.variants[0]?.id ??
        null,
);

const selectedVariant = computed(
    () =>
        props.product.variants.find(
            (variant) => variant.id === selectedVariantId.value,
        ) ??
        props.product.variants[0] ??
        null,
);
const totalStock = computed(() =>
    props.product.variants.reduce((total, variant) => total + variant.stock, 0),
);
const stockLabel = computed(() => {
    if (!selectedVariant.value) {
        return 'Unavailable';
    }

    if (selectedVariant.value.stock === 0) {
        return 'Sold out';
    }

    if (selectedVariant.value.stock <= 3) {
        return `Only ${selectedVariant.value.stock} left`;
    }

    return 'In stock';
});
const maxQuantity = computed(() => selectedVariant.value?.stock ?? 1);
const canAddToCart = computed(
    () => Boolean(selectedVariant.value?.is_available) && maxQuantity.value > 0,
);

function changeQuantity(nextQuantity: number) {
    quantity.value = Math.min(Math.max(1, nextQuantity), maxQuantity.value || 1);
}

function selectVariant(variant: ProductVariant) {
    selectedVariantId.value = variant.id;
    changeQuantity(quantity.value);
    justAdded.value = false;
}

function addToCart() {
    if (!selectedVariant.value || !canAddToCart.value) {
        return;
    }

    addItem(
        {
            id: props.product.id,
            name: props.product.name,
            slug: props.product.slug,
            image: currentImage.value,
        },
        {
            id: selectedVariant.value.id,
            sku: selectedVariant.value.sku,
            size: selectedVariant.value.size,
            color: selectedVariant.value.color,
            price: selectedVariant.value.price,
            stock: selectedVariant.value.stock,
        },
        quantity.value,
    );

    justAdded.value = true;
}

function money(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}
</script>

<template>
    <StorefrontLayout active="shop">
        <Head :title="product.name" />

        <section class="product-shell">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <Link href="/shop">Shop</Link>
                <span>/</span>
                <Link :href="`/shop?category=${product.category.slug}`">{{
                    product.category.name
                }}</Link>
            </nav>

            <div class="product-layout">
                <div class="media-column">
                    <div class="main-image">
                        <img :src="currentImage" :alt="product.name" />
                    </div>
                    <div
                        v-if="product.images.length > 1"
                        class="thumbnail-row"
                        aria-label="Product images"
                    >
                        <button
                            v-for="image in product.images"
                            :key="image.id"
                            type="button"
                            :class="{ active: currentImage === image.path }"
                            @click="currentImage = image.path"
                        >
                            <img :src="image.path" :alt="image.alt_text" />
                        </button>
                    </div>
                </div>

                <article class="product-copy">
                    <p class="eyebrow">{{ product.category.name }}</p>
                    <h1>{{ product.name }}</h1>
                    <strong>{{
                        selectedVariant
                            ? money(selectedVariant.price)
                            : money(0)
                    }}</strong>
                    <p class="description">{{ product.description }}</p>

                    <section
                        class="variant-panel"
                        aria-label="Product variants"
                    >
                        <div class="option-heading">
                            <span>Choose Size & Color</span>
                            <em>{{ stockLabel }}</em>
                        </div>
                        <div class="variant-grid">
                            <button
                                v-for="variant in product.variants"
                                :key="variant.id"
                                type="button"
                                :class="{
                                    selected: selectedVariantId === variant.id,
                                }"
                                :disabled="!variant.is_available"
                                @click="selectVariant(variant)"
                            >
                                <span>
                                    <i
                                        v-if="variant.color_hex"
                                        :style="{
                                            background: variant.color_hex,
                                        }"
                                    />
                                    {{ variant.color }}
                                </span>
                                <strong>{{ variant.size }}</strong>
                                <small>{{
                                    variant.stock > 0
                                        ? `${variant.stock} pcs`
                                        : 'Sold out'
                                }}</small>
                            </button>
                        </div>
                    </section>

                    <div class="purchase-panel">
                        <div class="quantity-control" aria-label="Quantity">
                            <button
                                type="button"
                                aria-label="Decrease quantity"
                                @click="changeQuantity(quantity - 1)"
                            >
                                −
                            </button>
                            <span>{{ quantity }}</span>
                            <button
                                type="button"
                                aria-label="Increase quantity"
                                @click="changeQuantity(quantity + 1)"
                            >
                                +
                            </button>
                        </div>

                        <div class="action-row">
                            <button
                                type="button"
                                :disabled="!canAddToCart"
                                @click="addToCart"
                            >
                                <ShoppingBag /> Add to Cart
                            </button>
                            <Link href="/cart">View Cart</Link>
                        </div>

                        <p v-if="justAdded" class="cart-notice">
                            Added to cart. You can continue shopping or checkout by WhatsApp.
                        </p>
                    </div>

                    <div class="trust-grid">
                        <article>
                            <ShieldCheck />
                            <span>Checkout ready</span>
                        </article>
                        <article>
                            <Package />
                            <span>{{ totalStock }} total stock</span>
                        </article>
                    </div>
                </article>
            </div>
        </section>

        <section class="detail-info">
            <article>
                <Leaf />
                <h2>Materials</h2>
                <p>{{ product.materials }}</p>
            </article>
            <article>
                <Check />
                <h2>Care</h2>
                <p>{{ product.care_instructions }}</p>
            </article>
            <article>
                <Ruler />
                <h2>Size Guide</h2>
                <p>
                    Choose by baby's current height and weight. If between
                    sizes, pick the larger size for longer wear.
                </p>
            </article>
        </section>

        <section v-if="relatedProducts.length" class="related-section">
            <div class="section-heading">
                <p>Related</p>
                <h2>More from {{ product.category.name }}</h2>
            </div>
            <div class="related-grid">
                <ProductCard
                    v-for="relatedProduct in relatedProducts"
                    :key="relatedProduct.name"
                    :product="relatedProduct"
                />
            </div>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.product-shell,
.detail-info,
.related-section {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.product-shell {
    padding: 38px 0 52px;
}

.breadcrumb {
    display: flex;
    gap: 9px;
    margin-bottom: 24px;
    color: #80756e;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.breadcrumb a:hover {
    color: #1c1c18;
}

.product-layout {
    display: grid;
    grid-template-columns: minmax(320px, 0.9fr) minmax(420px, 0.78fr);
    gap: clamp(28px, 4vw, 54px);
    align-items: start;
    justify-content: center;
}

.main-image {
    aspect-ratio: 1;
    max-height: min(68vh, 680px);
    overflow: hidden;
    background: #f0eee8;
}

.main-image img,
.thumbnail-row img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-row {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.thumbnail-row button {
    aspect-ratio: 1;
    overflow: hidden;
    border: 1px solid transparent;
    background: #f0eee8;
}

.thumbnail-row button.active {
    border-color: #6c5b4e;
}

.product-copy {
    display: grid;
    gap: 18px;
    max-width: 620px;
    padding-top: 10px;
}

.eyebrow,
.section-heading p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.product-copy h1 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(38px, 4vw, 58px);
    font-weight: 400;
    line-height: 1.1;
}

.product-copy > strong {
    color: #1c1c18;
    font-size: 20px;
    font-weight: 600;
}

.description {
    max-width: 560px;
    color: #4e453f;
    font-size: 15px;
    line-height: 1.85;
}

.variant-panel {
    display: grid;
    gap: 14px;
    border-top: 1px solid #e5ded6;
    padding-top: 20px;
}

.option-heading {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    color: #4e453f;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.option-heading em {
    color: #80756e;
    font-style: normal;
}

.variant-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: stretch;
}

.variant-grid button {
    display: grid;
    flex: 0 0 112px;
    gap: 4px;
    width: 112px;
    min-height: 86px;
    box-sizing: border-box;
    border: 1px solid #d1c4bc;
    background: #fffdf8;
    padding: 13px;
    color: #4e453f;
    text-align: left;
}

.variant-grid button.selected {
    border-color: #6c5b4e;
    box-shadow: inset 0 0 0 1px #6c5b4e;
}

.variant-grid button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.variant-grid span {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.variant-grid i {
    width: 13px;
    height: 13px;
    border: 1px solid rgba(108, 91, 78, 0.18);
    border-radius: 999px;
}

.variant-grid strong {
    font-size: 16px;
    font-weight: 600;
}

.variant-grid small {
    color: #80756e;
    font-size: 11px;
}

.purchase-panel {
    display: grid;
    gap: 12px;
}

.quantity-control {
    display: inline-grid;
    grid-template-columns: 48px 60px 48px;
    width: 156px;
    height: 48px;
    border: 1px solid #d1c4bc;
    background: #fffdf8;
}

.quantity-control button,
.quantity-control span {
    display: grid;
    min-width: 0;
    place-items: center;
    color: #4e453f;
    font-size: 14px;
    font-weight: 700;
}

.quantity-control span {
    border-inline: 1px solid #e5ded6;
}

.action-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.action-row button,
.action-row a {
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

.action-row button {
    min-width: 164px;
    gap: 8px;
    background: #6c5b4e;
    color: #fffdf8;
}

.action-row button svg {
    width: 16px;
    height: 16px;
    stroke-width: 1.5;
}

.cart-notice {
    color: #5d6c4e;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.6;
}

.action-row button:disabled {
    cursor: not-allowed;
    opacity: 0.46;
}

.action-row a {
    min-width: 150px;
    background: #fffdf8;
    color: #4e453f;
}

.trust-grid,
.detail-info {
    display: grid;
    gap: 12px;
}

.trust-grid {
    grid-template-columns: repeat(2, minmax(0, 170px));
}

.trust-grid article,
.detail-info article {
    border: 1px solid #e5ded6;
    background: #f6f3ed;
    color: #4e453f;
}

.trust-grid article {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 52px;
    padding: 0 14px;
    font-size: 12px;
    font-weight: 600;
}

.trust-grid svg,
.detail-info svg {
    width: 18px;
    height: 18px;
    color: #6c5b4e;
    stroke-width: 1.45;
}

.detail-info {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    padding-bottom: 58px;
}

.detail-info article {
    display: grid;
    gap: 10px;
    align-content: start;
    padding: 24px;
}

.detail-info h2,
.section-heading h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-weight: 400;
}

.detail-info h2 {
    font-size: 22px;
}

.detail-info p {
    color: #4e453f;
    font-size: 13px;
    line-height: 1.75;
}

.related-section {
    padding-bottom: 76px;
}

.section-heading {
    display: grid;
    justify-items: center;
    gap: 8px;
    margin-bottom: 28px;
    text-align: center;
}

.section-heading h2 {
    font-size: clamp(26px, 3vw, 36px);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 28px 16px;
}

@media (max-width: 900px) {
    .product-shell,
    .detail-info,
    .related-section {
        width: min(100% - 32px, 1220px);
    }

    .product-layout,
    .detail-info {
        grid-template-columns: 1fr;
    }

    .product-copy {
        max-width: none;
    }

    .main-image {
        max-height: none;
    }

    .related-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 560px) {
    .action-row,
    .trust-grid {
        grid-template-columns: 1fr;
    }

    .variant-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .variant-grid button {
        width: 100%;
    }

    .quantity-control {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
    }

    .action-row {
        display: grid;
    }

    .action-row button,
    .action-row a {
        width: 100%;
    }
}

@media (max-width: 380px) {
    .variant-grid {
        grid-template-columns: 1fr;
    }
}
</style>
