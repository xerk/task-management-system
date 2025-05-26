<?php

use App\Models\User;
use App\Models\Task;
use App\Services\TaskService;
use App\Enum\TaskStatus;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->taskService = app(TaskService::class);
});

test('it creates task successfully', function () {
    $taskData = [
        'title' => 'Test Task',
        'description' => 'Test Description',
        'due_date' => '2025-05-31',
    ];

    $task = $this->taskService->createTask($taskData);

    expect($task)->toBeInstanceOf(Task::class);
    expect($task->title)->toBe('Test Task');
    expect($task->created_by)->toBe($this->user->id);
    expect($task->status)->toBe(TaskStatus::PENDING);
});

test('it updates task successfully', function () {
    $task = Task::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $updatedTask = $this->taskService->updateTask($task, [
        'title' => 'Updated Task',
    ]);

    expect($updatedTask->title)->toBe('Updated Task');
});

test('it deletes task successfully', function () {
    $task = Task::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $result = $this->taskService->deleteTask($task);

    expect($result)->toBeTrue();
    expect(Task::find($task->id))->toBeNull();
});

test('it gets task by id', function () {
    $task = Task::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $foundTask = $this->taskService->getTaskById($task->id);

    expect($foundTask->id)->toBe($task->id);
});

test('it gets all tasks for user', function () {
    Task::factory()->count(3)->create([
        'created_by' => $this->user->id,
    ]);

    $tasks = $this->taskService->getAllTasks();

    expect($tasks)->toHaveCount(3);
});
