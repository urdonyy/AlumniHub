<?php

namespace App\Exceptions;

use App\Models\Community;
use App\Models\User;
use RuntimeException;

class UserAlreadyInProgramBatchException extends RuntimeException
{
    public function __construct(
        public readonly User $user,
        public readonly Community $currentCommunity,
    ) {
        parent::__construct(sprintf(
            'User %s is already a member of program-batch community "%s" and must leave before joining another.',
            $user->name,
            $currentCommunity->name,
        ));
    }
}
