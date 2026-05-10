<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::create('pembelians', function (Blueprint $table) {
        $table->id();
        $table->string('no_pembelian')->unique();
        $table->string('nama_supplier');
        $table->foreignId('user_id')->constrained('users'); 
        $table->decimal('total_biaya', 15, 2);
        $table->date('tanggal');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
