<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $today = today()->toDateString();
        $counts = JobPosting::where('company_id', $request->user()->id)
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(start_date <= ? AND end_date >= ?), 0) AS active, COALESCE(SUM(end_date < ?), 0) AS expired', [$today, $today, $today])
            ->first();
        $cvCounts = JobApplication::whereHas('jobPosting', fn ($query) => $query
            ->where('company_id', $request->user()->id))
            ->selectRaw("COUNT(*) AS total, COALESCE(SUM(status = 'accepted'), 0) AS accepted, COALESCE(SUM(status = 'pending'), 0) AS pending")
            ->first();

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => [
                'job' => [
                    'totalJobs' => (int) $counts->total,
                    'jobActive' => (int) $counts->active,
                    'jobExpired' => (int) $counts->expired,
                ],
                'cv' => [
                    'totalCVs' => (int) $cvCounts->total,
                    'cvAccepted' => (int) $cvCounts->accepted,
                    'cvPending' => (int) $cvCounts->pending,
                ],
            ],
        ]);
    }
}
