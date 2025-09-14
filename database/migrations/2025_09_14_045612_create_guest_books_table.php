<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - untuk membuat tabel guest books
     */
    public function up(): void
    {
        Schema::create('guest_books', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama customer yang memberikan feedback
            $table->string('email'); // Email customer
            $table->text('message'); // Pesan feedback
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status moderasi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_books');
    }
};