<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_api_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'Postman',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'トークンを発行しました。')
            ->assertJsonStructure([
                'message',
                'token',
            ]);

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'Postman',
        ]);
    }

    public function test_wrong_password_cannot_create_token(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'Postman',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_nonexistent_email_cannot_create_token(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'unknown@example.com',
            'password' => 'password123',
            'device_name' => 'Postman',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_email_is_required(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'password' => 'password123',
            'device_name' => 'Postman',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_password_is_required(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'device_name' => 'Postman',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_device_name_is_required(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('device_name');
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'invalid-email',
            'password' => 'password123',
            'device_name' => 'Postman',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_device_name_cannot_exceed_255_characters(): void
    {
        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => str_repeat('a', 256),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('device_name');
    }

    public function test_get_method_is_not_allowed(): void
    {
        $response = $this->getJson('/api/v1/tokens');

        $response->assertMethodNotAllowed();
    }

    public function test_issued_token_can_access_authenticated_book_api(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $tokenResponse = $this->postJson('/api/v1/tokens', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'Postman',
        ]);

        $tokenResponse->assertCreated();

        $token = $tokenResponse->json('token');

        $bookResponse = $this->withToken($token)
            ->postJson('/api/v1/books', []);

        // トークン認証は成功し、書籍登録のバリデーションで422になる
        $bookResponse->assertUnprocessable()
            ->assertJsonValidationErrors([
                'user_id',
                'title',
                'author',
                'isbn',
                'published_date',
                'genres',
            ]);
    }
}
