<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Allow collections to link to budget request and support status "Ordered"
        if (Schema::hasTable('collections')) {
            if (!Schema::hasColumn('collections', 'budget_request_id')) {
                Schema::table('collections', function (Blueprint $table) {
                    $table->unsignedBigInteger('budget_request_id')->nullable()->after('id');
                });
            }
            if (!Schema::hasColumn('collections', 'employee_id')) {
                Schema::table('collections', function (Blueprint $table) {
                    $table->unsignedBigInteger('employee_id')->nullable()->after('budget_request_id');
                });
            }
            // Add 'Ordered' to status (MySQL enum)
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE collections MODIFY COLUMN status ENUM('Pending','Paid','Overdue','Ordered') DEFAULT 'Pending'");
            }
        }

        Schema::create('budget_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_request_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('material_description');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_number')->unique();
            $table->string('receipt_path')->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('budget_request_id')->references('id')->on('budget_requests')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_orders');
        if (Schema::hasTable('collections')) {
            if (Schema::hasColumn('collections', 'budget_request_id')) {
                Schema::table('collections', function (Blueprint $table) {
                    $table->dropColumn('budget_request_id');
                });
            }
            if (Schema::hasColumn('collections', 'employee_id')) {
                Schema::table('collections', function (Blueprint $table) {
                    $table->dropColumn('employee_id');
                });
            }
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE collections MODIFY COLUMN status ENUM('Pending','Paid','Overdue') DEFAULT 'Pending'");
            }
        }
    }
};
