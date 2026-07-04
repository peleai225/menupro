<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifyScan extends Model
{
    protected $table = 'crm_verify_scans';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'location' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
