<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'account',
        'type',
        'credit',
        'debit',
        'description',
        'entry_date',
        'source_module',
        'reference_id',
    ];
}