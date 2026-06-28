import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CartController from '@/actions/App/Http/Controllers/CartController';
import CartImportController from '@/actions/App/Http/Controllers/CartImportController';
import CartItemController from '@/actions/App/Http/Controllers/CartItemController';

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
    id: number;
    product: CartProduct;
    variant: CartVariant;
    quantity: number;
    line_total: number;
};

const storageKey = 'lette-little-cart';
let importAttempted = false;

function canUseStorage() {
    try {
        return typeof window !== 'undefined' && Boolean(window.localStorage);
    } catch {
        return false;
    }
}

function formatMoney(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

export function useCart() {
    const page = usePage();
    const cartCount = computed(() => page.props.cart?.count ?? 0);

    function addItem(productVariantId: number, quantity = 1): void {
        router.post(
            CartItemController.store.url(),
            {
                product_variant_id: productVariantId,
                quantity,
            },
            {
                preserveScroll: true,
            },
        );
    }

    function updateQuantity(cartItemId: number, quantity: number): void {
        router.patch(
            CartItemController.update.url(cartItemId),
            { quantity },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }

    function removeItem(cartItemId: number): void {
        router.delete(CartItemController.destroy.url(cartItemId), {
            preserveScroll: true,
        });
    }

    function clearCart(): void {
        router.delete(CartController.destroy.url(), {
            preserveScroll: true,
        });
    }

    function importLocalStorageCart(): void {
        if (importAttempted || !canUseStorage()) {
            return;
        }

        importAttempted = true;

        try {
            const stored = window.localStorage.getItem(storageKey);

            if (!stored) {
                return;
            }

            const oldCart = JSON.parse(stored);

            if (!Array.isArray(oldCart) || oldCart.length === 0) {
                window.localStorage.removeItem(storageKey);

                return;
            }

            const items = oldCart
                .filter(
                    (item) =>
                        item?.variant?.id &&
                        item.quantity > 0 &&
                        Number.isFinite(item.quantity),
                )
                .map((item) => ({
                    product_variant_id: item.variant.id,
                    quantity: Math.floor(item.quantity),
                }));

            if (items.length === 0) {
                window.localStorage.removeItem(storageKey);

                return;
            }

            fetch(CartImportController.store.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({ items }),
            })
                .then(() => {
                    window.localStorage.removeItem(storageKey);
                    router.reload({ only: ['cart'] });
                })
                .catch(() => {
                    // Silent fail
                });
        } catch {
            window.localStorage.removeItem(storageKey);
        }
    }

    if (typeof window !== 'undefined' && !importAttempted) {
        importLocalStorageCart();
    }

    return {
        cartCount,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
        formatMoney,
    };
}
