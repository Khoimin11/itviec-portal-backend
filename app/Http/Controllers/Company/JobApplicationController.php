<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\JobApplicationResource;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $applications = JobApplication::whereHas('jobPosting', fn ($query) => $query
            ->where('company_id', $request->user()->id))
            ->with('jobPosting:id,title,end_date')
            ->latest('id')
            ->get();

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => JobApplicationResource::collection($applications),
        ]);
    }
}
