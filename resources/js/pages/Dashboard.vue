<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    CheckCircle,
    ChevronRight,
    Clock,
    CreditCard,
    FileText,
    FolderOpen,
    Package,
    PackageCheck,
    ShoppingBag,
    Timer,
    TrendingUp,
    Truck,
} from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import { index as ordersIndex, show as orderShow } from '@/routes/admin/orders';
import { index as productsIndex } from '@/routes/admin/products';

type Stat = {
    label: string;
    value: string;
    direction: 'up' | 'down' | 'flat';
    delta: string;
    tone: 'good' | 'bad' | 'neutral';
    hint: string;
};

type Stage = {
    key: string;
    label: string;
    count: number;
    href: string;
    tone: string;
};

type Point = {
    label: string;
    masuk: number;
    selesai: number;
    batal: number;
};

type Slice = {
    label: string;
    value: number;
    percent: number;
    color: string;
};

type Bar = {
    label: string;
    value: number;
    percent: number;
    color?: string;
};

type ProductRow = {
    name: string;
    sold: number;
    stock: number;
    share: number;
};

type RecentOrder = {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    status: string;
    status_label?: string;
    payment_provider?: string;
    payment_status: string | null;
    total: number;
    created_at: string | null;
    updated_at?: string | null;
};

type LowStockVariant = {
    id: number;
    product_name: string;
    sku: string;
    size: string;
    color: string;
    stock: number;
};

type NeedsAttention = {
    ready_to_pack: number;
    pending_payment: number;
    low_stock: number;
};

type DashboardStats = {
    orders: number;
    pending_payment: number;
    ready_to_pack: number;
    in_fulfillment: number;
    completed: number;
    paid_revenue: number;
};

const props = defineProps<{
    range?: number;
    stats: DashboardStats;
    kpiStats?: Record<'orders' | 'inProgress' | 'overdue' | 'paidRate' | 'leadTime' | 'finishedToday', Stat>;
    pipeline?: { stages: Stage[]; conversion: number };
    series?: Point[];
    distribution?: { total: number; slices: Slice[] };
    ages?: Bar[];
    categories?: Bar[];
    topProducts?: ProductRow[];
    needsAttention: NeedsAttention;
    recentOrders: RecentOrder[];
    lowStockVariants: LowStockVariant[];
}>();

const moneyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

function formatMoney(value: number): string {
    return moneyFormatter.format(value);
}

const selectedRange = computed({
    get: () => String(props.range ?? 7),
    set: (value: string) => {
        router.get(dashboard(), { range: Number(value) }, { preserveState: true, preserveScroll: true, replace: true });
    },
});

const cards = computed(() => {
    if (props.kpiStats) {
        return [
            { ...props.kpiStats.orders, icon: FileText, tint: 'bg-[#6c5b4e] text-white' },
            { ...props.kpiStats.inProgress, icon: FolderOpen, tint: 'bg-[#f5efe6] text-[#6c5b4e]' },
            { ...props.kpiStats.overdue, icon: Clock, tint: 'bg-[#fbeee8] text-[#a4412f]' },
            { ...props.kpiStats.paidRate, icon: TrendingUp, tint: 'bg-[#f5efe6] text-[#6c5b4e]' },
            { ...props.kpiStats.leadTime, icon: Timer, tint: 'bg-[#fcf9f3] text-[#28231f] border border-[#d1c4bc]' },
            { ...props.kpiStats.finishedToday, icon: CheckCircle, tint: 'bg-[#6c5b4e] text-[#fffdf8]' },
        ];
    }

    return [
        {
            label: 'Semua Pesanan',
            value: String(props.stats.orders),
            direction: 'flat' as const,
            delta: 'total',
            tone: 'neutral' as const,
            hint: 'total pesanan masuk',
            icon: ShoppingBag,
            tint: 'bg-[#6c5b4e] text-white',
        },
        {
            label: 'Siap Dikemas',
            value: String(props.stats.ready_to_pack),
            direction: 'flat' as const,
            delta: 'lunas',
            tone: 'good' as const,
            hint: 'perlu dipacking',
            icon: PackageCheck,
            tint: 'bg-[#f5efe6] text-[#6c5b4e]',
        },
        {
            label: 'Menunggu Bayar',
            value: String(props.stats.pending_payment),
            direction: 'flat' as const,
            delta: 'menunggu',
            tone: 'neutral' as const,
            hint: 'menunggu konfirmasi',
            icon: CreditCard,
            tint: 'bg-[#fbeee8] text-[#a4412f]',
        },
        {
            label: 'Dalam Pengiriman',
            value: String(props.stats.in_fulfillment),
            direction: 'flat' as const,
            delta: 'transit',
            tone: 'neutral' as const,
            hint: 'dikemas & dikirim',
            icon: Truck,
            tint: 'bg-[#f5efe6] text-[#6c5b4e]',
        },
        {
            label: 'Selesai',
            value: String(props.stats.completed),
            direction: 'flat' as const,
            delta: 'berhasil',
            tone: 'good' as const,
            hint: 'pesanan diterima',
            icon: CheckCircle,
            tint: 'bg-[#6c5b4e] text-[#fffdf8]',
        },
        {
            label: 'Total Omzet',
            value: formatMoney(props.stats.paid_revenue),
            direction: 'flat' as const,
            delta: 'lunas',
            tone: 'good' as const,
            hint: 'pendapatan terverifikasi',
            icon: TrendingUp,
            tint: 'bg-[#6c5b4e] text-white',
        },
    ];
});

const defaultPipelineStages = [
    { key: 'pending_payment', label: 'Menunggu Bayar', count: props.stats.pending_payment, href: ordersIndex.url({ status: 'pending_payment' }), tone: 'amber' },
    { key: 'paid', label: 'Siap Kemas', count: props.stats.ready_to_pack, href: ordersIndex.url({ status: 'paid' }), tone: 'sky' },
    { key: 'packed', label: 'Dikemas', count: 0, href: ordersIndex.url({ status: 'packed' }), tone: 'violet' },
    { key: 'shipped', label: 'Dikirim', count: props.stats.in_fulfillment - props.stats.ready_to_pack, href: ordersIndex.url({ status: 'shipped' }), tone: 'blue' },
    { key: 'completed', label: 'Selesai', count: props.stats.completed, href: ordersIndex.url({ status: 'completed' }), tone: 'green' },
];

const pipelineStages = computed(() => props.pipeline?.stages ?? defaultPipelineStages);
const pipelineConversion = computed(() => props.pipeline?.conversion ?? (props.stats.orders > 0 ? Math.round((props.stats.completed / props.stats.orders) * 100) : 0));

const stageIconMap = {
    pending_payment: Clock,
    paid: PackageCheck,
    packed: Package,
    shipped: Truck,
    completed: CheckCircle,
};

const stageTintMap: Record<string, string> = {
    amber: 'bg-[#fbeee8] text-[#a4412f]',
    sky: 'bg-[#f5efe6] text-[#6c5b4e]',
    violet: 'bg-[#f0ece7] text-[#4e453f]',
    blue: 'bg-[#e9f1f5] text-[#2c5282]',
    green: 'bg-[#eaf3e6] text-[#3d6532]',
};

// SVG Multi-line Chart
const chart = computed(() => {
    const rawSeries = props.series ?? [];
    const width = 520;
    const height = 168;
    const pad = { top: 16, right: 28, bottom: 24, left: 28 };
    const max = Math.max(1, ...rawSeries.flatMap((p) => [p.masuk, p.selesai, p.batal]));
    const step = rawSeries.length > 1 ? (width - pad.left - pad.right) / (rawSeries.length - 1) : 0;
    const y = (val: number) => pad.top + (1 - val / max) * (height - pad.top - pad.bottom);
    const coords = (key: 'masuk' | 'selesai' | 'batal') =>
        rawSeries.map((p, idx) => `${pad.left + idx * step},${y(p[key])}`).join(' ');
    const ticks = [...new Set([0, Math.round(max / 2), max])];
    const labelEvery = rawSeries.length > 10 ? 5 : 1;

    return { width, height, pad, max, step, y, coords, ticks, labelEvery, rawSeries };
});

// Donut Chart
const donut = computed(() => {
    const slices = props.distribution?.slices ?? [
        { label: 'Lunas', value: props.stats.ready_to_pack + props.stats.completed, percent: 80, color: '#6c5b4e' },
        { label: 'Belum Lunas', value: props.stats.pending_payment, percent: 20, color: '#d1c4bc' },
    ];
    const total = props.distribution?.total ?? (props.stats.orders || 1);
    let cursor = 0;
    const stops = slices.map((s) => {
        const start = cursor;
        cursor += s.percent;
        return `${s.color} ${start}% ${cursor}%`;
    });
    return `conic-gradient(${stops.join(', ')})`;
});

const statusBadgeClasses: Record<string, string> = {
    pending_payment: 'bg-[#fbeee8] text-[#a4412f] border border-[#f3d2c6]',
    paid: 'bg-[#f5efe6] text-[#6c5b4e] border border-[#e5ded6]',
    packed: 'bg-[#f0ece7] text-[#4e453f] border border-[#dcd3cb]',
    shipped: 'bg-[#e9f1f5] text-[#2c5282] border border-[#cbe1ec]',
    completed: 'bg-[#eaf3e6] text-[#3d6532] border border-[#c9e4bf]',
    cancelled: 'bg-neutral-100 text-neutral-500 border border-neutral-200',
};

function statusLabel(status: string): string {
    const map: Record<string, string> = {
        draft: 'Draft',
        pending_payment: 'Menunggu Bayar',
        paid: 'Siap Kemas',
        packed: 'Dikemas',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    };
    return map[status] || status;
}

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dasbor Operasional — Lette Little" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 bg-[#fcf9f3] p-4 sm:p-6 lg:p-8 text-[#28231f]">
            <!-- Top Heading & Filter Tools -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold tracking-[0.18em] text-[#6c5b4e] uppercase">Operasional Butik · Lette Little</p>
                    <h1 class="font-serif text-3xl sm:text-4xl font-normal text-[#1c1c18] tracking-tight">Dasbor Operasional</h1>
                    <p class="mt-1 text-xs text-[#6f645d]">Ringkasan performa penjualan, pipeline pesanan, dan pemantauan stok bayi.</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 rounded-2xl border border-[#d1c4bc] bg-[#fffdf8] px-3.5 py-2 text-xs font-medium text-[#4e453f] shadow-xs">
                        <span class="text-[#6f645d]">Periode:</span>
                        <select v-model="selectedRange" class="bg-transparent font-semibold text-[#1c1c18] outline-none">
                            <option value="7">7 Hari Terakhir</option>
                            <option value="30">30 Hari Terakhir</option>
                        </select>
                    </label>
                </div>
            </div>

            <!-- Top 6 KPI Metric Cards (azzahra-store structure) -->
            <section class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <article
                    v-for="card in cards"
                    :key="card.label"
                    class="rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-4 shadow-xs transition hover:border-[#6c5b4e]/50"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[11px] font-medium tracking-[0.14em] text-[#6f645d] uppercase">{{ card.label }}</p>
                        <span class="grid size-8 shrink-0 place-items-center rounded-2xl" :class="card.tint">
                            <component :is="card.icon" class="size-4" />
                        </span>
                    </div>
                    <p class="mt-2.5 font-serif text-2xl font-normal text-[#1c1c18]">{{ card.value }}</p>
                    <p class="mt-1 text-[11px] text-[#6f645d]">
                        <span
                            :class="
                                card.tone === 'good'
                                    ? 'font-semibold text-emerald-700'
                                    : card.tone === 'bad'
                                      ? 'font-semibold text-[#a4412f]'
                                      : 'text-[#6f645d]'
                            "
                        >
                            {{ card.direction === 'up' ? '↑' : card.direction === 'down' ? '↓' : '•' }} {{ card.delta }}
                        </span>
                        {{ card.hint }}
                    </p>
                </article>
            </section>

            <!-- Order Pipeline / Tahap Pesanan -->
            <section class="rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4">
                    <div>
                        <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Tahap Pesanan</h2>
                        <p class="text-xs text-[#6f645d]">Alur pergerakan pesanan dari masuk hingga selesai.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                    <template v-for="(stage, index) in pipelineStages" :key="stage.key">
                        <Link
                            :href="stage.href"
                            class="group flex flex-1 items-center gap-3 rounded-2xl border border-[#e5ded6] bg-[#fcf9f3] p-3 transition hover:border-[#6c5b4e] hover:bg-[#fffdf8]"
                        >
                            <span class="grid size-10 shrink-0 place-items-center rounded-2xl" :class="stageTintMap[stage.tone] || 'bg-neutral-100 text-neutral-800'">
                                <component :is="stageIconMap[stage.key as keyof typeof stageIconMap] || Package" class="size-5" />
                            </span>
                            <div class="min-w-0">
                                <span class="block truncate text-[10px] font-semibold tracking-[0.14em] text-[#6f645d] uppercase group-hover:text-[#6c5b4e]">{{ stage.label }}</span>
                                <span class="font-serif text-2xl font-normal text-[#1c1c18]">{{ stage.count }}</span>
                            </div>
                        </Link>
                        <ArrowRight v-if="index < pipelineStages.length - 1" class="mx-auto hidden size-4 shrink-0 text-[#d1c4bc] xl:block" />
                    </template>

                    <!-- Conversion Metric Box -->
                    <div class="grid min-w-[130px] place-items-center rounded-2xl bg-[#6c5b4e] px-4 py-3 text-center text-white shadow-sm">
                        <p class="text-[10px] font-medium tracking-[0.14em] text-white/80 uppercase">Konversi Selesai</p>
                        <p class="font-serif text-2xl font-normal text-[#fffdf8]">{{ pipelineConversion }}%</p>
                    </div>
                </div>
            </section>

            <!-- 3-Column Mid Section: Perlu Perhatian, Dinamika Pesanan SVG, Status Pembayaran Donut -->
            <section class="grid gap-5 xl:grid-cols-[280px_minmax(0,1fr)_280px]">
                <!-- Perlu Perhatian -->
                <article class="flex flex-col rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Perlu Perhatian</h2>
                    <p class="text-xs text-[#6f645d]">Item mendesak untuk ditindaklanjuti.</p>

                    <div class="mt-4 flex flex-1 flex-col justify-center divide-y divide-[#f0ece7]">
                        <Link
                            :href="ordersIndex.url({ status: 'paid' })"
                            class="group flex items-center gap-3 py-3.5 transition hover:text-[#6c5b4e]"
                        >
                            <span class="grid size-9 shrink-0 place-items-center rounded-2xl bg-[#f5efe6] text-[#6c5b4e]">
                                <PackageCheck class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-[#1c1c18] group-hover:text-[#6c5b4e]">Siap Dikemas</span>
                                <span class="text-[11px] text-[#6f645d]">Pesanan lunas butuh packing</span>
                            </div>
                            <span class="rounded-full bg-[#f5efe6] px-2.5 py-0.5 text-xs font-bold text-[#6c5b4e]">{{ needsAttention.ready_to_pack }}</span>
                            <ChevronRight class="size-4 text-[#d1c4bc]" />
                        </Link>

                        <Link
                            :href="ordersIndex.url({ status: 'pending_payment' })"
                            class="group flex items-center gap-3 py-3.5 transition hover:text-[#a4412f]"
                        >
                            <span class="grid size-9 shrink-0 place-items-center rounded-2xl bg-[#fbeee8] text-[#a4412f]">
                                <Clock class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-[#1c1c18] group-hover:text-[#a4412f]">Menunggu Bayar</span>
                                <span class="text-[11px] text-[#6f645d]">Belum menyelesaikan transfer/snap</span>
                            </div>
                            <span class="rounded-full bg-[#fbeee8] px-2.5 py-0.5 text-xs font-bold text-[#a4412f]">{{ needsAttention.pending_payment }}</span>
                            <ChevronRight class="size-4 text-[#d1c4bc]" />
                        </Link>

                        <Link
                            :href="productsIndex.url()"
                            class="group flex items-center gap-3 py-3.5 transition hover:text-[#9a6a35]"
                        >
                            <span class="grid size-9 shrink-0 place-items-center rounded-2xl bg-[#fcf5ec] text-[#9a6a35]">
                                <AlertTriangle class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-[#1c1c18] group-hover:text-[#9a6a35]">Stok Menipis</span>
                                <span class="text-[11px] text-[#6f645d]">Sisa stok ≤ 3 pcs</span>
                            </div>
                            <span class="rounded-full bg-[#fcf5ec] px-2.5 py-0.5 text-xs font-bold text-[#9a6a35]">{{ needsAttention.low_stock }}</span>
                            <ChevronRight class="size-4 text-[#d1c4bc]" />
                        </Link>
                    </div>
                </article>

                <!-- Dinamika Pesanan (SVG Interactive Polyline Chart) -->
                <article class="flex flex-col rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Dinamika Pesanan</h2>
                            <p class="text-xs text-[#6f645d]">Grafik harian pesanan masuk, selesai, dan batal.</p>
                        </div>
                        <span class="rounded-full border border-[#e5ded6] bg-[#fcf9f3] px-2.5 py-1 text-[11px] font-medium text-[#6f645d]">{{ range }} hari</span>
                    </div>

                    <div class="mt-3 flex items-center gap-4 text-xs">
                        <span class="inline-flex items-center gap-1.5 font-medium text-[#1c1c18]">
                            <i class="size-2.5 rounded-full bg-[#1c1c18]"></i> Masuk
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-[#6c5b4e]">
                            <i class="size-2.5 rounded-full bg-[#6c5b4e]"></i> Selesai
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-[#a4412f]">
                            <i class="size-2.5 rounded-full bg-[#a4412f]"></i> Batal
                        </span>
                    </div>

                    <div class="mt-4 flex-1">
                        <svg class="h-44 w-full" :viewBox="`0 0 ${chart.width} ${chart.height}`" role="img" aria-label="Grafik pesanan harian">
                            <line
                                v-for="tick in chart.ticks"
                                :key="tick"
                                :x1="chart.pad.left"
                                :x2="chart.width - chart.pad.right"
                                :y1="chart.y(tick)"
                                :y2="chart.y(tick)"
                                stroke="#f0eee8"
                                stroke-dasharray="3 3"
                            />
                            <text
                                v-for="tick in chart.ticks"
                                :key="`tick-${tick}`"
                                :x="0"
                                :y="chart.y(tick) + 4"
                                fill="#a29890"
                                font-size="10"
                            >
                                {{ tick }}
                            </text>

                            <polyline
                                v-if="chart.rawSeries.length"
                                :points="chart.coords('masuk')"
                                fill="none"
                                stroke="#1c1c18"
                                stroke-width="2.5"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                            />
                            <polyline
                                v-if="chart.rawSeries.length"
                                :points="chart.coords('selesai')"
                                fill="none"
                                stroke="#6c5b4e"
                                stroke-width="2.5"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                            />
                            <polyline
                                v-if="chart.rawSeries.length"
                                :points="chart.coords('batal')"
                                fill="none"
                                stroke="#a4412f"
                                stroke-width="2"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                            />

                            <text
                                v-for="(point, index) in chart.rawSeries"
                                v-show="index % chart.labelEvery === 0"
                                :key="point.label"
                                :x="chart.pad.left + index * chart.step"
                                :y="chart.height - 4"
                                :text-anchor="index === chart.rawSeries.length - 1 ? 'end' : 'middle'"
                                fill="#6f645d"
                                font-size="10"
                            >
                                {{ point.label }}
                            </text>
                        </svg>
                    </div>
                </article>

                <!-- Status Pembayaran (Donut Chart) -->
                <article class="flex flex-col rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Status Pembayaran</h2>
                    <p class="text-xs text-[#6f645d]">Distribusi pelunasan transaksi.</p>

                    <div class="mt-4 flex flex-1 flex-col items-center justify-center gap-4">
                        <div class="relative grid size-32 place-items-center rounded-full" :style="{ background: donut }">
                            <div class="grid size-22 place-items-center rounded-full bg-[#fffdf8] text-center shadow-inner">
                                <div>
                                    <p class="font-serif text-2xl font-bold text-[#1c1c18]">{{ distribution?.total ?? stats.orders }}</p>
                                    <p class="text-[9px] font-semibold tracking-wider text-[#6f645d] uppercase">Transaksi</p>
                                </div>
                            </div>
                        </div>

                        <ul class="w-full space-y-2 text-xs">
                            <li
                                v-for="slice in (distribution?.slices ?? [])"
                                :key="slice.label"
                                class="flex items-center justify-between rounded-xl bg-[#fcf9f3] px-3 py-1.5"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full" :style="{ background: slice.color }"></span>
                                    <span class="text-[#28231f]">{{ slice.label }}</span>
                                </div>
                                <span class="font-semibold text-[#1c1c18]">{{ slice.value }} ({{ slice.percent }}%)</span>
                            </li>
                        </ul>
                    </div>
                </article>
            </section>

            <!-- 3-Column Lower Section: Umur Pesanan, Produk Terlaris, Kategori -->
            <section class="grid gap-5 xl:grid-cols-3">
                <!-- Umur Pesanan Terbuka -->
                <article class="rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Umur Pesanan Terbuka</h2>
                    <p class="text-xs text-[#6f645d]">Durasi pesanan aktif belum terselesaikan.</p>

                    <ul class="mt-4 space-y-3.5">
                        <li v-for="bar in (ages ?? [])" :key="bar.label">
                            <div class="mb-1 flex justify-between text-xs">
                                <span class="font-medium text-[#4e453f]">{{ bar.label }}</span>
                                <span class="text-[#6f645d]">{{ bar.value }} ({{ bar.percent }}%)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#f0eee8]">
                                <div class="h-full rounded-full transition-all duration-500" :style="{ width: `${bar.percent}%`, background: bar.color || '#6c5b4e' }"></div>
                            </div>
                        </li>
                    </ul>
                </article>

                <!-- Produk Terlaris -->
                <article class="rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Produk Terlaris</h2>
                            <p class="text-xs text-[#6f645d]">Volume penjualan tertinggi periode ini.</p>
                        </div>
                        <Link :href="productsIndex.url()" class="text-[11px] font-semibold tracking-wider text-[#6c5b4e] uppercase hover:underline">Semua</Link>
                    </div>

                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-[#e5ded6] text-[10px] font-semibold tracking-wider text-[#6f645d] uppercase">
                                <tr>
                                    <th class="py-2">Produk</th>
                                    <th class="py-2">Terjual</th>
                                    <th class="py-2">Stok</th>
                                    <th class="py-2 text-right">Porsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0ece7]">
                                <tr v-for="p in (topProducts ?? [])" :key="p.name">
                                    <td class="max-w-[120px] truncate py-2 font-medium text-[#1c1c18]">{{ p.name }}</td>
                                    <td class="py-2 text-[#4e453f]">{{ p.sold }} pcs</td>
                                    <td class="py-2" :class="p.stock <= 3 ? 'font-bold text-[#a4412f]' : 'text-[#6f645d]'">{{ p.stock }}</td>
                                    <td class="py-2 text-right">
                                        <span class="inline-block rounded-full bg-[#f5efe6] px-2 py-0.5 text-[10px] font-semibold text-[#6c5b4e]">{{ p.share }}%</span>
                                    </td>
                                </tr>
                                <tr v-if="!topProducts?.length">
                                    <td colspan="4" class="py-6 text-center text-xs text-[#a29890]">Belum ada data penjualan pada periode ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <!-- Pesanan per Kategori -->
                <article class="rounded-3xl border border-[#e5ded6] bg-[#fffdf8] p-5 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Pesanan per Kategori</h2>
                            <p class="text-xs text-[#6f645d]">Proporsi produk yang diminati pembeli.</p>
                        </div>
                    </div>

                    <ul class="mt-4 space-y-3.5">
                        <li v-for="cat in (categories ?? [])" :key="cat.label">
                            <div class="mb-1 flex justify-between text-xs">
                                <span class="font-medium text-[#4e453f]">{{ cat.label }}</span>
                                <span class="text-[#6f645d]">{{ cat.value }} pcs ({{ cat.percent }}%)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#f0eee8]">
                                <div class="h-full rounded-full bg-[#6c5b4e] transition-all duration-500" :style="{ width: `${cat.percent}%` }"></div>
                            </div>
                        </li>
                        <li v-if="!categories?.length" class="py-6 text-center text-xs text-[#a29890]">Belum ada item pesanan pada periode ini.</li>
                    </ul>
                </article>
            </section>

            <!-- Pesanan Terbaru Table (Recent Orders) -->
            <section class="overflow-hidden rounded-3xl border border-[#e5ded6] bg-[#fffdf8] shadow-xs">
                <div class="flex items-center justify-between border-b border-[#e5ded6] p-5">
                    <div>
                        <h2 class="font-serif text-xl font-normal text-[#1c1c18]">Pesanan Terbaru</h2>
                        <p class="text-xs text-[#6f645d]">Daftar 6 transaksi paling akhir yang masuk ke sistem.</p>
                    </div>
                    <Link :href="ordersIndex.url()" class="rounded-xl border border-[#d1c4bc] bg-[#fcf9f3] px-3.5 py-1.5 text-xs font-semibold text-[#6c5b4e] transition hover:bg-[#6c5b4e] hover:text-white">
                        Lihat Semua Pesanan
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-xs">
                        <thead class="border-b border-[#e5ded6] bg-[#fcf9f3] text-[10px] font-semibold tracking-wider text-[#6f645d] uppercase">
                            <tr>
                                <th class="px-5 py-3">No. Pesanan</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Pembayaran</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-5 py-3 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0ece7]">
                            <tr v-for="order in recentOrders" :key="order.order_number" class="transition hover:bg-[#fcf9f3]">
                                <td class="px-5 py-3.5 font-semibold">
                                    <Link :href="orderShow.url(order.order_number)" class="text-[#6c5b4e] hover:underline">
                                        {{ order.order_number }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-[#1c1c18]">{{ order.customer_name }}</div>
                                    <div class="text-[11px] text-[#6f645d]">{{ order.customer_email }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium" :class="statusBadgeClasses[order.status] || 'bg-neutral-100 text-neutral-600'">
                                        {{ order.status_label || statusLabel(order.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-[#4e453f]">
                                    <span class="capitalize">{{ order.payment_provider || 'Midtrans' }}</span>
                                    <span v-if="order.payment_status" class="ml-1 text-[10px] text-[#6f645d]">({{ order.payment_status }})</span>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-[#1c1c18]">{{ formatMoney(order.total) }}</td>
                                <td class="px-5 py-3.5 text-right text-[#6f645d]">{{ order.updated_at || order.created_at?.slice(0, 10) }}</td>
                            </tr>
                            <tr v-if="!recentOrders.length">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-[#a29890]">Belum ada riwayat pesanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Peringatan Stok Menipis -->
            <section v-if="lowStockVariants.length" class="rounded-3xl border border-[#f3d2c6] bg-[#fffdf8] p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3">
                    <div class="flex items-center gap-2">
                        <span class="grid size-8 place-items-center rounded-2xl bg-[#fbeee8] text-[#a4412f]">
                            <AlertTriangle class="size-4" />
                        </span>
                        <div>
                            <h2 class="font-serif text-lg font-normal text-[#1c1c18]">Peringatan Stok Menipis</h2>
                            <p class="text-xs text-[#6f645d]">Segera restock varian berikut agar pesanan storefront tidak tertolak.</p>
                        </div>
                    </div>
                    <Link :href="productsIndex.url()" class="text-xs font-semibold text-[#6c5b4e] hover:underline">Kelola Produk</Link>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <div
                        v-for="v in lowStockVariants"
                        :key="v.sku"
                        class="rounded-2xl border border-[#f3d2c6] bg-[#fdf9f7] p-3 text-xs"
                    >
                        <p class="font-semibold text-[#1c1c18] truncate">{{ v.product_name }}</p>
                        <p class="text-[11px] text-[#6f645d]">{{ v.sku }}</p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="rounded bg-white px-1.5 py-0.5 text-[10px] text-[#4e453f] border border-[#e5ded6]">{{ v.size }} · {{ v.color }}</span>
                            <span class="font-bold text-[#a4412f]">Sisa: {{ v.stock }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
