<?php

namespace App\Models;

use App\Enums\DeploymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandoDeployment extends Model
{
    protected $table = 'commando_deployments';

    protected $fillable = [
        'commando_agent_id',
        'restaurant_name',
        'manager_name',
        'phone',
        'latitude',
        'longitude',
        'status',
        'restaurant_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeploymentStatus::class,
        ];
    }

    public function commandoAgent(): BelongsTo
    {
        return $this->belongsTo(CommandoAgent::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
