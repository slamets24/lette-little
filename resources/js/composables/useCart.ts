import { computed, ref } from 'vue';

export type CartProduct = {
    id: number;
    name: string;
    slug: string;
    image: string;
};

export type CartVariant = {
    id: number;
    sku: string;
    size: string;
    color: string;
    price: number;
    stock: number;
};

export type CartItem = {
    key: string;
    product: CartProduct;
    variant: CartVariant;
    quantity: number;
};

const storageKey = 'lette-little-cart';
const items = ref<CartItem[]>([]);
let initialized = false;

function canUseStorage() {
    try {
        return typeof window !== 'undefined' && Boolean(window.localStorage);
    } catch {
        return false;
    }
}

function normalizeQuantity(quantity: number, stock: number) {
    const safeStock = Math.max(0, stock);
    const safeQuantity = Number.isFinite(quantity) ? Math.floor(quantity) : 1;

    return Math.min(Math.max(1, safeQuantity), safeStock || 1);
}

function persistCart() {
    if (!canUseStorage()) {
        return;
    }

    window.localStorage.setItem(storageKey, JSON.stringify(items.value));
}

function loadCart() {
    if (initialized) {
        return;
    }

    initialized = true;

    if (!canUseStorage()) {
        return;
    }

    try {
        const storedItems = JSON.parse(
            window.localStorage.getItem(storageKey) ?? '[]',
        ) as CartItem[];

        items.value = Array.isArray(storedItems)
            ? storedItems.filter(
                  (item) =>
                      item?.key &&
                      item.product?.id &&
                      item.variant?.id &&
                      item.quantity > 0,
              )
            : [];
    } catch {
        items.value = [];
        persistCart();
    }
}

function buildItemKey(productId: number, variantId: number) {
    return `${productId}:${variantId}`;
}

function formatMoney(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

export function useCart() {
    loadCart();

    const cartCount = computed(() =>
        items.value.reduce((total, item) => total + item.quantity, 0),
    );

    const subtotal = computed(() =>
        items.value.reduce(
            (total, item) => total + item.variant.price * item.quantity,
            0,
        ),
    );

    function addItem(
        product: CartProduct,
        variant: CartVariant,
        quantity = 1,
    ): CartItem {
        const key = buildItemKey(product.id, variant.id);
        const existingItem = items.value.find((item) => item.key === key);
        const nextQuantity = normalizeQuantity(quantity, variant.stock);

        if (existingItem) {
            existingItem.quantity = normalizeQuantity(
                existingItem.quantity + nextQuantity,
                variant.stock,
            );
            existingItem.product = product;
            existingItem.variant = variant;
            persistCart();

            return existingItem;
        }

        const item = {
            key,
            product,
            variant,
            quantity: nextQuantity,
        };

        items.value.push(item);
        persistCart();

        return item;
    }

    function updateQuantity(key: string, quantity: number) {
        const item = items.value.find((cartItem) => cartItem.key === key);

        if (!item) {
            return;
        }

        item.quantity = normalizeQuantity(quantity, item.variant.stock);
        persistCart();
    }

    function removeItem(key: string) {
        items.value = items.value.filter((item) => item.key !== key);
        persistCart();
    }

    function clearCart() {
        items.value = [];
        persistCart();
    }

    function whatsAppCheckoutUrl() {
        const lines = [
            'Halo Lette Little, saya ingin pesan:',
            '',
            ...items.value.flatMap((item, index) => [
                `${index + 1}. ${item.product.name}`,
                `Variant: ${item.variant.color} / ${item.variant.size}`,
                `Qty: ${item.quantity}`,
                `Harga: ${formatMoney(item.variant.price)}`,
                '',
            ]),
            `Subtotal: ${formatMoney(subtotal.value)}`,
            '',
            'Nama:',
            'Alamat:',
            'Catatan:',
        ];
        const phone = import.meta.env.VITE_LETTE_WHATSAPP_NUMBER as
            | string
            | undefined;
        const baseUrl = phone ? `https://wa.me/${phone}` : 'https://wa.me/';

        return `${baseUrl}?text=${encodeURIComponent(lines.join('\n'))}`;
    }

    return {
        items,
        cartCount,
        subtotal,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
        formatMoney,
        whatsAppCheckoutUrl,
    };
}
