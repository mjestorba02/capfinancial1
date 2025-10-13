<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'customer_name',
        'invoice_number',
        'amount_due',
        'amount_paid',
        'status',
        'payment_date',
        'remarks',
    ];
}