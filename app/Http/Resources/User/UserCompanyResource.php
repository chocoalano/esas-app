<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "radius" => $this->radius,
            "full_address" => $this->full_address,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
