<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin operations dashboard.
     */
    public function index(Request $request): Response
    {
        $range = $request->integer('range') === 30 ? 30 : 7;
        $end = now();
        $start = now()->copy()->subDays($range - 1)->startOfDay();
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $start->copy()->subDays($range);

        $currentOrders = Order::query()->whereBetween('created_at', [$start, $end])->with(['payment', 'items.variant.product.category'])->get();
        $previousOrders = Order::query()->whereBetween('created_at', [$previousStart, $previousEnd])->with(['payment'])->get();
        $openOrders = Order::query()
            ->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])
            ->get();

        $needsAttention = $this->needsAttention();
        $distribution = $this->paymentSplit($currentOrders);
        $categories = $this->categoryShares($currentOrders);
        $topProducts = $this->topProducts($currentOrders);

        return Inertia::render('Dashboard', [
            'range' => $range,
            'stats' => $this->stats(),
            'kpiStats' => [
                'orders' => $this->stat(
                    'Semua pesanan',
                    $this->formatCount($currentOrders->count()),
                    $this->change($currentOrders->count(), $previousOrders->count()),
                    'vs '.$range.' hari sebelumnya',
                ),
                'inProgress' => $this->stat(
                    'Dalam proses',
                    $this->formatCount($this->inProgress($currentOrders)),
                    $this->change($this->inProgress($currentOrders), $this->inProgress($previousOrders)),
                    'dibayar atau sedang dikemas',
                ),
                'overdue' => $this->stat(
                    'Menunggu bayar',
                    $this->formatCount($needsAttention['pending_payment']),
                    $this->change($needsAttention['pending_payment'], $this->overdueCount($previousOrders), true),
                    'belum menyelesaikan pembayaran',
                ),
                'paidRate' => $this->stat(
                    'Tingkat lunas',
                    $this->paidRate($currentOrders).'%',
                    $this->change($this->paidRate($currentOrders), $this->paidRate($previousOrders)),
                    'dari pesanan periode ini',
                ),
                'leadTime' => $this->stat(
                    'Rata-rata proses',
                    $this->leadTimeLabel($currentOrders),
                    $this->leadTimeChange($currentOrders, $previousOrders),
                    'dari order sampai dikirim',
                ),
                'finishedToday' => $this->stat(
                    'Selesai hari ini',
                    $this->formatCount($this->finishedOn(now())),
                    $this->change($this->finishedOn(now()), $this->finishedOn(now()->subDay())),
                    'vs kemarin',
                ),
            ],
            'pipeline' => $this->pipeline(),
            'series' => $this->series($start, $range, $currentOrders),
            'distribution' => $distribution,
            'ages' => $this->ages($openOrders),
            'categories' => $categories,
            'topProducts' => $topProducts,
            'needsAttention' => $needsAttention,
            'recentOrders' => $this->recentOrders(),
            'lowStockVariants' => $this->lowStockVariants(),
        ]);
    }

    /**
     * @return array{orders: int, pending_payment: int, ready_to_pack: int, in_fulfillment: int, completed: int, paid_revenue: int}
     */
    private function stats(): array
    {
        return [
            'orders' => Order::query()->count(),
            'pending_payment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'ready_to_pack' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'in_fulfillment' => Order::query()->whereIn('status', [
                OrderStatus::Packed,
                OrderStatus::Shipped,
            ])->count(),
            'completed' => Order::query()->where('status', OrderStatus::Completed)->count(),
            'paid_revenue' => (int) Payment::query()->where('status', PaymentStatus::Completed)->sum('amount'),
        ];
    }

    /**
     * @return array{ready_to_pack: int, pending_payment: int, low_stock: int}
     */
    private function needsAttention(): array
    {
        return [
            'ready_to_pack' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'pending_payment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'low_stock' => ProductVariant::query()->where('is_active', true)->where('stock', '<=', 3)->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentOrders(): array
    {
        return Order::query()
            ->with(['customer:id,name,email', 'payment:id,order_id,status,amount,provider'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->name ?? 'Guest',
                'customer_email' => $order->customer?->email ?? '-',
                'status' => $order->status->value,
                'status_label' => $this->statusLabel($order->status),
                'payment_provider' => $order->payment?->provider ?? 'Midtrans',
                'payment_status' => $order->payment?->status->value,
                'total' => $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm') ?? '-',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lowStockVariants(): array
    {
        return ProductVariant::query()
            ->where('is_active', true)
            ->with('product:id,name')
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->limit(6)
            ->get(['id', 'product_id', 'sku', 'size', 'color', 'stock'])
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'product_name' => $variant->product?->name ?? 'Produk',
                'sku' => $variant->sku,
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => $variant->stock,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{label: string, value: string, direction: string, delta: string, tone: string, hint: string}
     */
    private function stat(string $label, string $value, array $change, string $hint): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'direction' => $change['direction'],
            'delta' => $change['label'],
            'tone' => $change['tone'],
            'hint' => $hint,
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function inProgress(Collection $orders): int
    {
        return $orders->whereIn('status', [OrderStatus::Paid, OrderStatus::Packed, OrderStatus::Shipped])->count();
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function overdueCount(Collection $orders): int
    {
        return $orders->where('status', OrderStatus::PendingPayment)->count();
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function paidRate(Collection $orders): int
    {
        $eligible = $orders->where('status', '!=', OrderStatus::Cancelled);

        if ($eligible->isEmpty()) {
            return 0;
        }

        $paidCount = $eligible->filter(fn (Order $o) => $o->payment?->status === PaymentStatus::Completed)->count();

        return (int) round($paidCount / $eligible->count() * 100);
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function leadTimeLabel(Collection $orders): string
    {
        $days = $this->leadTimeDays($orders);

        return $days === null ? '—' : number_format($days, 1, ',', '.').' hari';
    }

    /**
     * @param  Collection<int, Order>  $current
     * @param  Collection<int, Order>  $previous
     * @return array{direction: string, label: string, tone: string}
     */
    private function leadTimeChange(Collection $current, Collection $previous): array
    {
        $now = $this->leadTimeDays($current);
        $before = $this->leadTimeDays($previous);

        if ($now === null || $before === null) {
            return ['direction' => 'flat', 'label' => '0', 'tone' => 'neutral'];
        }

        $diff = round($now - $before, 1);

        return [
            'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
            'label' => ($diff > 0 ? '+' : '').number_format($diff, 1, ',', '.').' hari',
            'tone' => $diff < 0 ? 'good' : ($diff > 0 ? 'bad' : 'neutral'),
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    private function leadTimeDays(Collection $orders): ?float
    {
        $samples = $orders
            ->whereIn('status', [OrderStatus::Shipped, OrderStatus::Completed])
            ->map(function (Order $order) {
                if (! $order->created_at || ! $order->updated_at) {
                    return null;
                }

                return $order->created_at->diffInMinutes($order->updated_at) / 1440;
            })
            ->filter(fn ($days) => $days !== null);

        if ($samples->isEmpty()) {
            return null;
        }

        return round($samples->avg(), 1);
    }

    private function finishedOn(CarbonInterface $day): int
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereDate('updated_at', $day->toDateString())
            ->count();
    }

    /**
     * @return array{direction: string, label: string, tone: string}
     */
    private function change(int $current, int $previous, bool $lowerIsBetter = false): array
    {
        if ($previous === 0 && $current === 0) {
            return ['direction' => 'flat', 'label' => '0%', 'tone' => 'neutral'];
        }

        if ($previous === 0) {
            return [
                'direction' => 'up',
                'label' => 'baru',
                'tone' => $lowerIsBetter ? 'bad' : 'good',
            ];
        }

        $diff = $current - $previous;
        $percent = (int) round(($diff / $previous) * 100);
        $direction = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat');
        $improved = $lowerIsBetter ? $diff < 0 : $diff > 0;

        return [
            'direction' => $direction,
            'label' => ($percent > 0 ? '+' : '').$percent.'%',
            'tone' => $diff === 0 ? 'neutral' : ($improved ? 'good' : 'bad'),
        ];
    }

    /**
     * @return array{stages: list<array{key: string, label: string, count: int, href: string, tone: string}>, conversion: int}
     */
    private function pipeline(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stages = [
            OrderStatus::PendingPayment->value => ['Menunggu bayar', 'amber'],
            OrderStatus::Paid->value => ['Siap kemas', 'sky'],
            OrderStatus::Packed->value => ['Dikemas', 'violet'],
            OrderStatus::Shipped->value => ['Dikirim', 'blue'],
            OrderStatus::Completed->value => ['Selesai', 'green'],
        ];

        $flow = collect($stages)->map(fn (array $meta, string $status) => [
            'key' => $status,
            'label' => $meta[0],
            'count' => (int) ($counts[$status] ?? 0),
            'href' => route('admin.orders.index', ['status' => $status]),
            'tone' => $meta[1],
        ])->values()->all();

        $base = array_sum(array_column($flow, 'count'));
        $completedCount = (int) ($counts[OrderStatus::Completed->value] ?? 0);

        return [
            'stages' => $flow,
            'conversion' => $base === 0 ? 0 : (int) round($completedCount / $base * 100),
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{label: string, masuk: int, selesai: int, batal: int}>
     */
    private function series(CarbonInterface $start, int $range, Collection $orders): array
    {
        $points = [];

        for ($offset = 0; $offset < $range; $offset++) {
            $day = $start->copy()->addDays($offset);
            $points[] = [
                'label' => $day->locale('id')->isoFormat('D MMM'),
                'masuk' => $orders->filter(fn (Order $order) => $order->created_at?->isSameDay($day))->count(),
                'selesai' => $orders->filter(fn (Order $order) => $order->status === OrderStatus::Completed && $order->updated_at?->isSameDay($day))->count(),
                'batal' => $orders->filter(fn (Order $order) => $order->status === OrderStatus::Cancelled && $order->updated_at?->isSameDay($day))->count(),
            ];
        }

        return $points;
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array{total: int, slices: list<array{label: string, value: int, percent: int, color: string}>}
     */
    private function paymentSplit(Collection $orders): array
    {
        $open = $orders->where('status', '!=', OrderStatus::Cancelled);
        $paid = $open->filter(fn (Order $o) => $o->payment?->status === PaymentStatus::Completed)->count();
        $unpaid = $open->count() - $paid;
        $total = $paid + $unpaid;

        return [
            'total' => $total,
            'slices' => [
                ['label' => 'Lunas', 'value' => $paid, 'percent' => $total === 0 ? 0 : (int) round($paid / $total * 100), 'color' => '#6c5b4e'],
                ['label' => 'Belum Lunas', 'value' => $unpaid, 'percent' => $total === 0 ? 0 : (int) round($unpaid / $total * 100), 'color' => '#d1c4bc'],
            ],
        ];
    }

    /**
     * @param  Collection<int, Order>  $open
     * @return list<array{label: string, value: int, percent: int, color: string}>
     */
    private function ages(Collection $open): array
    {
        $buckets = [
            ['label' => 'Di bawah 24 jam', 'value' => 0, 'color' => '#8f7b6d'],
            ['label' => '1–3 hari', 'value' => 0, 'color' => '#6c5b4e'],
            ['label' => '3–7 hari', 'value' => 0, 'color' => '#4a3d34'],
            ['label' => 'Lebih dari 7 hari', 'value' => 0, 'color' => '#2b231e'],
        ];

        foreach ($open as $order) {
            $days = $order->created_at ? $order->created_at->diffInHours(now()) / 24 : 0;

            $index = match (true) {
                $days < 1 => 0,
                $days < 3 => 1,
                $days < 7 => 2,
                default => 3,
            };
            $buckets[$index]['value']++;
        }

        $total = max(1, array_sum(array_column($buckets, 'value')));

        return array_map(fn (array $bucket) => [
            ...$bucket,
            'percent' => (int) round($bucket['value'] / $total * 100),
        ], $buckets);
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{label: string, value: int, percent: int}>
     */
    private function categoryShares(Collection $orders): array
    {
        if ($orders->isEmpty()) {
            return [];
        }

        $items = OrderItem::query()
            ->with('variant.product.category')
            ->whereIn('order_id', $orders->modelKeys())
            ->get();

        $grouped = $items
            ->groupBy(fn (OrderItem $item) => $item->variant?->product?->category?->name ?? 'Lainnya')
            ->map(fn (Collection $group) => (int) $group->sum('quantity'))
            ->sortDesc();

        $total = max(1, $grouped->sum());

        return $grouped->take(4)->map(fn (int $value, string $label) => [
            'label' => $label,
            'value' => $value,
            'percent' => (int) round($value / $total * 100),
        ])->values()->all();
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{name: string, sold: int, stock: int, share: int}>
     */
    private function topProducts(Collection $orders): array
    {
        if ($orders->isEmpty()) {
            return [];
        }

        $items = OrderItem::query()
            ->with('variant')
            ->whereIn('order_id', $orders->modelKeys())
            ->get();

        $grouped = $items->groupBy(fn (OrderItem $item) => $item->product_name);
        $soldTotal = max(1, (int) $items->sum('quantity'));

        return $grouped
            ->map(function (Collection $group, string $name) use ($soldTotal) {
                $sold = (int) $group->sum('quantity');
                $first = $group->first();
                $stock = $first?->variant?->stock ?? 0;

                return [
                    'name' => $name,
                    'sold' => $sold,
                    'stock' => $stock,
                    'share' => (int) round($sold / $soldTotal * 100),
                ];
            })
            ->sortByDesc('sold')
            ->take(5)
            ->values()
            ->all();
    }

    private function statusLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Draft => 'Draft',
            OrderStatus::PendingPayment => 'Menunggu Bayar',
            OrderStatus::Paid => 'Siap Kemas',
            OrderStatus::Packed => 'Dikemas',
            OrderStatus::Shipped => 'Dikirim',
            OrderStatus::Completed => 'Selesai',
            OrderStatus::Cancelled => 'Dibatalkan',
        };
    }

    private function formatCount(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
