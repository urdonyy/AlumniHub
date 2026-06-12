<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
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
        $thisMonth = now()->format('Y-m');

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
            'experiences.*.title' => ['nullable', 'string', 'max:120'],
            'experiences.*.organization' => ['nullable', 'string', 'max:120'],
            'experiences.*.start_month' => ['nullable', 'date_format:Y-m', 'before_or_equal:' . $thisMonth],
            'experiences.*.end_month' => ['nullable', 'date_format:Y-m', 'after_or_equal:experiences.*.start_month', 'before_or_equal:' . $thisMonth],
            'experiences.*.description' => ['nullable', 'string', 'max:1000'],
            'educations' => ['nullable', 'array', 'max:10'],
            'educations.*.id' => ['nullable', 'integer'],
            'educations.*.school' => ['nullable', 'string', 'max:160'],
            'educations.*.degree' => ['nullable', 'string', 'max:160'],
            'educations.*.start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'educations.*.end_date' => ['nullable', 'date', 'after_or_equal:educations.*.start_date'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ];
    }

    /**
     * Human-friendly messages so array field paths never leak into the UI.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'experiences.*.start_month.before_or_equal' => "The start month can't be in the future.",
            'experiences.*.start_month.date_format' => 'Please enter a valid start month.',
            'experiences.*.end_month.before_or_equal' => "The end month can't be in the future.",
            'experiences.*.end_month.after_or_equal' => "The end month can't be before the start month.",
            'experiences.*.end_month.date_format' => 'Please enter a valid end month.',
            'educations.*.start_date.before_or_equal' => "The start date can't be in the future.",
            'educations.*.end_date.after_or_equal' => "The end date can't be before the start date.",
        ];
    }

    /**
     * Require a role + organization (or school + degree) together, but only
     * when a row actually has data. Done here instead of `required_with` with
     * wildcards, which misfires on sibling array fields and produces confusing
     * "X is required when Y is present" errors even on filled rows.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('experiences', []) as $i => $exp) {
                $title = trim((string) ($exp['title'] ?? ''));
                $organization = trim((string) ($exp['organization'] ?? ''));
                $rowHasData = $title !== '' || $organization !== ''
                    || ($exp['start_month'] ?? '') !== '' || ($exp['end_month'] ?? '') !== ''
                    || trim((string) ($exp['description'] ?? '')) !== '';

                if (! $rowHasData) {
                    continue;
                }

                if ($title === '') {
                    $validator->errors()->add("experiences.{$i}.title", 'Please add a role / title.');
                }
                if ($organization === '') {
                    $validator->errors()->add("experiences.{$i}.organization", 'Please add an organization.');
                }
                if (($exp['start_month'] ?? '') === '') {
                    $validator->errors()->add("experiences.{$i}.start_month", 'Please add a start month.');
                }
            }

            foreach ((array) $this->input('educations', []) as $i => $edu) {
                $school = trim((string) ($edu['school'] ?? ''));
                $degree = trim((string) ($edu['degree'] ?? ''));
                $rowHasData = $school !== '' || $degree !== ''
                    || ($edu['start_date'] ?? '') !== '' || ($edu['end_date'] ?? '') !== '';

                if (! $rowHasData) {
                    continue;
                }

                if ($school === '') {
                    $validator->errors()->add("educations.{$i}.school", 'Please add a school.');
                }
                if ($degree === '') {
                    $validator->errors()->add("educations.{$i}.degree", 'Please add a degree / field.');
                }
                if (($edu['start_date'] ?? '') === '') {
                    $validator->errors()->add("educations.{$i}.start_date", 'Please add a start date.');
                }
            }
        });
    }
}
