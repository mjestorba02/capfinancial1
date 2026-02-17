<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_requests', function (Blueprint $table) {
            $table->text('details')->nullable()->after('remarks');
            $table->string('attachment_path')->nullable()->after('image_path');
            $table->timestamp('hr_approved_at')->nullable()->after('attachment_path');
            $table->timestamp('admin_approved_at')->nullable()->after('hr_approved_at');
        });

        // Add 'Pending Admin' to status enum (MySQL)
        DB::statement("ALTER TABLE budget_requests MODIFY COLUMN status ENUM('Pending', 'Pending Admin', 'Approved', 'Rejected') DEFAULT 'Pending'");
    }

    public function down(): void
    {
        Schema::table('budget_requests', function (Blueprint $table) {
            $table->dropColumn(['details', 'attachment_path', 'hr_approved_at', 'admin_approved_at']);
        });
        DB::statement("ALTER TABLE budget_requests MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");
    }
};
