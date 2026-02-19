<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandoVerifyScan extends Model
{
    protected $fillable = ['commando_agent_id', 'ip_address', 'user_agent'];

    public function commandoAgent(): BelongsTo
    {
        return $this->belongsTo(CommandoAgent::class);
    }
}
