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
    Schema::create('detail_pembelians', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
        $table->foreignId('barang_id')->constrained('barangs');
        $table->decimal('harga_beli', 15, 2);
        $table->integer('kuantitas');
        $table->decimal('subtotal', 15, 2);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelians');
    }
};
