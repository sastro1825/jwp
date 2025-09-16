<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request - DIPERBAIKI: Validasi semua field profile
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            // Tambahan validasi untuk field profile yang lain
            'dob' => ['nullable', 'date', 'before:today'], // Tanggal lahir harus sebelum hari ini
            'gender' => ['nullable', 'in:male,female'], // Gender hanya male atau female
            'address' => ['nullable', 'string', 'max:500'], // Alamat maksimal 500 karakter
            'city' => ['nullable', 'string', 'max:100'], // Kota maksimal 100 karakter
            'contact_no' => ['nullable', 'string', 'max:20'], // No HP maksimal 20 karakter
            'paypal_id' => ['nullable', 'string', 'max:100'], // PayPal ID maksimal 100 karakter
        ];
    }

    /**
     * Get custom messages for validator errors
     */
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