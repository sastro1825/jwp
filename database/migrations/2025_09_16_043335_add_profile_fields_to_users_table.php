<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk menambah kolom profile ke tabel users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('password'); // Date of birth
            $table->enum('gender', ['male', 'female'])->nullable()->after('dob'); // Gender
            $table->text('address')->nullable()->after('gender'); // Alamat lengkap
            $table->string('city', 100)->nullable()->after('address'); // Kota
            $table->string('contact_no', 20)->nullable()->after('city'); // No HP
            $table->string('paypal_id', 100)->nullable()->after('contact_no'); // PayPal ID
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dob', 'gender', 'address', 'city', 'contact_no', 'paypal_id']);
        });
    }
};