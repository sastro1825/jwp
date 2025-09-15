<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Tambah field yang diperlukan untuk registrasi
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom yang diperlukan untuk form registrasi
            $table->date('dob')->nullable()->after('email'); // Date of birth
            $table->enum('gender', ['male', 'female'])->nullable()->after('dob'); // Gender
            $table->text('address')->nullable()->after('gender'); // Alamat lengkap
            $table->string('city', 100)->nullable()->after('address'); // Kota
            $table->string('contact_no', 20)->nullable()->after('city'); // No HP
            $table->string('paypal_id', 100)->nullable()->after('contact_no'); // PayPal ID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['dob', 'gender', 'address', 'city', 'contact_no', 'paypal_id']);
        });
    }
};