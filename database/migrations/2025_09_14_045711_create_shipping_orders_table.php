<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - untuk membuat tabel shipping orders
     */
    public function up(): void
    {
        Schema::create('shipping_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->onDelete('cascade'); // ID transaksi
            $table->string('tracking_number')->unique(); // Nomor resi pengiriman
            $table->enum('status', ['pending', 'shipped', 'delivered', 'cancelled'])->default('pending'); // Status pengiriman
            $table->string('courier')->nullable(); // Jasa kurir
            $table->date('shipped_date')->nullable(); // Tanggal kirim
            $table->date('delivered_date')->nullable(); // Tanggal sampai
            $table->text('notes')->nullable(); // Catatan pengiriman
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_orders');
    }
};