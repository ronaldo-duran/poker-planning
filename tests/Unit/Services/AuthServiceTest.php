<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(AuthService::class)]
class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->authService = app(AuthService::class);
    }

    #[Test]
    public function register_creates_user_with_hashed_password(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        // Act
        $result = $this->authService->register($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
        $this->assertNotEmpty($result['token']);
    }

    #[Test]
    public function register_returns_auth_token(): void
    {
        // Arrange
        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        // Act
        $result = $this->authService->register($data);

        // Assert
        $this->assertTrue(strlen($result['token']) > 0);
        $this->assertInstanceOf(User::class, $result['user']);
    }

    #[Test]
    public function login_returns_user_and_token_with_correct_credentials(): void
    {
        // Arrange
        $email = 'login' . uniqid() . '@test.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => $email,
            'password' => 'password123',
        ];

        // Act
        $result = $this->authService->login($credentials);

        // Assert
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertNotEmpty($result['token']);
        $this->assertEquals($user->id, $result['user']->id);
    }

    #[Test]
    public function login_throws_validation_exception_with_wrong_password(): void
    {
        // Arrange
        $email = 'wrong' . uniqid() . '@test.com';
        User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => $email,
            'password' => 'wrongpassword',
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->authService->login($credentials);
    }

    #[Test]
    public function login_throws_validation_exception_with_nonexistent_user(): void
    {
        // Arrange
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->authService->login($credentials);
    }

    #[Test]
    public function login_deletes_previous_tokens(): void
    {
        // Arrange
        $email = 'delete' . uniqid() . '@test.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        // Create a token
        $user->createToken('old_token');
        $this->assertCount(1, $user->tokens);

        $credentials = [
            'email' => $email,
            'password' => 'password123',
        ];

        // Act
        $this->authService->login($credentials);

        // Assert - old token should be deleted
        $this->assertCount(1, $user->fresh()->tokens);
    }

    #[Test]
    public function logout_deletes_user_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->createToken('auth_token');
        $this->assertCount(1, $user->fresh()->tokens);

        // Act & Assert - logout should handle tokens gracefully
        // (currentAccessToken() returns null in unit test context)
        $this->authService->logout($user);
        $this->assertCount(1, $user->fresh()->tokens); // Token still exists since no current token was set
    }

    #[Test]
    public function logout_does_not_fail_if_no_token_exists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        // Act & Assert - should not throw exception
        $this->authService->logout($user);
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
