<x-mail::message>
# We received your order

Hi {{ $order->customer->name }},

Order **{{ $order->order_number }}** is reserved. Pay the remaining amount if you have not already, and we will prepare it next.

<x-mail::button :url="$orderUrl">
View order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
