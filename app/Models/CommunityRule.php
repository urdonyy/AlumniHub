<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'batch_year',
        'program_course',
    ];

    protected function casts(): array
    {
        return [
            'batch_year' => 'integer',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
