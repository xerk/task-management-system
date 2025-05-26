<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Auth API', function () {
    describe('POST /api/auth/register', function () {
        it('registers user successfully', function () {
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User registered successfully.',
                    'data' => [
                        'user' => [
                            'name' => 'Test User',
                            'email' => 'test@example.com'
                        ]
                    ]
                ]);

            $this->assertDatabaseHas('users', [
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]);

            // Verify password is hashed
            $user = User::where('email', 'test@example.com')->first();
            expect(Hash::check('password123', $user->password))->toBeTrue();
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/auth/register', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        it('validates duplicate email', function () {
            createUser(['email' => 'test@example.com']);

            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ];

            $response = $this->postJson('/api/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('POST /api/auth/login', function () {
        it('logs in user successfully', function () {
            $user = createUser([
                'email' => 'test@example.com',
                'password' => Hash::make('password123')
            ]);

            $loginData = [
                'email' => 'test@example.com',
                'password' => 'password123'
            ];

            $response = $this->postJson('/api/auth/login', $loginData);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Login successful.',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'email' => 'test@example.com'
                        ]
                    ]
                ]);
        });

        it('fails with invalid credentials', function () {
            createUser([
                'email' => 'test@example.com',
                'password' => Hash::make('password123')
            ]);

            $loginData = [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ];

            $response = $this->postJson('/api/auth/login', $loginData);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/auth/login', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
        });
    });

    describe('POST /api/auth/logout', function () {
        it('logs out user successfully', function () {
            $user = actingAsUser();

            $response = $this->postJson('/api/auth/logout');

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logged out successfully.',
                    'data' => null
                ]);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/auth/logout');
            $response->assertStatus(401);
        });
    });

    describe('GET /api/user', function () {
        it('gets authenticated user profile', function () {
            $user = createUser([
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]);

            $response = $this->actingAs($user)->getJson('/api/user');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User retrieved successfully.',
                    'data' => [
                        'id' => $user->id,
                        'name' => 'Test User',
                        'email' => 'test@example.com'
                    ]
                ]);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/user');
            $response->assertStatus(401);
        });
    });

    describe('session-based authentication flow', function () {
        it('completes full authentication flow', function () {
            // Register a user
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ];

            $registerResponse = $this->postJson('/api/auth/register', $userData);
            $registerResponse->assertStatus(201);

            // Login
            $loginData = [
                'email' => 'test@example.com',
                'password' => 'password123'
            ];

            $loginResponse = $this->postJson('/api/auth/login', $loginData);
            $loginResponse->assertStatus(200);

            // Access protected route
            $profileResponse = $this->getJson('/api/user');
            $profileResponse->assertStatus(200);

            // Logout
            $logoutResponse = $this->postJson('/api/auth/logout');
            $logoutResponse->assertStatus(200);
        });
    });
});
