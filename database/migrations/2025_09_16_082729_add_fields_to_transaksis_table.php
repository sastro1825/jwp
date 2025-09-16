<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->text('alamat_pengiriman')->nullable()->after('status');
            $table->text('catatan')->nullable()->after('alamat_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['alamat_pengiriman', 'catatan']);
        });
    }
};