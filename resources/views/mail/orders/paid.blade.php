<x-mail::message>
# Payment received

Hi {{ $order->customer->name }},

We have received payment for **{{ $order->order_number }}**. The order is now being prepared.

<x-mail::button :url="$orderUrl">
View order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
