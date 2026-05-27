<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificationUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Please upload a verification document.',
            'document.file' => 'The document must be a valid file.',
            'document.mimes' => 'The document must be a PDF, JPG, JPEG, or PNG file.',
            'document.max' => 'The document must not exceed 5MB.',
        ];
    }
}
