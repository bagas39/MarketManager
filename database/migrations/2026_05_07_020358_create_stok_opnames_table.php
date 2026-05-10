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
    Schema::create('stok_opnames', function (Blueprint $table) {
        $table->id();
        $table->foreignId('barang_id')->constrained('barangs');
        $table->foreignId('user_id')->constrained('users');
        $table->integer('stok_sistem');
        $table->integer('stok_fisik');
        $table->integer('selisih');
        $table->string('keterangan')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_opnames');
    }
};
