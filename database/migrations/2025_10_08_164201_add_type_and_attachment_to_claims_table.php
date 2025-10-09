<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('type_of_claim')->nullable()->after('user_id');
            $table->string('attached_document')->nullable()->after('type_of_claim');
        });
    }

    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['type_of_claim', 'attached_document']);
        });
    }
};
