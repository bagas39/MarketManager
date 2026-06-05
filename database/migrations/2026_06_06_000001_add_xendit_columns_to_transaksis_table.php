<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->enum('payment_method', ['tunai', 'qris'])->default('tunai')->after('tanggal');
            $table->string('xendit_qr_id')->nullable()->after('payment_method');
            $table->enum('status', ['pending', 'paid'])->default('paid')->after('xendit_qr_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'xendit_qr_id', 'status']);
        });
    }
};
