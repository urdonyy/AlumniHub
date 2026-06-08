<?php

namespace App\Http\Requests;

use App\Models\CommunityCreationRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityCreationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isVerified() && ! $user->isInstitution();
    }

    public function rules(): array
    {
        return [
            'year_section' => ['required', 'string', 'regex:/^[1-3]-\d$/'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'purpose' => ['required', 'string', 'min:20', 'max:2000'],
            'co_moderator_ids' => ['required', 'array', 'size:2'],
            'co_moderator_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'year_section.regex' => 'Year must be 1–3 and Section must be a digit (e.g., 2-1).',
            'co_moderator_ids.size' => 'You must invite exactly 2 co-moderators.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var User $user */
            $user = $this->user();

            $programCode = self::extractProgramCode($user->program_course);
            if (! $programCode) {
                $v->errors()->add('year_section', 'Your program is missing or has no short code. Update your profile first.');
                return;
            }

            if (! $user->batch_year) {
                $v->errors()->add('year_section', 'Your batch year is missing. Update your profile first.');
                return;
            }

            $yearSection = (string) $this->input('year_section');
            $name = sprintf('%s %s Batch %d', $programCode, $yearSection, (int) $user->batch_year);

            $duplicate = CommunityCreationRequest::query()
                ->where('name', $name)
                ->whereNotIn('status', [
                    CommunityCreationRequest::STATUS_REJECTED,
                    CommunityCreationRequest::STATUS_CANCELLED,
                ])
                ->exists();

            if ($duplicate) {
                $v->errors()->add('year_section', 'A request for "' . $name . '" already exists.');
            }

            $existingOwn = CommunityCreationRequest::query()
                ->where('requestor_id', $user->id)
                ->whereIn('status', [
                    CommunityCreationRequest::STATUS_PENDING_CO_MODS,
                    CommunityCreationRequest::STATUS_PENDING_ADMIN,
                    CommunityCreationRequest::STATUS_APPROVED,
                ])
                ->where('batch_year', (int) $user->batch_year)
                ->where('program_course', $user->program_course)
                ->exists();

            if ($existingOwn) {
                $v->errors()->add('year_section', 'You already have an active or approved community for your batch/program.');
            }

            $this->merge([
                '_resolved_name' => $name,
                '_resolved_program_course' => $user->program_course,
                '_resolved_batch_year' => (int) $user->batch_year,
            ]);

            $coModIds = $this->input('co_moderator_ids', []);

            if (in_array($user->id, $coModIds, false)) {
                $v->errors()->add('co_moderator_ids', 'You cannot invite yourself as a co-moderator.');
            }

            foreach ($coModIds as $idx => $coModId) {
                $coMod = User::find($coModId);
                if (! $coMod) {
                    continue;
                }
                if (! $coMod->isVerified()) {
                    $v->errors()->add("co_moderator_ids.$idx", $coMod->name . ' must be a verified user.');
                }
                if (! $user->isConnectedWith($coMod)) {
                    $v->errors()->add("co_moderator_ids.$idx", $coMod->name . ' is not in your connections.');
                }
                if ($coMod->batch_year !== $user->batch_year || $coMod->program_course !== $user->program_course) {
                    $v->errors()->add("co_moderator_ids.$idx", $coMod->name . ' is not in your program/batch.');
                }
            }
        });
    }

    /**
     * @return array{name:string,description:string,purpose:string,batch_year:int,program_course:string,year_section:string}
     */
    public function validatedData(): array
    {
        $v = $this->validated();
        return [
            'name' => (string) $this->input('_resolved_name'),
            'description' => $v['description'],
            'purpose' => $v['purpose'],
            'batch_year' => (int) $this->input('_resolved_batch_year'),
            'program_course' => (string) $this->input('_resolved_program_course'),
            'year_section' => (string) $v['year_section'],
        ];
    }

    /**
     * Extract a short program code from a program label.
     * "Diploma in Information Communication Technology (DICT)" -> "DICT"
     * "BSCS" -> "BSCS"
     */
    public static function extractProgramCode(?string $programCourse): ?string
    {
        if (! $programCourse) {
            return null;
        }

        if (preg_match('/\(([A-Z]{2,10})\)\s*$/', $programCourse, $m)) {
            return $m[1];
        }

        if (preg_match('/^[A-Z]{2,10}$/', trim($programCourse))) {
            return trim($programCourse);
        }

        return null;
    }
}
