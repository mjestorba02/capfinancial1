<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Claim extends Model
{
    protected $fillable = [
        'claim_id',
        'user_id',
        'type_of_claim',
        'attached_document',
        'claim_date',
        'claim_amount',
        'reimbursement_amount',
        'reimbursement_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}