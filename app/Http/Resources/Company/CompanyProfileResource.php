<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->contact_name,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'position' => $this->position,
            'companyName' => $this->company_name,
            'location' => $this->location,
            'website' => $this->website ?? '',
            'tagline' => $this->tagline ?? '',
            'companyType' => $this->company_type ?? '',
            'industryId' => $this->industry_id,
            'industry' => $this->industry,
            'companySize' => $this->company_size ?? '',
            'country' => $this->country ?? '',
            'workingDay' => $this->working_day ?? '',
            'overtimePolicy' => $this->overtime_policy ?? '',
            'overview' => $this->overview ?? '',
            'perks' => $this->perks ?? '',
            'skills' => $this->skills,
            'logo' => $this->logo_url ?: ($this->logo_path ? Storage::disk('public')->url($this->logo_path) : ''),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
