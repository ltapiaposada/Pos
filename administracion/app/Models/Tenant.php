<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'companies';
    protected $fillable = ['tenant_uuid', 'name', 'domain', 'identification', 'email', 'phone', 'address', 'company_type_id', 'status', 'kickoff_completed', 'primary_admin_name', 'primary_admin_email', 'keycloak_subject'];

    public function entitlements()
    {
        return $this->hasMany(Entitlement::class, 'company_id');
    }

    public function provisioningJobs()
    {
        return $this->hasMany(ProvisioningJob::class, 'company_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class, 'company_id');
    }
}
