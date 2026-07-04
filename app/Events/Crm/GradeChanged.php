<?php

namespace App\Events\Crm;

use App\Enums\Crm\Grade;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradeChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Grade $newGrade,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("crm.user.{$this->user->id}"),
            new Channel('crm.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'grade.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'new_grade' => $this->newGrade->value,
            'new_grade_label' => $this->newGrade->label(),
        ];
    }
}
