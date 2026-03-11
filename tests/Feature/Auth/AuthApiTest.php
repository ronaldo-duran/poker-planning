<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    #[Test]
    public function register_returns_user_and_token(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $data);

        // Assert
        $response->assertCreated()
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    #[Test]
    public function register_validates_required_fields(): void
    {
        // Act
        $response = $this->postJson('/api/register', []);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function register_validates_email_format(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $data);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function register_validates_unique_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/register', $data);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function login_returns_user_and_token(): void
    {
        // Arrange
        $email = 'login-api' . uniqid() . '@test.com';
        User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => $email,
            'password' => 'password123',
        ];

        // Act
        $response = $this->postJson('/api/login', $credentials);

        // Assert
        $response->assertOk()
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token',
                 ]);
    }

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        // Arrange
        $email = 'wrong-pass' . uniqid() . '@test.com';
        User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function login_fails_with_nonexistent_email(): void
    {
        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function logout_deletes_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Act
        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->postJson('/api/logout');

        // Assert
        $response->assertOk()
                 ->assertJson(['message' => 'Logged out successfully.']);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    #[Test]
    public function logout_requires_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertUnauthorized();
    }

    #[Test]
    public function me_returns_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Act
        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->getJson('/api/me');

        // Assert
        $response->assertOk()
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }

    #[Test]
    public function me_requires_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/me');

        // Assert
        $response->assertUnauthorized();
    }
}
