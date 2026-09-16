<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyProfileResource;
use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $company = $request->user();
        abort_unless($company instanceof AccountCompanyInfo && $company->status === 'active', 403);

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => new CompanyProfileResource($company->load(['industry', 'skills'])),
        ]);
    }
}
