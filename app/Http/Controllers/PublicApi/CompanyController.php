<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\PublicCompanyResource;
use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $company = AccountCompanyInfo::where('slug', $slug)->where('status', 'active')
            ->with([
                'industry', 'skills',
                'jobPostings' => fn ($query) => $query
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())
                    ->with('skills')->latest('id'),
            ])->firstOrFail();

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => new PublicCompanyResource($company),
        ]);
    }
}
