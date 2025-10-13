<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivablesTable extends Migration
{
    public function up()
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();           // e.g. AR-001
            $table->string('customer')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['Unpaid','Paid','Overdue'])->default('Unpaid');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receivables');
    }
}