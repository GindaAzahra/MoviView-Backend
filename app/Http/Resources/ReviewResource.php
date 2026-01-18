<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_review' => $this->id_review,
            'id_user' => $this->id_user,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id_user' => $this->user->id_user,
                    'name' => $this->user->name,
                ];
            }),
            'id_movie' => $this->id_movie,
            'rating' => $this->rating,
            'review' => $this->review,
            'created_at' => $this->created_at,
        ];
    }
}
