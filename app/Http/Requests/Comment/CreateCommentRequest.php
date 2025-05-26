<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateCommentRequest extends FormRequest
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
            'content' => ['required', 'string', 'max:1000'],
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'The comment content is required.',
            'content.max' => 'The comment content may not be greater than 1000 characters.',
            'task_id.required' => 'The task ID is required.',
            'task_id.exists' => 'The selected task does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'task_id' => 'task',
        ];
    }
}
