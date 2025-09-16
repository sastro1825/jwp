<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TokoRequest;
use Illuminate\Support\Facades\Hash;

class TokoRequestSeeder extends Seeder
{
    /**
     * Run the database seeds untuk test permohonan toko dengan kolom yang benar
     */
    public function run(): void
    {
        // Buat user customer untuk test permohonan toko
        $customerTest = User::create([
            'name' => 'Customer Test Toko',
            'email' => 'customer.test.toko@oss.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'dob' => '1990-01-15', // Tambahkan DOB
            'gender' => 'male', // Tambahkan gender
            'address' => 'Jl. Test No. 123, Jakarta Selatan',
            'city' => 'Jakarta',
            'contact_no' => '081234567890',
            'paypal_id' => 'customer.test@paypal.com', // Tambahkan PayPal ID
        ]);

        // Buat permohonan toko pending untuk testing
        TokoRequest::create([
            'user_id' => $customerTest->id,
            'nama_toko' => 'Apotek Sehat Test',
            'deskripsi_toko' => 'Apotek yang menyediakan obat-obatan dan alat kesehatan berkualitas',
            'kategori_usaha' => 'obat-obatan',
            'alamat_toko' => 'Jl. Kesehatan No. 456, Jakarta Selatan',
            'no_telepon' => '021-1234567',
            'alasan_permohonan' => 'Ingin membantu masyarakat dengan menyediakan obat-obatan berkualitas melalui platform OSS',
            'status' => 'pending'
        ]);

        // Buat user pemilik toko yang sudah approved
        $pemilikToko = User::create([
            'name' => 'Pemilik Toko Test',
            'email' => 'pemilik.test@oss.com',
            'password' => Hash::make('password123'),
            'role' => 'pemilik_toko',
            'dob' => '1985-05-20', // Tambahkan DOB
            'gender' => 'female', // Tambahkan gender
            'address' => 'Jl. Toko No. 789, Bandung',
            'city' => 'Bandung',
            'contact_no' => '082345678901',
            'paypal_id' => 'pemilik.test@paypal.com', // Tambahkan PayPal ID
        ]);

        // Buat permohonan toko yang sudah approved
        TokoRequest::create([
            'user_id' => $pemilikToko->id,
            'nama_toko' => 'Toko Kesehatan Prima Test',
            'deskripsi_toko' => 'Toko alat kesehatan lengkap dan terpercaya',
            'kategori_usaha' => 'alat-kesehatan',
            'alamat_toko' => 'Jl. Prima No. 101, Bandung',
            'no_telepon' => '022-7654321',
            'alasan_permohonan' => 'Berpengalaman di bidang alat kesehatan selama 10 tahun',
            'status' => 'approved',
            'catatan_admin' => 'Permohonan disetujui. Selamat bergabung dengan OSS!'
        ]);
    }
}