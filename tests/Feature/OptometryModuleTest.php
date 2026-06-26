<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashRegisterSession;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\MedicalOrder;
use App\Models\OptometryPatientProfile;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OptometryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_company_cannot_access_optometry_routes(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('optometry.patients.index'))
            ->assertForbidden();
    }

    public function test_pos_subscription_does_not_expose_or_accept_medical_orders_in_pos(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertDontSee('Orden medica');

        $this->actingAs($user)
            ->post(route('pos.checkout'), [
                'branch_id' => $user->branch_id,
                'customer_id' => Customer::query()->value('id'),
                'medical_order_id' => 1,
                'items' => [
                    [
                        'product_id' => 1,
                        'quantity' => 1,
                        'unit_price' => 1.20,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'cash',
                        'amount' => 1.39,
                    ],
                ],
            ])
            ->assertSessionHasErrors('medical_order_id');
    }

    public function test_optic_company_can_manage_records_print_and_use_order_in_sale(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $this->markUserCompanyAsOptic($user);

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 50,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->post(route('optometry.patients.store'), [
                'name' => 'Paciente Optico',
                'document' => 'OPT-001',
                'email' => 'paciente@optic.test',
                'phone' => '3000000',
                'address' => 'Calle 1',
                'is_active' => 1,
                'birth_date' => '1990-01-01',
                'gender' => 'female',
                'occupation' => 'Disenadora',
                'emergency_contact_name' => 'Contacto Uno',
                'emergency_contact_phone' => '3111111',
                'allergies' => 'Ninguna',
                'systemic_history' => 'Sin antecedentes',
                'ocular_history' => 'Usa gafas',
            ])
            ->assertRedirect();

        $patient = Customer::where('document', 'OPT-001')->firstOrFail();
        $this->assertDatabaseHas('optometry_patient_profiles', [
            'customer_id' => $patient->id,
            'occupation' => 'Disenadora',
        ]);

        $this->actingAs($user)
            ->post(route('optometry.records.store'), [
                'customer_id' => $patient->id,
                'examined_at' => now()->format('Y-m-d H:i:s'),
                'reason_for_consultation' => 'Vision borrosa de lejos',
                'medical_history' => 'Sin hallazgos relevantes',
                'ocular_history' => 'Uso previo de lentes',
                'examination' => 'Examen basico completado',
                'diagnosis' => 'Miopia simple',
                'treatment_plan' => 'Formula y seguimiento',
                'observations' => 'Control en seis meses',
                'professional_name' => 'Dra. Optica',
                'professional_license' => 'RP-123',
            ])
            ->assertRedirect();

        $record = \App\Models\ClinicalRecord::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('optometry.orders.store'), [
                'customer_id' => $patient->id,
                'clinical_record_id' => $record->id,
                'ordered_at' => now()->format('Y-m-d H:i:s'),
                'status' => MedicalOrder::STATUS_ACTIVE,
                'prescription_details' => 'Lente monofocal, formula base',
                'usage_instructions' => 'Uso permanente',
                'observations' => 'Montura liviana',
                'professional_name' => 'Dra. Optica',
                'professional_license' => 'RP-123',
            ])
            ->assertRedirect();

        $order = MedicalOrder::query()->firstOrFail();

        $this->actingAs($user)->get(route('optometry.records.print', $record))->assertOk();
        $this->actingAs($user)->get(route('optometry.orders.print', $order))->assertOk();
        $this->actingAs($user)->get(route('pos.index'))->assertOk();

        $this->actingAs($user)
            ->post(route('pos.checkout'), [
                'branch_id' => $user->branch_id,
                'customer_id' => $patient->id,
                'medical_order_id' => $order->id,
                'items' => [
                    [
                        'product_id' => 1,
                        'quantity' => 1,
                        'unit_price' => 1.20,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'cash',
                        'amount' => 1.39,
                    ],
                ],
            ])
            ->assertRedirect();

        $sale = Sale::query()->firstOrFail();
        $this->assertSame($order->id, $sale->medical_order_id);
        $this->assertSame(MedicalOrder::STATUS_USED, $order->fresh()->status);
    }

    public function test_optometry_module_respects_company_scope(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$companyA, $branchA] = $this->makeCompanyContext('optic', 'Optica A');
        [$companyB, $branchB] = $this->makeCompanyContext('optic', 'Optica B');
        $userA = $this->makeUser($companyA, $branchA, 'optic-a@test.local', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $companyA->id,
            'plan_type' => 'optic',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $foreignPatient = Customer::withoutGlobalScopes()->create([
            'company_id' => $companyB->id,
            'name' => 'Paciente B',
            'document' => 'PB-1',
            'contact_type' => Customer::TYPE_PERSON,
            'is_active' => true,
        ]);

        OptometryPatientProfile::withoutGlobalScopes()->create([
            'company_id' => $companyB->id,
            'customer_id' => $foreignPatient->id,
        ]);

        $this->actingAs($userA)
            ->post(route('optometry.records.store'), [
                'customer_id' => $foreignPatient->id,
                'examined_at' => now()->format('Y-m-d H:i:s'),
                'reason_for_consultation' => 'No debe pasar',
            ])
            ->assertSessionHasErrors('customer_id');
    }

    private function makeCompanyContext(string $slug, string $name): array
    {
        $type = CompanyType::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'features' => ['patients', 'optical_prescriptions', 'sales'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => $name,
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'OP-'.$company->id,
        ]);

        return [$company, $branch];
    }

    private function makeUser(Company $company, Branch $branch, string $email, string $role): User
    {
        $user = User::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Usuario Optica',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function markUserCompanyAsOptic(User $user): void
    {
        $type = CompanyType::query()->firstOrCreate(
            ['slug' => 'optic'],
            [
                'name' => 'Optica',
                'features' => ['patients', 'optical_prescriptions', 'sales'],
                'is_active' => true,
            ]
        );

        $user->company()->update([
            'company_type_id' => $type->id,
        ]);

        CompanySubscription::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereIn('status', [
                CompanySubscription::STATUS_ACTIVE,
                CompanySubscription::STATUS_PENDING_PAYMENT,
            ])
            ->update([
                'plan_type' => 'optic',
                'status' => CompanySubscription::STATUS_ACTIVE,
                'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
            ]);

        $user->unsetRelation('company');
        $user->refresh();
    }
}
