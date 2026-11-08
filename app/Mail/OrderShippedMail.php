<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class OrderShippedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->order->order_number} is on its way",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.shipped',
            with: [
                'orderUrl' => URL::temporarySignedRoute('orders.show', now()->addDays(14), [
                    'order' => $this->order,
                ]),
            ],
        );
    }
}
