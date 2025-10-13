<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'vendor',
        'invoice_number',
        'amount',
        'mode_of_payment',
        'due_date',
        'payment_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];
}