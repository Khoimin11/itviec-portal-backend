<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicJobPostingController extends Controller
{
    public function __invoke(Request $request, int $job): JsonResponse
    {
        $job = JobPosting::whereHas('company', fn ($query) => $query->where('status', 'active'))
            ->with(['skills', 'company.industry'])->findOrFail($job);
        $company = $job->company;

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => (new JobPostingResource($job))->resolve($request) + [
                'slug' => (string) $job->id,
                'company' => [
                    'id' => $company->id,
                    'slug' => $company->slug,
                    'companyName' => $company->company_name,
                    'tagline' => $company->tagline ?? '',
                    'logo' => $company->logo_url ?: ($company->logo_path ? Storage::disk('public')->url($company->logo_path) : ''),
                    'website' => $company->website ?? '',
                    'companyType' => $company->company_type ?? '',
                    'companySize' => $company->company_size ?? '',
                    'country' => $company->country ?? '',
                    'workingDay' => $company->working_day ?? '',
                    'overtimePolicy' => $company->overtime_policy ?? '',
                    'industry' => $company->industry?->only(['id', 'name_en', 'name_vi']),
                ],
            ],
        ]);
    }
}
