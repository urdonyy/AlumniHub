<?php

namespace App\Exceptions;

use App\Models\Community;
use RuntimeException;

class CommunityNameTakenException extends RuntimeException
{
    public function __construct(public readonly Community $existingCommunity, string $message = 'A community with this name already exists.')
    {
        parent::__construct($message);
    }
}
