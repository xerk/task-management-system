<?php

use App\Models\User;
use App\Models\Task;
use App\Models\Comment;

beforeEach(function () {
    $this->user = createUser();
    $this->otherUser = createUser();
    $this->task = createTask(['created_by' => $this->user->id]);
});

describe('Comment API', function () {
    describe('GET /api/tasks/{taskId}/comments', function () {
        it('returns comments for task', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            // Create comments for the task
            createComment(['task_id' => $task->id, 'user_id' => $user->id]);
            createComment(['task_id' => $task->id, 'user_id' => $user->id]);

            // Create comment for other task (should not be returned)
            $otherTask = createTask(['created_by' => $user->id]);
            createComment(['task_id' => $otherTask->id, 'user_id' => $user->id]);

            $response = $this->getJson("/api/tasks/{$task->id}/comments");


            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJsonCount(2, 'data');
        });

        it('requires authentication', function () {
            $response = $this->getJson("/api/tasks/{$this->task->id}/comments");
            $response->assertStatus(401);
        });
    });

    describe('POST /api/comments', function () {
        it('creates comment successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            $commentData = [
                'task_id' => $task->id,
                'content' => 'This is a test comment'
            ];

            $response = $this->postJson('/api/comments', $commentData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'content',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'content' => 'This is a test comment',
                    ]
                ]);

            $this->assertDatabaseHas('comments', [
                'content' => 'This is a test comment',
            ]);
        });

        it('validates required fields', function () {
            actingAsUser();

            $response = $this->postJson('/api/comments', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['task_id', 'content']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/comments', []);
            $response->assertStatus(401);
        });
    });

    describe('GET /api/comments/{id}', function () {
        it('gets comment by id successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);
            $comment = createComment(['task_id' => $task->id, 'user_id' => $user->id]);

            $response = $this->getJson("/api/comments/{$comment->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $comment->id,
                        'content' => $comment->content
                    ]
                ]);
        });

        it('requires authentication', function () {
            $comment = createComment();
            $response = $this->getJson("/api/comments/{$comment->id}");
            $response->assertStatus(401);
        });
    });

    describe('PUT /api/comments/{id}', function () {
        it('updates comment successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);
            $comment = createComment(['task_id' => $task->id, 'user_id' => $user->id]);

            $updateData = [
                'content' => 'Updated comment content'
            ];

            $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $comment->id,
                        'content' => 'Updated comment content'
                    ]
                ]);

            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'content' => 'Updated comment content'
            ]);
        });


        it('requires authentication', function () {
            $comment = createComment();
            $response = $this->putJson("/api/comments/{$comment->id}", []);
            $response->assertStatus(401);
        });
    });

    describe('DELETE /api/comments/{id}', function () {
        it('deletes comment successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);
            $comment = createComment(['task_id' => $task->id, 'user_id' => $user->id]);

            $response = $this->deleteJson("/api/comments/{$comment->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Comment deleted successfully.'
                ]);

            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        });

        it('fails for unauthorized user', function () {
            actingAsUser();
            $task = createTask(['created_by' => $this->otherUser->id]);
            $comment = createComment(['task_id' => $task->id, 'user_id' => $this->otherUser->id]);

            $response = $this->deleteJson("/api/comments/{$comment->id}");

            $response->assertStatus(422)
                ->assertJson([
                    'message' => "You do not have permission to delete this comment.",
                    'errors' => [
                        'comment' => ['You do not have permission to delete this comment.']
                    ]
                ]);

            $this->assertDatabaseHas('comments', ['id' => $comment->id]);
        });

        it('requires authentication', function () {
            $comment = createComment();
            $response = $this->deleteJson("/api/comments/{$comment->id}");
            $response->assertStatus(401);
        });
    });

    describe('GET /api/users/{id}/comments', function () {
        it('gets user comments successfully', function () {
            $user = actingAsUser();
            $task = createTask(['created_by' => $user->id]);

            createComment(['task_id' => $task->id, 'user_id' => $user->id]);
            createComment(['task_id' => $task->id, 'user_id' => $user->id]);

            $response = $this->getJson("/api/users/{$user->id}/comments");

            $response->assertStatus(200)
                ->assertJsonCount(2, 'data');
        });

        it('requires authentication', function () {
            $response = $this->getJson("/api/users/1/comments");
            $response->assertStatus(401);
        });
    });
});
