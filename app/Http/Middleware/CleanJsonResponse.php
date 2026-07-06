<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CleanJsonResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (\InvalidArgumentException $e) {
            // If the error is about UTF-8, try to handle it
            if (str_contains($e->getMessage(), 'UTF-8')) {
                \Log::error('UTF-8 error caught in middleware', [
                    'error' => $e->getMessage(),
                    'path' => $request->path()
                ]);
                // Return a safe error response
                return response()->json([
                    'error' => 'Une erreur de codage est survenue. Veuillez réessayer.'
                ], 500);
            }
            throw $e;
        }

        // Ignorer les réponses streamées ou fichiers binaires — getContent() lève une exception
        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return $response;
        }

        // Only process JSON responses (including Livewire responses)
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/json') ||
            str_contains($request->path(), 'livewire')) {
            
            try {
                $content = $response->getContent();
                
                if (!empty($content) && is_string($content)) {
                    // Try to decode JSON
                    $data = json_decode($content, true);
                    
                    // If it's valid JSON, clean and re-encode
                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (is_array($data)) {
                            // Clean the data recursively
                            $cleaned = $this->cleanArray($data);
                        } else {
                            $cleaned = $data;
                        }
                        
                        // Encoder sans JSON_PARTIAL_OUTPUT_ON_ERROR pour ne pas produire
                        // un JSON tronqué qui casserait Livewire. JSON_THROW_ON_ERROR permet
                        // au bloc catch(\JsonException) ci-dessous de s'exécuter réellement.
                        $cleanedContent = json_encode($cleaned,
                            JSON_UNESCAPED_UNICODE |
                            JSON_INVALID_UTF8_IGNORE |
                            JSON_THROW_ON_ERROR
                        );

                        $response->setContent($cleanedContent);
                    } else {
                        // If JSON decode failed, try to clean the raw content
                        $cleanedContent = $this->cleanString($content);
                        if (mb_check_encoding($cleanedContent, 'UTF-8')) {
                            $response->setContent($cleanedContent);
                        }
                    }
                }
            } catch (\JsonException $e) {
                // L'encodage a échoué — retourner la réponse originale intacte plutôt que
                // de risquer un JSON partiel/corrompu (ex : Livewire attend un JSON complet).
                \Log::warning('CleanJsonResponse: JSON encoding failed, returning original response', [
                    'error' => $e->getMessage(),
                    'path'  => $request->path(),
                ]);
                // Ne pas modifier $response — la réponse originale est retournée telle quelle.
            } catch (\Exception $e) {
                // If cleaning fails, log and continue with original response
                \Log::warning('Failed to clean JSON response', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $response;
    }

    /**
     * Clean array recursively
     */
    protected function cleanArray(array $data): array
    {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            $cleanKey = is_string($key) ? $this->cleanString($key) : $key;
            
            if (is_array($value)) {
                $cleaned[$cleanKey] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                $cleaned[$cleanKey] = $this->cleanString($value);
            } else {
                $cleaned[$cleanKey] = $value;
            }
        }
        
        return $cleaned;
    }

    /**
     * Clean string to ensure valid UTF-8
     */
    protected function cleanString(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Use iconv to remove invalid UTF-8 sequences
        if (function_exists('iconv')) {
            $value = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if ($value === false) {
                return '';
            }
        }

        // Final check
        if (!mb_check_encoding($value, 'UTF-8')) {
            return '';
        }

        return $value;
    }
}

