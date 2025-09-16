<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations untuk update enum role user
     */
    public function up(): void
    {
        // Update enum role untuk menambah pemilik_toko
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer', 'pemilik_toko') DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Kembalikan ke enum semula
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer') DEFAULT 'customer'");
    }
};