<?php

use App\Models\User;
use App\Models\Task;
use App\Enum\TaskStatus;

beforeEach(function () {
    $this->user = createUser();
    $this->otherUser = createUser();
});

describe('Task API', function () {
    describe('GET /api/tasks', function () {
        it('returns user tasks', function () {
            $user = actingAsUser();

            // Create tasks for the user
            $userTask1 = createTask(['created_by' => $user->id]);
            $userTask2 = createTask(['assigned_to' => $user->id]);

            // Create task for other user (should not be returned)
            createTask(['created_by' => $this->otherUser->id]);

            $response = $this->getJson('/api/tasks');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'status',
                            'status_label',
                            'due_date',
                            'created_at',
                            'updated_at',
                            'creator',
                            'assignee'
                        ]
                    ]
                ])
                ->assertJsonCount(2, 'data');
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/tasks');
            $response->assertStatus(401);
        });
    });

    describe('POST /api/tasks', function () {
        it('creates task successfully', function () {
            $user = actingAsUser();

            $taskData = [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => TaskStatus::PENDING->value,
                'assigned_to' => $this->otherUser->id,
                'due_date' => '2025-12-31'
            ];

            $response = $this->postJson('/api/tasks', $taskData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'creator',
                        'assignee'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Test Task',
                        'description' => 'Test Description'
                    ]
                ]);

            $this->assertDatabaseHas('tasks', [
                'title' => 'Test Task',
                'created_by' => $user->id,
                'assigned_to' => $this->otherUser->id
            ]);
        });

        it('validates required fields', function () {
            actingAsUser();

            $response = $this->postJson('/api/tasks', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/tasks', []);
            $response->assertStatus(401);
        });
    });

    describe('GET /api/tasks/{id}', function () {
        it('gets task by id successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            $response = $this->getJson("/api/tasks/{$task->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $task->id,
                        'title' => $task->title
                    ]
                ]);
        });

        it('requires authentication', function () {
            $task = createTask();
            $response = $this->getJson("/api/tasks/{$task->id}");
            $response->assertStatus(401);
        });
    });

    describe('PUT /api/tasks/{id}', function () {
        it('updates task successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            $updateData = [
                'title' => 'Updated Task Title',
                'description' => 'Updated Description'
            ];

            $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $task->id,
                        'title' => 'Updated Task Title',
                        'description' => 'Updated Description'
                    ]
                ]);

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'title' => 'Updated Task Title',
                'description' => 'Updated Description'
            ]);
        });


        it('requires authentication', function () {
            $task = createTask();
            $response = $this->putJson("/api/tasks/{$task->id}", []);
            $response->assertStatus(401);
        });
    });

    describe('DELETE /api/tasks/{id}', function () {
        it('deletes task successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            $response = $this->deleteJson("/api/tasks/{$task->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Task deleted successfully.'
                ]);

            $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        });

        it('requires authentication', function () {
            $task = createTask();
            $response = $this->deleteJson("/api/tasks/{$task->id}");
            $response->assertStatus(401);
        });
    });

    describe('GET /api/users/{id}/tasks', function () {
        it('gets user tasks successfully', function () {
            $user = actingAsUser();

            createTask(['created_by' => $user->id]);
            createTask(['assigned_to' => $user->id]);

            $response = $this->getJson("/api/users/{$user->id}/tasks");

            $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
        });

        it('requires authentication', function () {
            $response = $this->getJson("/api/users/1/tasks");
            $response->assertStatus(401);
        });
    });
});
