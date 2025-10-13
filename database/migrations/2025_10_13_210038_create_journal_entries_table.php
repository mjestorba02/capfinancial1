<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->string('type'); // e.g., 'Debit' or 'Credit'
            $table->decimal('credit', 10, 2)->default(0);
            $table->decimal('debit', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->date('entry_date');
            $table->string('source_module')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};