<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_request_id')->nullable()->index();
            $table->string('department');
            $table->string('project');
            $table->decimal('allocated', 15, 2);
            $table->decimal('used', 15, 2)->default(0);
            $table->timestamps();

            // optional foreign key (if you keep budget_requests table)
            // $table->foreign('budget_request_id')->references('id')->on('budget_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};