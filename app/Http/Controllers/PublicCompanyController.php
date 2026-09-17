<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicCompanyResource;
use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;

class PublicCompanyController extends Controller
{
    public function __invoke(string $slug): JsonResponse
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
