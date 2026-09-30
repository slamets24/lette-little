<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_cannot_manage_admin_users(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.users.store'), $this->payload())->assertForbidden();
    }

    public function test_superadmin_can_view_admin_users(): void
    {
        $superadmin = User::factory()->superAdmin()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);
        User::factory()->admin()->create([
            'name' => 'Ops Admin',
            'email' => 'ops@example.com',
        ]);

        $this->actingAs($superadmin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/Index')
                ->has('users', 2)
                ->where('stats.admins', 1)
                ->where('stats.superadmins', 1)
                ->where('users.0.role', 'superadmin')
            );
    }

    public function test_superadmin_can_create_admin_user(): void
    {
        $superadmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superadmin)
            ->post(route('admin.users.store'), $this->payload());

        $created = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($created->is_admin);
        $this->assertFalse($created->is_super_admin);
        $this->assertNotNull($created->email_verified_at);
    }

    public function test_database_seeder_creates_initial_superadmin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertSame('Super Admin', $user->name);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->is_super_admin);
    }

    /**
     * @return array<string, string>
     */
    private function payload(): array
    {
        return [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }
}
