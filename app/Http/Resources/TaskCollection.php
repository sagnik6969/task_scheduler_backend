<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    /**
     * The state of the task collection.
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
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request)
    {
        return [
            'type' => $this->state,
            'data' => $this->collection,
            'links' => [
                'self' => url('/tasks'),
            ]
        ];
    }
}
