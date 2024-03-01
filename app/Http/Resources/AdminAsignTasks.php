<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAsignTasks extends JsonResource
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
            'admin_id' => $this->admin_id,
            'created_at' => $this->created_at->diffForHumans(),
            'deadline' => $this->task ? $this->task->deadline : $this->deadline,
            'description' => $this->task ? $this->task->description : $this->description,
            'priority' => $this->task ? $this->task->priority : $this->priority,
            'status' => $this->status,
            'title' => $this->task ? $this->task->title : $this->title,
            'progress' => $this->task?->progress,
            'user' => $this->user
        ];
    }
}
