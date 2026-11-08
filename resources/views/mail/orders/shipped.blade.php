<x-mail::message>
# Your order is on its way

Hi {{ $order->customer->name }},

**{{ $order->order_number }}** has been shipped with **{{ $order->courier }}**. Tracking number: **{{ $order->tracking_number }}**.

<x-mail::button :url="$orderUrl">
View order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
