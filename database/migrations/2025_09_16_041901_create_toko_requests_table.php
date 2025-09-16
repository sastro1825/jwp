<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel permohonan toko
     */
    public function up(): void
    {
        Schema::create('toko_requests', function (Blueprint $table) {
            $table->id(); // ID permohonan toko
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Customer yang mengajukan
            $table->string('nama_toko'); // Nama toko yang diinginkan
            $table->text('deskripsi_toko')->nullable(); // Deskripsi toko
            $table->string('kategori_usaha')->nullable(); // Kategori usaha yang akan dijual
            $table->text('alamat_toko'); // Alamat toko fisik
            $table->string('no_telepon')->nullable(); // No telepon toko
            $table->text('alasan_permohonan'); // Alasan mengajukan permohonan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status permohonan
            $table->text('catatan_admin')->nullable(); // Catatan dari admin saat approve/reject
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('toko_requests');
    }
};