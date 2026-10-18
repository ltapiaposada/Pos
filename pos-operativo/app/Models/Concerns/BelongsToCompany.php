<?php

namespace App\Models\Concerns;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $companyId = CompanyContext::companyIdForScopedReads();

            if ($companyId !== null) {
                $builder->where($builder->qualifyColumn('company_id'), $companyId);
            }
        });

        static::creating(function ($model): void {
            if (empty($model->company_id)) {
                $companyId = CompanyContext::companyIdForWrites();

                if ($companyId !== null) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        if ($companyId === null) {
            return $query;
        }

        return $query->where($query->qualifyColumn('company_id'), $companyId);
    }
}
