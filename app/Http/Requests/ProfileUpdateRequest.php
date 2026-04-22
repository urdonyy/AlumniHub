<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentYear = (int) now()->format('Y');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'batch_year' => ['required', 'integer', 'between:2024,' . $currentYear],
            'program_course' => ['required', Rule::in([
                'Diploma in Civil Engineering Technology (DCvET)',
                'Diploma in Computer Engineering Technology (DCET)',
                'Diploma in Electrical Engineering Technology (DEET)',
                'Diploma in Electronics Engineering Technology (DECET)',
                'Diploma in Information Communication Technology (DICT)',
                'Diploma in Mechanical Engineering Technology (DMET)',
                'Diploma in Office Management Technology (DOMT)',
                'Diploma in Railway Engineering Technology (DRET)',
            ])],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
