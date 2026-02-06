<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    /**
     * Les attributs à exclure du logging
     */
    protected array $excludedAttributes = [
        'password',
        'remember_token',
        'email_verified_at',
        'updated_at',
        'created_at',
    ];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        ActivityLog::log(
            'created',
            $model,
            $this->getDescription($model, 'créé'),
            ['new' => $this->filterAttributes($model->toArray())]
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $original = $model->getOriginal();
        
        // Filtrer les attributs exclus
        $filteredChanges = $this->filterAttributes($changes);
        $filteredOriginal = $this->filterAttributes(
            array_intersect_key($original, $changes)
        );

        // Ne pas logger si aucun changement significatif
        if (empty($filteredChanges)) {
            return;
        }

        ActivityLog::log(
            'updated',
            $model,
            $this->getDescription($model, 'modifié'),
            [
                'old' => $filteredOriginal,
                'new' => $filteredChanges,
            ]
        );
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        ActivityLog::log(
            'deleted',
            $model,
            $this->getDescription($model, 'supprimé'),
            ['old' => $this->filterAttributes($model->toArray())]
        );
    }

    /**
     * Filtrer les attributs exclus
     */
    protected function filterAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip($this->excludedAttributes));
    }

    /**
     * Générer une description
     */
    protected function getDescription(Model $model, string $action): string
    {
        $type = class_basename($model);
        $name = $model->name ?? $model->title ?? "#{$model->id}";
        
        return "{$type} \"{$name}\" {$action}";
    }
}
