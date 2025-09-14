<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - menambah kolom harga ke tabel kategoris
     */
    public function up(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            $table->decimal('harga', 10, 2)->nullable()->after('deskripsi'); // Harga kategori produk
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }
};