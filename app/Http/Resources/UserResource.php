<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    protected $token;

    public function withToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    public function toArray(Request $request): array
    {

        return [
            'id_user' => $this->id_user,
            'name' => $this->name,
            'email' => $this->email,
            'token' => $this->when($this->token, $this->token),
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
