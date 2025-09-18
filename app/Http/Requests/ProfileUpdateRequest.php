<?php

// Menggunakan namespace untuk organisasi kode
namespace App\Http\Requests;

// Mengimpor model dan library yang diperlukan
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Kelas untuk validasi pembaruan profil pengguna
class ProfileUpdateRequest extends FormRequest
{
    // Menentukan aturan validasi untuk permintaan pembaruan profil
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'contact_no' => ['nullable', 'string', 'max:20'],
            'paypal_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    // Menentukan pesan kustom untuk kesalahan validasi
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'dob.date' => 'Format tanggal lahir tidak valid.',
            'dob.before' => 'Tanggal lahir harus sebelum hari ini.',
            'gender.in' => 'Gender harus male atau female.',
            'address.max' => 'Alamat tidak boleh lebih dari 500 karakter.',
            'city.max' => 'Kota tidak boleh lebih dari 100 karakter.',
            'contact_no.max' => 'No HP tidak boleh lebih dari 20 karakter.',
            'paypal_id.max' => 'PayPal ID tidak boleh lebih dari 100 karakter.',
        ];
    }
}