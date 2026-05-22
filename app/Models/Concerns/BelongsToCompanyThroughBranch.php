<?php

namespace App\Models\Concerns;

use App\Models\Branch;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompanyThroughBranch
{
    public static function bootBelongsToCompanyThroughBranch(): void
    {
        static::addGlobalScope('company_branch', function (Builder $builder): void {
            $companyId = CompanyContext::companyIdForScopedReads();

            if ($companyId !== null) {
                $builder->where($builder->qualifyColumn('company_id'), $companyId);
            }
        });

        static::creating(function ($model): void {
            if (! empty($model->company_id)) {
                return;
            }

            if (! empty($model->branch_id)) {
                $model->company_id = Branch::withoutGlobalScopes()
                    ->whereKey($model->branch_id)
                    ->value('company_id');
            }

            if (empty($model->company_id)) {
                $model->company_id = CompanyContext::companyIdForWrites();
            }
        });
    }
}
