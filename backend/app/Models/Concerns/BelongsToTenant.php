<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model): void {
            $user = Auth::user();

            if (! $user) {
                return;
            }

            foreach (['tenant_id', 'branch_id'] as $column) {
                if ($model->isFillable($column) && empty($model->{$column}) && isset($user->{$column})) {
                    $model->{$column} = $user->{$column};
                }
            }

            if ($model->isFillable('created_by') && empty($model->created_by)) {
                $model->created_by = $user->getKey();
            }

            if ($model->isFillable('updated_by') && empty($model->updated_by)) {
                $model->updated_by = $user->getKey();
            }
        });

        static::updating(function ($model): void {
            $user = Auth::user();

            if ($user && $model->isFillable('updated_by')) {
                $model->updated_by = $user->getKey();
            }
        });
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where($query->getModel()->getTable().'.tenant_id', $tenantId);
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where($query->getModel()->getTable().'.branch_id', $branchId);
    }
}
