<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest users cannot access the change password page.
     */
    public function test_guest_cannot_access_change_password_page(): void
    {
        $response = $this->get(route('password.change'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated users can access the change password page.
     */
    public function test_authenticated_user_can_access_change_password_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.change'));

        $response->assertStatus(200);
        $response->assertSee('Change Password');
    }

    /**
     * Changing password fails if the current password is incorrect.
     */
    public function test_change_password_fails_if_current_password_incorrect(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->post(route('password.update'), [
            'current_password'      => 'wrong-current-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /**
     * Changing password fails if new password is too short.
     */
    public function test_change_password_fails_if_new_password_too_short(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->post(route('password.update'), [
            'current_password'      => 'old-password',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /**
     * Changing password fails if confirmation does not match.
     */
    public function test_change_password_fails_if_confirmation_does_not_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->post(route('password.update'), [
            'current_password'      => 'old-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'different-new-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /**
     * Changing password succeeds with valid credentials.
     */
    public function test_change_password_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->post(route('password.update'), [
            'current_password'      => 'old-password',
            'password'              => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
