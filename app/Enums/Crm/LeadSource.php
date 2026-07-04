<?php

namespace App\Enums\Crm;

enum LeadSource: string
{
    case TERRAIN = 'terrain';
    case REFERRAL = 'referral';
    case WEB = 'web';
    case WHATSAPP = 'whatsapp';
    case INBOUND = 'inbound';

    public function label(): string
    {
        return match ($this) {
            self::TERRAIN => 'Terrain',
            self::REFERRAL => 'Parrainage',
            self::WEB => 'Site web',
            self::WHATSAPP => 'WhatsApp',
            self::INBOUND => 'Entrant',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TERRAIN => 'map-pin',
            self::REFERRAL => 'users',
            self::WEB => 'globe-alt',
            self::WHATSAPP => 'chat-bubble-left-right',
            self::INBOUND => 'inbox-arrow-down',
        };
    }
}
