<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toko_kategoris', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('toko_id');
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2);
            $table->string('gambar')->nullable();
            $table->enum('category_type', [
                'alat-kesehatan',
                'obat-obatan', 
                'suplemen-kesehatan',
                'perawatan-kecantikan',
                'kesehatan-pribadi'
            ]);
            $table->timestamps();
            
            $table->foreign('toko_id')->references('id')->on('tokos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toko_kategoris');
    }
};