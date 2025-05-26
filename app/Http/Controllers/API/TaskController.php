<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Contracts\TaskServiceInterface;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAllTasks();

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully.',
            'data' => TaskResource::collection($tasks)
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'data' => new TaskResource($task)
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id);

        return response()->json([
            'success' => true,
            'message' => 'Task retrieved successfully.',
            'data' => new TaskResource($task)
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'data' => new TaskResource($updatedTask)
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.',
            'data' => null
        ]);
    }

    /**
     * Get tasks for a specific user.
     */
    public function userTasks(int $userId): JsonResponse
    {
        $tasks = $this->taskService->getAllTasksByUserId($userId);

        return response()->json([
            'success' => true,
            'message' => 'User tasks retrieved successfully.',
            'data' => TaskResource::collection($tasks)
        ]);
    }
}
