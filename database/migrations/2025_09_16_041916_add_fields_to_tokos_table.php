<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk menambah kolom ke tabel tokos
     */
    public function up(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('alamat'); // Deskripsi toko
            $table->string('kategori_usaha')->nullable()->after('deskripsi'); // Kategori usaha
            $table->string('no_telepon')->nullable()->after('kategori_usaha'); // No telepon toko
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('tokos', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'kategori_usaha', 'no_telepon']);
        });
    }
};