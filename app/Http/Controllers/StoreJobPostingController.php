<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobPostingRequest;
use App\Models\JobPosting;
use App\Support\RichText;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StoreJobPostingController extends Controller
{
    public function __invoke(StoreJobPostingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $job = DB::transaction(function () use ($request, $data): JobPosting {
            $job = new JobPosting;
            $job->forceFill([
                'company_id' => $request->user()->id,
                'title' => $data['title'],
                'label' => $data['label'] ?? null,
                'currency_salary' => $data['currencySalary'],
                'min_salary' => $data['minSalary'],
                'max_salary' => $data['maxSalary'],
                'level' => $data['level'],
                'working_model' => $data['workingModel'],
                'location' => $data['location'],
                'address' => $data['address'] ?? null,
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'description' => RichText::clean($data['description'] ?? null),
                'requirement' => RichText::clean($data['requirement'] ?? null),
                'reason' => RichText::clean($data['reason'] ?? null),
            ])->save();
            $job->skills()->attach($data['skillIds']);

            return $job;            
        });

        return response()->json([
            'isSuccess' => true,
            'message' => 'Thêm việc làm thành công.',
            'data' => ['id' => $job->id],
        ], 201);
    }
}
