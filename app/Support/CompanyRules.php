<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyRules
{
    public static function currentCompanyId(): ?int
    {
        return CompanyContext::authenticatedCompanyId() ?? CompanyContext::defaultCompanyId();
    }

    public static function companyScoped(string $table, string $column = 'id', ?string $companyColumn = 'company_id')
    {
        $companyId = static::currentCompanyId();
        $rule = Rule::exists($table, $column);

        if ($companyId === null || $companyColumn === null) {
            return $rule;
        }

        return $rule->where(fn ($query) => $query->where($companyColumn, $companyId));
    }

    public static function branchScoped(string $table, string $column = 'id', string $branchColumn = 'branch_id')
    {
        $companyId = static::currentCompanyId();
        $rule = Rule::exists($table, $column);

        if ($companyId === null) {
            return $rule;
        }

        return $rule->where(fn ($query) => $query->whereIn(
            $branchColumn,
            DB::table('branches')->select('id')->where('company_id', $companyId)
        ));
    }
}
