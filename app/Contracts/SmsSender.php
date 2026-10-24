<?php

namespace App\Contracts;

/**
 * Envoi de SMS (relances urgentes du restaurant). Aucun fournisseur n'est encore choisi :
 * l'implémentation liée dans le conteneur est App\Services\Sms\NullSmsSender (journal seulement).
 * Brancher un fournisseur = créer une classe qui implémente ce contrat et changer la liaison
 * dans AppServiceProvider::register().
 */
interface SmsSender
{
    /**
     * @return bool true si le SMS a été remis au fournisseur
     */
    public function send(string $phone, string $message): bool;
}
