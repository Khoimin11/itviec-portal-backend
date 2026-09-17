<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'label' => $this->label ?? '',
            'currencySalary' => $this->currency_salary,
            'minSalary' => $this->min_salary,
            'maxSalary' => $this->max_salary,
            'level' => $this->level,
            'workingModel' => $this->working_model,
            'location' => $this->location,
            'address' => $this->address ?? '',
            'startDate' => $this->start_date->format('Y-m-d'),
            'endDate' => $this->end_date->format('Y-m-d'),
            'description' => $this->description ?? '',
            'requirement' => $this->requirement ?? '',
            'reason' => $this->reason ?? '',
            'skills' => $this->skills->map(fn ($skill) => ['id' => $skill->id, 'name' => $skill->name]),
            'skillIds' => $this->skills->modelKeys(),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
            'deletedAt' => null,
        ];
    }
}
