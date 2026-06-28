<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, store, update } from '@/routes/admin/products';
import type {
    AdminCategory,
    AdminProductDetail,
    ProductFormData,
    ProductVariantFormData,
} from '@/types/admin/product';

const props = defineProps<{
    categories: AdminCategory[];
    product: AdminProductDetail | null;
}>();

const isEditing = computed(() => props.product !== null);
const title = computed(() => (isEditing.value ? 'Edit Product' : 'Add Product'));

function blankVariant(): ProductVariantFormData {
    return {
        sku: '',
        size: '',
        color: '',
        color_hex: '',
        price: props.product?.base_price ?? '',
        stock: 0,
        is_active: true,
    };
}

const form = useForm<ProductFormData>({
    category_id: props.product?.category_id ?? props.categories[0]?.id ?? null,
    name: props.product?.name ?? '',
    slug: props.product?.slug ?? '',
    description: props.product?.description ?? '',
    materials: props.product?.materials ?? '',
    care_instructions: props.product?.care_instructions ?? '',
    base_price: props.product?.base_price ?? '',
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
    images: [],
    existing_images: props.product?.images ?? [],
    variants: props.product?.variants.length
        ? props.product.variants.map((variant) => ({
              id: variant.id,
              sku: variant.sku,
              size: variant.size,
              color: variant.color,
              color_hex: variant.color_hex ?? '',
              price: variant.price,
              stock: variant.stock,
              is_active: variant.is_active,
          }))
        : [blankVariant()],
});

const activeVariantCount = computed(
    () => form.variants.filter((variant) => variant.is_active).length,
);

function addVariant() {
    form.variants.push(blankVariant());
}

function removeVariant(indexToRemove: number) {
    if (form.variants.length === 1) {
        return;
    }

    form.variants.splice(indexToRemove, 1);
}

function errorFor(field: string): string | undefined {
    return (form.errors as Record<string, string | undefined>)[field];
}

function submit() {
    if (props.product) {
        form.patch(update.url(props.product.slug), {
            preserveScroll: true,
        });

        return;
    }

    form.post(store.url(), {
        preserveScroll: true,
    });
}

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
    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    {{ title }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ activeVariantCount }} active variant{{ activeVariantCount === 1 ? '' : 's' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="index()">
                        <ArrowLeft />
                        Products
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing" form="product-form">
                    <Save />
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </Button>
            </div>
        </div>

        <form id="product-form" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]" @submit.prevent="submit">
            <div class="grid gap-5">
                <section class="rounded-lg border bg-background p-4 md:p-5">
                    <div class="mb-5 grid gap-1">
                        <h2 class="text-base font-semibold tracking-normal">Product Details</h2>
                        <p class="text-sm text-muted-foreground">Name, category, storefront copy, and visibility.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" autocomplete="off" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" autocomplete="off" placeholder="Auto from name" />
                            <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="category">Category</Label>
                            <select
                                id="category"
                                v-model.number="form.category_id"
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="text-sm text-destructive">{{ form.errors.category_id }}</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="base_price">Base Price</Label>
                            <Input id="base_price" v-model="form.base_price" type="number" min="0" inputmode="numeric" />
                            <p v-if="form.errors.base_price" class="text-sm text-destructive">{{ form.errors.base_price }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4">
                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="4" />
                            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="materials">Materials</Label>
                                <Textarea id="materials" v-model="form.materials" rows="3" />
                                <p v-if="form.errors.materials" class="text-sm text-destructive">{{ form.errors.materials }}</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="care_instructions">Care Instructions</Label>
                                <Textarea id="care_instructions" v-model="form.care_instructions" rows="3" />
                                <p v-if="form.errors.care_instructions" class="text-sm text-destructive">{{ form.errors.care_instructions }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border bg-background p-4 md:p-5">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="grid gap-1">
                            <h2 class="text-base font-semibold tracking-normal">Variants</h2>
                            <p class="text-sm text-muted-foreground">SKU, size, color, price, and stock.</p>
                        </div>

                        <Button type="button" variant="outline" size="sm" @click="addVariant">
                            <Plus />
                            Add Variant
                        </Button>
                    </div>

                    <div class="grid gap-4">
                        <article
                            v-for="(variant, variantIndex) in form.variants"
                            :key="variant.id ?? variantIndex"
                            class="grid gap-4 rounded-md border bg-muted/20 p-4"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="grid gap-1">
                                    <h3 class="text-sm font-medium">Variant {{ variantIndex + 1 }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ variant.sku || 'New SKU' }}</p>
                                </div>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    :disabled="form.variants.length === 1"
                                    @click="removeVariant(variantIndex)"
                                >
                                    <Trash2 />
                                    <span class="sr-only">Remove variant</span>
                                </Button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-sku`">SKU</Label>
                                    <Input :id="`variant-${variantIndex}-sku`" v-model="variant.sku" autocomplete="off" />
                                    <p v-if="errorFor(`variants.${variantIndex}.sku`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.sku`) }}
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-size`">Size</Label>
                                    <Input :id="`variant-${variantIndex}-size`" v-model="variant.size" autocomplete="off" />
                                    <p v-if="errorFor(`variants.${variantIndex}.size`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.size`) }}
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-color`">Color</Label>
                                    <Input :id="`variant-${variantIndex}-color`" v-model="variant.color" autocomplete="off" />
                                    <p v-if="errorFor(`variants.${variantIndex}.color`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.color`) }}
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-color-hex`">Swatch Hex</Label>
                                    <div class="flex gap-2">
                                        <Input :id="`variant-${variantIndex}-color-hex`" v-model="variant.color_hex" autocomplete="off" placeholder="#f0dfca" />
                                        <span
                                            class="size-9 rounded-md border"
                                            :style="{ backgroundColor: variant.color_hex || 'transparent' }"
                                        />
                                    </div>
                                    <p v-if="errorFor(`variants.${variantIndex}.color_hex`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.color_hex`) }}
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-price`">Price</Label>
                                    <Input :id="`variant-${variantIndex}-price`" v-model="variant.price" type="number" min="0" inputmode="numeric" />
                                    <p v-if="errorFor(`variants.${variantIndex}.price`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.price`) }}
                                    </p>
                                </div>

                                <div class="grid gap-2">
                                    <Label :for="`variant-${variantIndex}-stock`">Stock</Label>
                                    <Input :id="`variant-${variantIndex}-stock`" v-model="variant.stock" type="number" min="0" inputmode="numeric" />
                                    <p v-if="errorFor(`variants.${variantIndex}.stock`)" class="text-sm text-destructive">
                                        {{ errorFor(`variants.${variantIndex}.stock`) }}
                                    </p>
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                                <input v-model="variant.is_active" type="checkbox" class="size-4 rounded border-input accent-primary" />
                                Active variant
                            </label>
                        </article>
                    </div>
                </section>
            </div>

            <aside class="grid gap-5 self-start xl:sticky xl:top-6">
                <section class="rounded-lg border bg-background p-4 md:p-5">
                    <div class="grid gap-4">
                        <div class="grid gap-1">
                            <h2 class="text-base font-semibold tracking-normal">Publishing</h2>
                            <p class="text-sm text-muted-foreground">Control storefront visibility.</p>
                        </div>

                        <label class="flex items-center justify-between gap-4 rounded-md border p-3 text-sm">
                            <span>Active product</span>
                            <input v-model="form.is_active" type="checkbox" class="size-4 rounded border-input accent-primary" />
                        </label>
                        <p v-if="form.errors.is_active" class="text-sm text-destructive">{{ form.errors.is_active }}</p>

                        <label class="flex items-center justify-between gap-4 rounded-md border p-3 text-sm">
                            <span>Featured</span>
                            <input v-model="form.is_featured" type="checkbox" class="size-4 rounded border-input accent-primary" />
                        </label>
                        <p v-if="form.errors.is_featured" class="text-sm text-destructive">{{ form.errors.is_featured }}</p>
                    </div>
                </section>

                <section v-if="product?.images.length" class="rounded-lg border bg-background p-4 md:p-5">
                    <div class="mb-4 grid gap-1">
                        <h2 class="text-base font-semibold tracking-normal">Images</h2>
                        <p class="text-sm text-muted-foreground">Current storefront images.</p>
                    </div>
                    <div class="grid gap-3">
                        <div v-for="image in product.images" :key="image.id" class="overflow-hidden rounded-md border">
                            <img :src="image.path" :alt="image.alt_text ?? product.name" class="aspect-[4/3] w-full object-cover" />
                        </div>
                    </div>
                </section>
            </aside>
        </form>
    </div>
</template>
