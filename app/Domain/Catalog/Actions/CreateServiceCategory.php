<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateServiceCategory
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): ServiceCategory
    {
        return DB::transaction(function () use ($actor, $data): ServiceCategory {
            $category = ServiceCategory::create([
                ...$data,
                'is_active' => true,
            ]);

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($category)
                ->event('catalog.category.created')
                ->withProperties(['code' => $category->code])
                ->log('Service category created.');

            return $category;
        });
    }
}
