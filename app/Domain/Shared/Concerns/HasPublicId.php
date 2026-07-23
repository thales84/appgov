<?php

namespace App\Domain\Shared\Concerns;

use Illuminate\Support\Str;

trait HasPublicId
{
    public function initializeHasPublicId(): void
    {
        $this->mergeFillable(['public_id']);
    }

    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
