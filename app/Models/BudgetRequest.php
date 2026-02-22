<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'employee_id',
        'name',
        'department',
        'purpose',
        'amount',
        'status',
        'remarks',
        'details',          // employee-provided details
        'image_path',
        'attachment_path',  // PDF or image uploaded by employee
        'hr_approved_at',
        'admin_approved_at',
    ];

    protected $casts = [
        'hr_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
    ];

    // 🔹 Add relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function budgetOrders()
    {
        return $this->hasMany(BudgetOrder::class);
    }
}