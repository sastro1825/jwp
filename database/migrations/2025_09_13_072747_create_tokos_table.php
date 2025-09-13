<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tokos', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama toko vendor
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Pemilik (customer)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('alamat')->nullable(); // Opsional alamat toko
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokos');
    }
};