<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobPostingResource;
use App\Models\AccountCompanyInfo;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyJobPostingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $company = $request->user();
        abort_unless($company instanceof AccountCompanyInfo && $company->status === 'active', 403);

        $jobs = JobPosting::where('company_id', $company->id)
            ->with('skills')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => JobPostingResource::collection($jobs),
        ]);
    }
}
