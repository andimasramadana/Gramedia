<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IsAdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_with_role_user_cannot_access_admin_dashboard_and_is_redirected_to_home_with_error(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'anda tidak memilik izin');
        $this->get(route('home'))->assertSee('anda tidak memilik izin');
    }

    public function test_user_with_role_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dasboard');
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'anda tidak memilik izin');
        $this->get(route('home'))->assertSee('anda tidak memilik izin');
    }
}
