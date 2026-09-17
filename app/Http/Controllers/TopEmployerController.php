<?php

namespace App\Http\Controllers;

use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TopEmployerController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $companies = AccountCompanyInfo::query()
            ->where('status', 'active')
            ->has('jobPostings', '>=', 1)
            ->withCount('jobPostings')
            ->with('skills:id,name')
            ->orderByDesc('job_postings_count')
            ->orderBy('id')
            ->get();

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => $companies->map(fn (AccountCompanyInfo $company) => [
                'id' => $company->id,
                'slug' => $company->slug,
                'companyName' => $company->company_name,
                'location' => $company->location,
                'logo' => $company->logo_url ?: ($company->logo_path ? Storage::disk('public')->url($company->logo_path) : ''),
                'skills' => $company->skills->map(fn ($skill) => ['id' => $skill->id, 'name' => $skill->name]),
                'jobsCount' => $company->job_postings_count,
            ]),
        ]);
    }
}
