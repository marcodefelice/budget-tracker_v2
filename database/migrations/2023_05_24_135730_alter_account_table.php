<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('date_end')->nullable();
            $table->string('type')->default('bank');
            $table->boolean('installment')->default(0);
            $table->float('bank_credit')->default(0);
        });
    }
};
