<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'actor_name',
        'actor_email',
        'action',
        'description',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
    ];
}

