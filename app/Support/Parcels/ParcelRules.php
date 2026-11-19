<?php

namespace App\Support\Parcels;

use App\Models\Parcel;

/**
 * Règles de validation communes (API client, API partenaire, page commerçant),
 * avec messages et noms de champs en français.
 */
class ParcelRules
{
    public const PHONE = 'regex:/^\+?[0-9][0-9 .\-]{7,19}$/';

    public static function quote(): array
    {
        return [
            'pickup_lat' => 'required|numeric|between:-90,90',
            'pickup_lng' => 'required|numeric|between:-180,180',
            'dropoff_lat' => 'required|numeric|between:-90,90',
            'dropoff_lng' => 'required|numeric|between:-180,180',
            'size' => 'required|in:' . implode(',', Parcel::SIZES),
        ];
    }

    public static function create(bool $partner = false): array
    {
        $rules = self::quote() + [
            'pickup_address' => 'required|string|min:3|max:300',
            'dropoff_address' => 'required|string|min:3|max:300',
            'sender_name' => ($partner ? 'required' : 'nullable') . '|string|min:2|max:120',
            'sender_phone' => [$partner ? 'required' : 'nullable', 'string', self::PHONE],
            'recipient_name' => 'required|string|min:2|max:120',
            'recipient_phone' => ['required', 'string', self::PHONE],
            'description' => 'required|string|min:2|max:160',
            'note' => 'nullable|string|max:300',
        ];

        if ($partner) {
            $rules += [
                'external_reference' => 'required|string|max:100',
                'pickup_contact' => 'nullable|array',
                'pickup_contact.name' => 'nullable|string|max:120',
                'pickup_contact.phone' => ['nullable', 'string', self::PHONE],
            ];
        } else {
            $rules += [
                'payment_method' => 'required|in:jeko,cash',
                'jeko_operator' => 'nullable|in:wave,orange,mtn,moov',
            ];
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'required' => 'Le champ « :attribute » est obligatoire.',
            'numeric' => 'Le champ « :attribute » doit être un nombre.',
            'between' => 'Le champ « :attribute » est hors limites.',
            'in' => 'La valeur du champ « :attribute » n\'est pas valide.',
            'min' => 'Le champ « :attribute » est trop court.',
            'max' => 'Le champ « :attribute » est trop long.',
            'regex' => 'Le numéro de téléphone « :attribute » n\'est pas valide.',
            'string' => 'Le champ « :attribute » doit être un texte.',
            'array' => 'Le champ « :attribute » est mal formé.',
            'image' => 'La photo doit être une image (JPEG, PNG ou WebP).',
            'mimes' => 'La photo doit être au format JPEG, PNG ou WebP.',
            'photo.max' => 'La photo est trop lourde (6 Mo maximum).',
            'accepted' => 'Vous devez confirmer « :attribute ».',
            'digits' => 'Le code doit contenir exactement 4 chiffres.',
        ];
    }

    public static function attributes(): array
    {
        return [
            'pickup_lat' => 'latitude de ramassage',
            'pickup_lng' => 'longitude de ramassage',
            'dropoff_lat' => 'latitude de livraison',
            'dropoff_lng' => 'longitude de livraison',
            'pickup_address' => 'adresse de ramassage',
            'dropoff_address' => 'adresse de livraison',
            'sender_name' => 'nom de l\'expéditeur',
            'sender_phone' => 'téléphone de l\'expéditeur',
            'recipient_name' => 'nom du destinataire',
            'recipient_phone' => 'téléphone du destinataire',
            'size' => 'taille',
            'description' => 'description',
            'note' => 'note',
            'payment_method' => 'mode de paiement',
            'jeko_operator' => 'opérateur Mobile Money',
            'external_reference' => 'référence externe',
            'pickup_contact.name' => 'nom du contact de ramassage',
            'pickup_contact.phone' => 'téléphone du contact de ramassage',
            'photo' => 'photo',
            'cash_received' => 'montant reçu en espèces',
            'code' => 'code',
            'reason' => 'motif',
        ];
    }
}
