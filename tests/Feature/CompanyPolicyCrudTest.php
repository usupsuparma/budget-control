<?php

namespace Tests\Feature;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Employee;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CompanyPolicyCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('budget_control_testing', config('database.connections.mysql.database'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        parent::tearDown();
    }

    public function test_authorized_user_can_create_company_policy_from_company_policy_page(): void
    {
        $this->actingAsAuthorizedEmployee();
        $token = 'company-policy-create-token';

        $response = $this
            ->withSession(['_token' => $token])
            ->from(route('company-policy.index'))
            ->post(route('company-policy.store'), $this->companyPolicyPayload(2026, [
                '_token' => $token,
            ]));

        $response
            ->assertRedirect(route('company-policy.index'))
            ->assertSessionHas('success', 'Company Policy saved successfully');

        $this->assertDatabaseHas('company_policy', [
            'tahun' => 2026,
            'nama_dokumen' => 'Company Policy FY2026',
            'file_path' => '0',
        ]);

        $policy = CompanyPolicy::where('tahun', 2026)->firstOrFail();

        $this->assertStringContainsString('PT Integration Test', $policy->header);
        $this->assertStringContainsString('Background EN 2026', $policy->contents_en);
        $this->assertStringContainsString('Latar belakang ID 2026', $policy->contents_id);

        $this->assertDatabaseHas('company_policy_detail', [
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Growth 2026',
            'description' => 'Grow responsibly 2026',
            'strategic_goal_id' => 'Pertumbuhan 2026',
            'description_id' => 'Tumbuh bertanggung jawab 2026',
            'target' => '0',
        ]);
    }

    public function test_authorized_user_can_update_company_policy_and_replace_details(): void
    {
        $this->actingAsAuthorizedEmployee();
        $token = 'company-policy-update-token';

        $policy = $this->createPolicyWithDetail(2026);

        $response = $this
            ->withSession(['_token' => $token])
            ->from(route('company-policy.index'))
            ->put(route('company-policy.update', $policy->id), $this->companyPolicyPayload(2027, [
                '_token' => $token,
                'company_policy_core_en' => ['Updated Growth 2027', 'Operational Excellence 2027'],
                'company_policy_desc_en' => ['Updated EN Description', 'Second EN Description'],
                'company_policy_core_id' => ['Pertumbuhan Diperbarui 2027', 'Keunggulan Operasional 2027'],
                'company_policy_desc_id' => ['Deskripsi ID diperbarui', 'Deskripsi ID kedua'],
            ]));

        $response
            ->assertRedirect(route('company-policy.index'))
            ->assertSessionHas('success', 'Company Policy updated successfully');

        $policy->refresh();

        $this->assertSame(2027, (int) $policy->tahun);
        $this->assertSame('Company Policy FY2027', $policy->nama_dokumen);
        $this->assertStringContainsString('PT Integration Test', $policy->header);
        $this->assertStringContainsString('Background EN 2027', $policy->contents_en);

        $this->assertDatabaseMissing('company_policy_detail', [
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Original Goal',
        ]);

        $this->assertDatabaseHas('company_policy_detail', [
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Updated Growth 2027',
            'description' => 'Updated EN Description',
            'strategic_goal_id' => 'Pertumbuhan Diperbarui 2027',
            'description_id' => 'Deskripsi ID diperbarui',
            'target' => '0',
        ]);

        $this->assertDatabaseHas('company_policy_detail', [
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Operational Excellence 2027',
            'description' => 'Second EN Description',
            'strategic_goal_id' => 'Keunggulan Operasional 2027',
            'description_id' => 'Deskripsi ID kedua',
            'target' => '0',
        ]);

        $this->assertSame(2, $policy->details()->count());
    }

    public function test_authorized_user_can_delete_company_policy_and_its_details(): void
    {
        Storage::fake('public');
        $this->actingAsAuthorizedEmployee();
        $token = 'company-policy-delete-token';

        Storage::disk('public')->put('company-policy/test-policy.pdf', 'fake pdf content');
        $policy = $this->createPolicyWithDetail(2026, [
            'file_path' => 'company-policy/test-policy.pdf',
        ]);
        $detailId = $policy->details()->firstOrFail()->id;

        $response = $this
            ->withSession(['_token' => $token])
            ->from(route('company-policy.index'))
            ->delete(route('company-policy.destroy', $policy->id), [
                '_token' => $token,
            ]);

        $response
            ->assertRedirect(route('company-policy.index'))
            ->assertSessionHas('success', 'Dokumen dan seluruh strategic goals berhasil dihapus.');

        $this->assertDatabaseMissing('company_policy', [
            'id' => $policy->id,
        ]);
        $this->assertDatabaseMissing('company_policy_detail', [
            'id' => $detailId,
        ]);
        Storage::disk('public')->assertMissing('company-policy/test-policy.pdf');
    }

    private function actingAsAuthorizedEmployee(): Employee
    {
        $employee = Employee::create([
            'email' => 'company-policy-tester@example.test',
            'password' => bcrypt('password'),
            'first_name' => 'Company',
            'last_name' => 'Policy Tester',
            'status' => 'Active',
        ]);

        collect([
            'companypolicy.view',
            'companypolicy.create',
            'companypolicy.edit',
            'companypolicy.delete',
        ])->each(function (string $permission) use ($employee): void {
            $employee->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        });

        $this->actingAs($employee, 'web');

        return $employee;
    }

    private function createPolicyWithDetail(int $year, array $overrides = []): CompanyPolicy
    {
        $policy = CompanyPolicy::create(array_merge([
            'tahun' => $year,
            'nama_dokumen' => 'Company Policy FY' . $year,
            'file_path' => '0',
            'header' => '<h3>Original Header</h3>',
            'contents_en' => '<p>Original Contents EN</p>',
            'contents_id' => '<p>Original Contents ID</p>',
            'prologue_en' => '<p>Original Prologue EN</p>',
            'prologue_id' => '<p>Original Prologue ID</p>',
            'closing_en' => '<p>Original Closing EN</p>',
            'closing_id' => '<p>Original Closing ID</p>',
            'signature' => '<p>Original Signature</p>',
        ], $overrides));

        CompanyPolicyDetail::create([
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Original Goal',
            'description' => 'Original Description',
            'strategic_goal_id' => 'Tujuan Awal',
            'description_id' => 'Deskripsi Awal',
            'target' => '0',
        ]);

        return $policy->load('details');
    }

    private function companyPolicyPayload(int $year, array $overrides = []): array
    {
        return array_merge([
            'tahun' => $year,
            'company_name' => 'PT Integration Test',
            'place_date' => 'Jakarta, 1 January ' . $year,
            'document_title' => 'Company Policy Integration Test',
            'subtitle' => 'Fiscal Year ' . $year,
            'refer_to_en' => ['Regulation EN ' . $year],
            'refer_to_id' => ['Regulasi ID ' . $year],
            'considering_en' => ['Considering EN ' . $year],
            'considering_id' => ['Menimbang ID ' . $year],
            'decision_en' => 'Decision EN ' . $year,
            'decision_id' => 'Keputusan ID ' . $year,
            'background_en' => '<p>Background EN ' . $year . '</p>',
            'background_id' => '<p>Latar belakang ID ' . $year . '</p>',
            'prologue_en' => '<p>Prologue EN ' . $year . '</p>',
            'prologue_id' => '<p>Prolog ID ' . $year . '</p>',
            'closing_en' => '<p>Closing EN ' . $year . '</p>',
            'closing_id' => '<p>Penutup ID ' . $year . '</p>',
            'signature_position' => ['President Director'],
            'signature_name' => ['Jane Doe'],
            'company_policy_core_en' => ['Growth ' . $year],
            'company_policy_desc_en' => ['Grow responsibly ' . $year],
            'company_policy_core_id' => ['Pertumbuhan ' . $year],
            'company_policy_desc_id' => ['Tumbuh bertanggung jawab ' . $year],
        ], $overrides);
    }
}
