export type AdminProduct = {
    id: number;
    name: string;
    slug: string;
    category: {
        id: number;
        name: string;
        slug: string;
    };
    variants_count: number;
    total_stock: number;
    is_active: boolean;
    is_featured: boolean;
    created_at: string;
    updated_at: string;
};

export type AdminProductDetail = {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    description: string | null;
    materials: string | null;
    care_instructions: string | null;
    base_price: number;
    is_active: boolean;
    is_featured: boolean;
    images: AdminProductImage[];
    variants: AdminProductVariant[];
    created_at: string;
    updated_at: string;
};

export type AdminProductImage = {
    id: number;
    product_id: number;
    path: string;
    alt_text: string | null;
    sort_order: number;
    is_primary: boolean;
};

export type AdminProductVariant = {
    id: number;
    product_id: number;
    sku: string;
    size: string;
    color: string;
    color_hex: string | null;
    price: number;
    stock: number;
    is_active: boolean;
};

export type AdminCategory = {
    id: number;
    name: string;
    slug: string;
};

export type ProductFormData = {
    category_id: number | null;
    name: string;
    slug: string;
    description: string;
    materials: string;
    care_instructions: string;
    base_price: number | string;
    is_active: boolean;
    is_featured: boolean;
    images: File[];
    existing_images?: AdminProductImage[];
    variants: ProductVariantFormData[];
};

export type ProductVariantFormData = {
    id?: number;
    size: string;
    color: string;
    color_hex: string;
    price: number | string;
    stock: number | string;
    sku: string;
    is_active: boolean;
};
