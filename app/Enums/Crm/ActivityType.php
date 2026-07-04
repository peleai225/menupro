<?php

namespace App\Enums\Crm;

enum ActivityType: string
{
    case CALL = 'call';
    case VISIT = 'visit';
    case DEMO = 'demo';
    case NOTE = 'note';
    case STATUS_CHANGE = 'status_change';
    case WHATSAPP = 'whatsapp';
    case EMAIL = 'email';
    case ASSIGNMENT = 'assignment';

    public function label(): string
    {
        return match ($this) {
            self::CALL => 'Appel',
            self::VISIT => 'Visite terrain',
            self::DEMO => 'Démonstration',
            self::NOTE => 'Note',
            self::STATUS_CHANGE => 'Changement statut',
            self::WHATSAPP => 'WhatsApp',
            self::EMAIL => 'Email',
            self::ASSIGNMENT => 'Assignation',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CALL => 'phone',
            self::VISIT => 'map-pin',
            self::DEMO => 'presentation-chart-bar',
            self::NOTE => 'pencil-square',
            self::STATUS_CHANGE => 'arrow-path',
            self::WHATSAPP => 'chat-bubble-left-right',
            self::EMAIL => 'envelope',
            self::ASSIGNMENT => 'user-plus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CALL => 'blue',
            self::VISIT => 'emerald',
            self::DEMO => 'violet',
            self::NOTE => 'gray',
            self::STATUS_CHANGE => 'amber',
            self::WHATSAPP => 'green',
            self::EMAIL => 'sky',
            self::ASSIGNMENT => 'indigo',
        };
    }
}
