<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - untuk update tabel keranjang agar support kategori
     */
    public function up(): void
    {
        Schema::table('keranjangs', function (Blueprint $table) {
            // Tambah kolom untuk support kategori
            $table->foreignId('kategori_id')->nullable()->after('produk_id')->constrained('kategoris')->onDelete('cascade');
            
            // Kolom untuk menyimpan data dari kategori (jika buy dari kategori)
            $table->string('nama_item')->nullable()->after('kategori_id'); // Nama item dari kategori atau produk
            $table->decimal('harga_item', 10, 2)->nullable()->after('nama_item'); // Harga item dari kategori atau produk
            $table->text('deskripsi_item')->nullable()->after('harga_item'); // Deskripsi item
            
            // Kolom untuk menentukan jenis item
            $table->enum('item_type', ['produk', 'kategori'])->default('produk')->after('deskripsi_item');
            
            // Ubah produk_id menjadi nullable karena sekarang bisa dari kategori
            $table->foreignId('produk_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations - untuk rollback perubahan
     */
    public function down(): void
    {
        Schema::table('keranjangs', function (Blueprint $table) {
            // Drop kolom yang ditambahkan
            $table->dropForeign(['kategori_id']);
            $table->dropColumn(['kategori_id', 'nama_item', 'harga_item', 'deskripsi_item', 'item_type']);
            
            // Kembalikan produk_id menjadi required
            $table->foreignId('produk_id')->nullable(false)->change();
        });
    }
};