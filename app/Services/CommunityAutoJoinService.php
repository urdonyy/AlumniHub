<?php

namespace App\Services;

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Collection;

class CommunityAutoJoinService
{
    public function attachMatchingCommunities(User $user): Collection
    {
        $matchingCommunities = Community::query()
            ->with('rules')
            ->where('type', '!=', Community::TYPE_PROGRAM_BATCH)
            ->get()
            ->filter(function (Community $community) use ($user): bool {
                return $community->rules->contains(function ($rule) use ($user): bool {
                    return ($rule->batch_year === null || $rule->batch_year === $user->batch_year)
                        && ($rule->program_course === null || $rule->program_course === $user->program_course);
                });
            })
            ->values();

        if ($matchingCommunities->isNotEmpty()) {
            $user->communities()->syncWithoutDetaching($matchingCommunities->pluck('id')->all());
        }

        return $matchingCommunities;
    }
}
