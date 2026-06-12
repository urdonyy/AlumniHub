<?php

namespace App\Services;

use App\Exceptions\CommunityNameTakenException;
use App\Models\Community;
use App\Models\CommunityCreationRequest;
use App\Models\CommunityCreationRequestModerator;
use App\Models\User;
use App\Notifications\CoModeratorAcceptedNotification;
use App\Notifications\CoModeratorDeclinedNotification;
use App\Notifications\CoModeratorInviteNotification;
use App\Notifications\CommunityCreationApprovedNotification;
use App\Notifications\CommunityCreationCancelledNotification;
use App\Notifications\CommunityCreationPendingReviewNotification;
use App\Notifications\CommunityCreationRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunityCreationRequestService
{
    /**
     * @param array{name:string,description:string,purpose:string,batch_year:int,program_course:string,year_section:string} $data
     * @param array<int> $coModeratorIds
     */
    public function submit(User $requestor, array $data, array $coModeratorIds): CommunityCreationRequest
    {
        $existing = Community::query()
            ->programBatch()
            ->where('name', $data['name'])
            ->first();

        if ($existing) {
            throw new CommunityNameTakenException($existing);
        }

        return DB::transaction(function () use ($requestor, $data, $coModeratorIds) {
            $request = CommunityCreationRequest::create([
                'requestor_id' => $requestor->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'purpose' => $data['purpose'],
                'batch_year' => $data['batch_year'],
                'program_course' => $data['program_course'],
                'year_section' => $data['year_section'],
                'status' => CommunityCreationRequest::STATUS_PENDING_CO_MODS,
            ]);

            foreach ($coModeratorIds as $userId) {
                $request->coModeratorInvites()->create([
                    'invited_user_id' => $userId,
                    'status' => CommunityCreationRequestModerator::STATUS_PENDING,
                ]);
            }

            $invitedUsers = User::whereIn('id', $coModeratorIds)->get();
            foreach ($invitedUsers as $invitee) {
                $invitee->notify(new CoModeratorInviteNotification($request, $requestor));
            }

            return $request;
        });
    }

    public function respondAsCoMod(CommunityCreationRequestModerator $invite, bool $accepted): void
    {
        $request = $invite->request;

        if ($request->status !== CommunityCreationRequest::STATUS_PENDING_CO_MODS) {
            return;
        }

        $requestor = $request->requestor;
        $responder = $invite->invitedUser;

        DB::transaction(function () use ($invite, $accepted, $request, $requestor, $responder) {
            $invite->update([
                'status' => $accepted
                    ? CommunityCreationRequestModerator::STATUS_ACCEPTED
                    : CommunityCreationRequestModerator::STATUS_DECLINED,
                'responded_at' => now(),
            ]);

            $request->load('coModeratorInvites');

            if ($accepted) {
                $requestor->notify(new CoModeratorAcceptedNotification($request, $responder));

                if ($request->allCoModsAccepted()) {
                    $request->update(['status' => CommunityCreationRequest::STATUS_PENDING_ADMIN]);

                    $admins = User::query()->where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new CommunityCreationPendingReviewNotification($request));
                    }
                }
            } else {
                $requestor->notify(new CoModeratorDeclinedNotification($request, $responder));
            }
        });
    }

    public function cancelByRequestor(CommunityCreationRequest $request): void
    {
        if ($request->status !== CommunityCreationRequest::STATUS_PENDING_CO_MODS) {
            return;
        }

        $request->loadMissing(['requestor', 'coModeratorInvites.invitedUser']);

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => CommunityCreationRequest::STATUS_CANCELLED,
                'decided_at' => now(),
            ]);

            foreach ($request->coModeratorInvites as $invite) {
                if ($invite->status !== CommunityCreationRequestModerator::STATUS_ACCEPTED) {
                    continue;
                }
                $invite->invitedUser?->notify(
                    new CommunityCreationCancelledNotification($request, $request->requestor)
                );
            }
        });
    }

    public function approve(CommunityCreationRequest $request, User $admin, ?string $note = null): Community
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            $request->load(['requestor', 'coModeratorInvites.invitedUser']);

            $slug = $this->makeUniqueSlug($request->name);

            $community = Community::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'purpose' => $request->purpose,
                'created_by' => $request->requestor_id,
                'is_system' => false,
                'type' => Community::TYPE_PROGRAM_BATCH,
                'batch_year' => $request->batch_year,
                'program_course' => $request->program_course,
                'year_section' => $request->year_section,
            ]);

            $community->addModerator($request->requestor, 'senior_moderator');
            $community->members()->syncWithoutDetaching([$request->requestor_id]);

            // Note: the PUP-ITECH Official account intentionally does NOT auto-join
            // batch communities — those belong to the batch's own members.

            foreach ($request->coModeratorInvites as $invite) {
                if ($invite->status !== CommunityCreationRequestModerator::STATUS_ACCEPTED) {
                    continue;
                }
                $community->addModerator($invite->invitedUser, 'moderator');
                $community->members()->syncWithoutDetaching([$invite->invited_user_id]);
            }

            $request->update([
                'status' => CommunityCreationRequest::STATUS_APPROVED,
                'admin_id' => $admin->id,
                'admin_note' => $note,
                'community_id' => $community->id,
                'decided_at' => now(),
            ]);

            $request->requestor->notify(new CommunityCreationApprovedNotification($request, $community));
            foreach ($request->coModeratorInvites as $invite) {
                if ($invite->status === CommunityCreationRequestModerator::STATUS_ACCEPTED) {
                    $invite->invitedUser->notify(new CommunityCreationApprovedNotification($request, $community));
                }
            }

            return $community;
        });
    }

    public function reject(CommunityCreationRequest $request, User $admin, string $note): void
    {
        DB::transaction(function () use ($request, $admin, $note) {
            $request->update([
                'status' => CommunityCreationRequest::STATUS_REJECTED,
                'admin_id' => $admin->id,
                'admin_note' => $note,
                'decided_at' => now(),
            ]);

            $request->requestor->notify(new CommunityCreationRejectedNotification($request));
        });
    }

    private function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Community::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
