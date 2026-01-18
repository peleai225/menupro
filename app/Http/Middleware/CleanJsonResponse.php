<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
                        
                        // Encode with flags to ignore invalid UTF-8
                        $cleanedContent = json_encode($cleaned, 
                            JSON_UNESCAPED_UNICODE | 
                            JSON_INVALID_UTF8_IGNORE | 
                            JSON_PARTIAL_OUTPUT_ON_ERROR
                        );
                        
                        if ($cleanedContent !== false) {
                            $response->setContent($cleanedContent);
                        }
                    } else {
                        // If JSON decode failed, try to clean the raw content
                        $cleanedContent = $this->cleanString($content);
                        if (mb_check_encoding($cleanedContent, 'UTF-8')) {
                            $response->setContent($cleanedContent);
                        }
                    }
                }
            } catch (\JsonException $e) {
                // If encoding fails, try with more permissive flags
                try {
                    if (isset($cleaned)) {
                        $cleanedContent = json_encode($cleaned, 
                            JSON_UNESCAPED_UNICODE | 
                            JSON_INVALID_UTF8_IGNORE | 
                            JSON_PARTIAL_OUTPUT_ON_ERROR
                        );
                        if ($cleanedContent !== false) {
                            $response->setContent($cleanedContent);
                        }
                    }
                } catch (\Exception $e2) {
                    \Log::warning('Failed to clean JSON response', [
                        'error' => $e2->getMessage(),
                        'original_error' => $e->getMessage()
                    ]);
                }
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

