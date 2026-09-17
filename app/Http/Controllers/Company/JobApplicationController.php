<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\JobApplicationResource;
use App\Models\JobApplication;
use App\Services\CvStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function show(Request $request, int $application, CvStorage $storage): JsonResponse
    {
        $application = JobApplication::whereHas('jobPosting', fn ($query) => $query
            ->where('company_id', $request->user()->id))
            ->with('jobPosting:id,title,end_date')
            ->findOrFail($application);

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => (new JobApplicationResource($application))->resolve($request) + [
                'email' => $application->email,
                'locations' => $application->locations,
                'coverLetter' => $application->cover_letter ?? '',
                'cvName' => $application->cv_original_name,
                'cvUrl' => $storage->temporaryUrl($application->cv_public_id),
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

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
