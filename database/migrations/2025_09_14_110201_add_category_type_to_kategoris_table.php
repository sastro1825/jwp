<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - menambah field category_type untuk jenis kategori kesehatan
     */
    public function up(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            $table->enum('category_type', [
                'obat-obatan',
                'alat-kesehatan', 
                'suplemen-kesehatan',
                'kesehatan-pribadi',
                'perawatan-kecantikan',
                'gizi-nutrisi',
                'kesehatan-lingkungan'
            ])->default('alat-kesehatan')->after('harga'); // Field untuk jenis kategori kesehatan
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};