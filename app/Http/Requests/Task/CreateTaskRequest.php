<?php

namespace App\Http\Requests\Task;

use App\Enum\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'due_date' => ['nullable', 'date', 'after:today'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'description.max' => 'The task description may not be greater than 1000 characters.',
            'due_date.after' => 'The due date must be a future date.',
            'assigned_to.exists' => 'The selected user does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'assigned_to' => 'assignee',
        ];
    }
}
