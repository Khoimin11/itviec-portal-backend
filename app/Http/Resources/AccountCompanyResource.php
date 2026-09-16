<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->contact_name,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'avatar' => '',
            'loginType' => 'EMAIL',
            'role' => 'COMPANY',
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'deletedAt' => null,
        ];
    }
}
