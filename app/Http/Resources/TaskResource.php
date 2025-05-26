<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
        ];
    }
}
