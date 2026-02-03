<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'employee_id',  // 🔹 new: links request to employee
        'name',
        'department',
        'purpose',
        'amount',
        'status',
        'remarks',      // 🔹 new: optional message from employee/admin
        'image_path',   // 🔹 new: image file path
    ];

    // 🔹 Add relationship to Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}