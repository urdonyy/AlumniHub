<?php

namespace App\Http\Requests;

use App\Models\Community;
use App\Models\CommunityCreationRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommunityCreationRequest extends FormRequest
{
    public const NAME_REGEX = '/^([A-Z]{2,10})\s(\d-\d)\sBatch\s(\d{4})$/';

    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isVerified();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:' . self::NAME_REGEX,
                Rule::unique('community_creation_requests', 'name')
                    ->whereNotIn('status', [
                        CommunityCreationRequest::STATUS_REJECTED,
                        CommunityCreationRequest::STATUS_CANCELLED,
                    ]),
            ],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'purpose' => ['required', 'string', 'min:20', 'max:2000'],
            'co_moderator_ids' => ['required', 'array', 'size:2'],
            'co_moderator_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name must follow the format "PROGRAM Y-S Batch YYYY" (e.g., "DICT 3-3 Batch 2026").',
            'co_moderator_ids.size' => 'You must invite exactly 2 co-moderators.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var User $user */
            $user = $this->user();
            $name = (string) $this->input('name', '');

            if (preg_match(self::NAME_REGEX, $name, $matches)) {
                $program = $matches[1];
                $yearSection = $matches[2];
                $batchYear = (int) $matches[3];

                if ($user->program_course !== $program) {
                    $v->errors()->add('name', 'Program in the name must match your program (' . ($user->program_course ?? 'unset') . ').');
                }

                if ((int) $user->batch_year !== $batchYear) {
                    $v->errors()->add('name', 'Batch year in the name must match your batch (' . ($user->batch_year ?? 'unset') . ').');
                }

                $existingOwn = CommunityCreationRequest::query()
                    ->where('requestor_id', $user->id)
                    ->whereIn('status', [
                        CommunityCreationRequest::STATUS_PENDING_CO_MODS,
                        CommunityCreationRequest::STATUS_PENDING_ADMIN,
                        CommunityCreationRequest::STATUS_APPROVED,
                    ])
                    ->where('batch_year', $batchYear)
                    ->where('program_course', $program)
                    ->exists();

                if ($existingOwn) {
                    $v->errors()->add('name', 'You already have an active or approved community for this batch/program.');
                }

                $this->merge([
                    'program_course' => $program,
                    'year_section' => $yearSection,
                    'batch_year' => $batchYear,
                ]);
            }

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
            'name' => $v['name'],
            'description' => $v['description'],
            'purpose' => $v['purpose'],
            'batch_year' => (int) $this->input('batch_year'),
            'program_course' => (string) $this->input('program_course'),
            'year_section' => (string) $this->input('year_section'),
        ];
    }
}
