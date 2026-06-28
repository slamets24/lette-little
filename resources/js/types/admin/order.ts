export type OrderStatus =
    | 'pending_payment'
    | 'paid'
    | 'packed'
    | 'shipped'
    | 'completed'
    | 'cancelled';

export type PaymentStatus =
    | 'pending'
    | 'paid'
    | 'failed'
    | 'expired';

export type AdminOrder = {
    id: number;
    order_number: string;
    customer: {
        name: string;
        email: string;
        phone: string;
    };
    status: OrderStatus;
    payment_status: PaymentStatus;
    total: number;
    created_at: string;
    updated_at: string;
};

export type AdminOrderDetail = {
    id: number;
    order_number: string;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    address: {
        address_line_1: string;
        address_line_2: string | null;
        city: string;
        province: string;
        postal_code: string;
        country: string;
    };
    items: AdminOrderItem[];
    subtotal: number;
    shipping_cost: number;
    total: number;
    notes: string | null;
    status: OrderStatus;
    payment: {
        provider: string;
        status: PaymentStatus;
        amount: number;
        external_reference: string | null;
        settled_at: string | null;
    } | null;
    created_at: string;
    updated_at: string;
};

export type AdminOrderItem = {
    id: number;
    product_name: string;
    variant_sku: string;
    variant_size: string;
    variant_color: string;
    unit_price: number;
    quantity: number;
    line_total: number;
};

export type OrderFilters = {
    q?: string;
    status?: OrderStatus | '';
    payment_status?: PaymentStatus | '';
    date_from?: string;
    date_to?: string;
};
