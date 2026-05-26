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
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'skills' => ['nullable', 'string', 'max:2000'],
            'experiences' => ['nullable', 'array', 'max:10'],
            'experiences.*.id' => ['nullable', 'integer'],
            'experiences.*.title' => [
                'nullable',
                'string',
                'max:120',
                'required_with:experiences.*.organization,experiences.*.start_month,experiences.*.end_month,experiences.*.description',
            ],
            'experiences.*.organization' => [
                'nullable',
                'string',
                'max:120',
                'required_with:experiences.*.title,experiences.*.start_month,experiences.*.end_month,experiences.*.description',
            ],
            'experiences.*.start_month' => ['nullable', 'date_format:Y-m'],
            'experiences.*.end_month' => ['nullable', 'date_format:Y-m'],
            'experiences.*.description' => ['nullable', 'string', 'max:1000'],
            'educations' => ['nullable', 'array', 'max:10'],
            'educations.*.id' => ['nullable', 'integer'],
            'educations.*.school' => [
                'nullable',
                'string',
                'max:160',
                'required_with:educations.*.degree,educations.*.start_date,educations.*.end_date',
            ],
            'educations.*.degree' => [
                'nullable',
                'string',
                'max:160',
                'required_with:educations.*.school,educations.*.start_date,educations.*.end_date',
            ],
            'educations.*.start_date' => ['nullable', 'date'],
            'educations.*.end_date' => ['nullable', 'date', 'after_or_equal:educations.*.start_date'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
