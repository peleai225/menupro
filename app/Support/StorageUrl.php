<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    /**
     * Retourne l'URL publique d'un fichier stocké.
     * Corrige APP_URL si la valeur de dev (*.test ou localhost) est encore présente en prod.
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $url = Storage::url($path);

        // Si l'URL générée pointe vers un hôte local/de dev, forcer le domaine de prod
        if (preg_match('#^https?://(localhost|127\.0\.0\.1|.*\.test)(:\d+)?/#', $url)) {
            $url = preg_replace('#^https?://[^/]+#', 'https://www.menupro.ci', $url);
        }

        return $url;
    }
}
