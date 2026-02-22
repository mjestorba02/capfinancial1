<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_request_id',
        'employee_id',
        'material_description',
        'amount',
        'receipt_number',
        'receipt_path',
        'collection_id',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function budgetRequest()
    {
        return $this->belongsTo(BudgetRequest::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
