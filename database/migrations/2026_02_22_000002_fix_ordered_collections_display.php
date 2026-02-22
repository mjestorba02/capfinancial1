<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix existing "Ordered" (budget order) collections:
     * - Remove " (Budget Order)" from customer_name
     * - Set amount_paid = amount_due
     * - Set payment_date = created_at (transaction date)
     */
    public function up(): void
    {
        if (!Schema::hasTable('collections')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                UPDATE collections
                SET customer_name = TRIM(REPLACE(COALESCE(customer_name, ''), ' (Budget Order)', '')),
                    amount_paid = amount_due,
                    payment_date = DATE(created_at)
                WHERE status = 'Ordered'
                  AND (employee_id IS NOT NULL OR budget_request_id IS NOT NULL)
            ");
        } else {
            DB::table('collections')
                ->where('status', 'Ordered')
                ->where(function ($q) {
                    $q->whereNotNull('employee_id')->orWhereNotNull('budget_request_id');
                })
                ->get()
                ->each(function ($row) {
                    $cleanName = trim(str_replace(' (Budget Order)', '', $row->customer_name ?? ''));
                    DB::table('collections')->where('id', $row->id)->update([
                        'customer_name' => $cleanName ?: $row->customer_name,
                        'amount_paid' => $row->amount_due,
                        'payment_date' => $row->created_at,
                    ]);
                });
        }
    }

    /**
     * Reverse the migration (we cannot restore original values reliably).
     */
    public function down(): void
    {
        // No-op: original data not stored
    }
};
