export type OrderStatus =
    | 'draft'
    | 'pending_payment'
    | 'paid'
    | 'packed'
    | 'shipped'
    | 'completed'
    | 'cancelled';

export type PaymentStatus = 'pending' | 'completed' | 'failed' | 'refunded';

export type AdminOrder = {
    id: number;
    order_number: string;
    customer: {
        name: string;
        email: string;
        phone: string;
    };
    status: OrderStatus;
    payment_status: PaymentStatus | null;
    total: number;
    created_at: string;
    updated_at: string;
};

export type AdminOrderDetail = AdminOrder & {
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
    payment: {
        provider: string;
        status: PaymentStatus;
        amount: number;
        external_reference: string | null;
        settled_at: string | null;
    } | null;
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
    q: string;
    status: OrderStatus | '';
    payment_status: PaymentStatus | '';
    date_from: string;
    date_to: string;
};

export type OrderOption = {
    label: string;
    value: string;
};

export type OrderTransition = {
    label: string;
    value: OrderStatus;
};
