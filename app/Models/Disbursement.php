<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'vendor',
        'category',
        'amount',
        'status',
        'disbursement_date',
        'external_id',
        'remarks',
    ];

    protected $dates = [
        'disbursement_date',
        'created_at',
        'updated_at',
    ];
}