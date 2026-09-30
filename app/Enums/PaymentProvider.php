<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Midtrans = 'midtrans';
    case BankTransfer = 'bank_transfer';
}
