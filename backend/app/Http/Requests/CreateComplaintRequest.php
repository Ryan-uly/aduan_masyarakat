<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => 'pending'
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi',
            'description.required' => 'Deskripsi wajib diisi',
        ];
    }
}
