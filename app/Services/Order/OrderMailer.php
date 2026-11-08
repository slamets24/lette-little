<?php

namespace App\Services\Order;

use App\Mail\OrderPaidMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderMailer
{
    public function placed(Order $order): void
    {
        $this->send($order, new OrderPlacedMail($order));
    }

    public function paid(Order $order): void
    {
        $this->send($order, new OrderPaidMail($order));
    }

    public function shipped(Order $order): void
    {
        $this->send($order, new OrderShippedMail($order));
    }

    private function send(Order $order, OrderPlacedMail|OrderPaidMail|OrderShippedMail $mail): void
    {
        $order->loadMissing('customer');

        Mail::to($order->customer->email)->send($mail);
    }
}
