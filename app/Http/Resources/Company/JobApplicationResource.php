<?php

namespace App\Http\Resources\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jobId' => $this->job_posting_id,
            'jobTitle' => $this->jobPosting->title,
            'jobEndDate' => $this->jobPosting->end_date->copy()->endOfDay()->toISOString(),
            'fullName' => $this->full_name,
            'phoneNumber' => $this->phone_number,
            'status' => $this->status,
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
            'deletedAt' => null,
        ];
    }
}
