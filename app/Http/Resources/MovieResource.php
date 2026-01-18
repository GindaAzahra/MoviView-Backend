<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base_url_image = config('tmdb.base_url_image');
        $data = $this->resource;

        return [
            'id' => $data['id'] ?? null,
            'original_title' => $data['original_title'] ?? null,
            'overview' => $data['overview'] ?? null,
           'poster_path' => $base_url_image . ($data['poster_path'] ?? ''),
'backdrop_path' => $base_url_image . ($data['backdrop_path'] ?? ''),
            'release_date' => $data['release_date'] ?? null,
            'vote_average' => $data['vote_average'] ?? null,
            'vote_count' => $data['vote_count'] ?? null,
            'popularity' => $data['popularity'] ?? null,
        ];

    }
}
