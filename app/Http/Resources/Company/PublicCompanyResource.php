<?php

namespace App\Http\Resources\Company;

use App\Http\Resources\Job\JobPostingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $company = [
            'id' => $this->id,
            'slug' => $this->slug,
            'companyName' => $this->company_name,
            'logo' => $this->logo_url ?: ($this->logo_path ? Storage::disk('public')->url($this->logo_path) : ''),
            'location' => $this->location,
        ];

        return $company + [
            'companyType' => $this->company_type ?? '',
            'companySize' => $this->company_size ?? '',
            'country' => $this->country ?? '',
            'workingDay' => $this->working_day ?? '',
            'overtimePolicy' => $this->overtime_policy ?? '',
            'overview' => $this->overview ?? '',
            'perks' => $this->perks ?? '',
            'website' => $this->website ?? '',
            'industry' => $this->industry?->only(['id', 'name_en', 'name_vi']),
            'skills' => $this->skills->map(fn ($skill) => $skill->only(['id', 'name'])),
            'jobs' => $this->jobPostings->map(fn ($job) => (new JobPostingResource($job))->resolve($request) + [
                'slug' => (string) $job->id,
                'company' => $company,
            ]),
        ];
    }
}
