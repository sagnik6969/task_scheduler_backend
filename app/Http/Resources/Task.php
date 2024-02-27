<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Task extends JsonResource
{

    /**
     * The state of the task resource.
     *
     * @var string
     */
    protected $state;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $state
     * @return void
     */
    public function __construct($resource, string $state)
    {
        parent::__construct($resource);
        $this->state = $state;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'type' => $this->state,
                'task_id' => $this->id,
                'attributes' => [
                    'title' => $this->title,
                    'description' => $this->description,
                    'deadline' => $this->deadline,
                    'is_completed' => $this->is_completed ? true : false,
                    'progress' => $this->progress,
                    'priority' => $this->priority,
                    'user_id' => $this->user_id,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ],
            ],
        ];
    }
}
