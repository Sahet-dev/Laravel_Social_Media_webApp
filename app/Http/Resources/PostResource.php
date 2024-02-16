<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'body' => $this->body,
            'created_at' => $this->created_at->format('Y-m_d H:i'),
            'updated_at' => $this->updated_at->format('Y-m_d H:i'),
            'user' =>  $this->user,
            'group' => $this->group,
            'attachment' => $this->attachment
        ];
    }
}
