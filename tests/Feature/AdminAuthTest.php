<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('admin-login|bangnahan@gmail.com|127.0.0.1');

        $this->adminUser = User::create([
            'name' => 'Bang Nahan',
            'email' => 'bangnahan@gmail.com',
            'password' => Hash::make('pekanbaru12'),
            'role' => 'SUPER_ADMIN',
        ]);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200)
            ->assertSee('LOGIN ADMIN VIRA')
            ->assertSee('Alamat Email Admin')
            ->assertSee('Kata Sandi');
    }

    public function test_authenticated_admin_visiting_login_page_is_redirected_to_admin_events(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/login');

        $response->assertRedirect('/admin/events');
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'bangnahan@gmail.com',
            'password' => 'pekanbaru12',
            'remember' => '1',
        ]);

        $response->assertRedirect('/admin/events');
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_admin_cannot_login_with_incorrect_password(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'bangnahan@gmail.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $regularUser = User::create([
            'name' => 'Regular Runner',
            'email' => 'runner@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'PARTICIPANT',
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'runner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin/events');

        $response->assertRedirect('/admin/login');
    }

    public function test_regular_user_directly_accessing_admin_routes_receives_403_forbidden(): void
    {
        $regularUser = User::create([
            'name' => 'Regular Runner',
            'email' => 'runner2@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'PARTICIPANT',
        ]);

        $response = $this->actingAs($regularUser)->get('/admin/events');

        $response->assertStatus(403);
    }

    public function test_authenticated_admin_can_access_admin_routes(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/events');

        $response->assertStatus(200);
    }

    public function test_rate_limiter_throttles_excessive_failed_login_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/admin/login')->post('/admin/login', [
                'email' => 'bangnahan@gmail.com',
                'password' => 'wrong-password-'.$i,
            ]);
        }

        // Percobaan ke-6 harus di-throttle
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'bangnahan@gmail.com',
            'password' => 'wrong-password-6',
        ]);

        $response->assertSessionHasErrors('email');
        $error = session('errors')->first('email');
        $this->assertStringContainsString('Terlalu banyak percobaan masuk', $error);
    }

    public function test_authenticated_admin_can_logout(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
