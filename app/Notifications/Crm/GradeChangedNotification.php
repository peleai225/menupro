<?php
namespace App\Notifications\Crm;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GradeChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $oldGrade,
        private string $newGrade,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'grade_changed',
            'title'     => 'Nouveau grade !',
            'message'   => "Félicitations ! Vous êtes maintenant {$this->newGrade} (anciennement {$this->oldGrade}).",
            'old_grade' => $this->oldGrade,
            'new_grade' => $this->newGrade,
        ];
    }
}
