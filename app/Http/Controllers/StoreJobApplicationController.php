<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Services\CvStorage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreJobApplicationController extends Controller
{
    public function __invoke(StoreJobApplicationRequest $request, int $job, CvStorage $storage): JsonResponse
    {
        $posting = JobPosting::findOrFail($job);
        $this->ensureOpen($posting);
        $applicant = $request->user();
        if (JobApplication::where('job_posting_id', $job)->where('applicant_id', $applicant->id)->exists()) {
            throw ValidationException::withMessages(['cv' => 'Bạn đã ứng tuyển việc làm này.']);
        }

        $data = $request->validated();
        $file = $request->file('cv');
        $publicId = $storage->upload($file, $applicant->id);

        try {
            $application = DB::transaction(function () use ($job, $applicant, $data, $file, $publicId): JobApplication {
                $this->ensureOpen(JobPosting::lockForUpdate()->findOrFail($job));
                $application = new JobApplication;
                $application->forceFill([
                    'job_posting_id' => $job,
                    'applicant_id' => $applicant->id,
                    'full_name' => $data['fullName'],
                    'email' => $applicant->email,
                    'phone_number' => $data['phoneNumber'],
                    'locations' => $data['locations'],
                    'cover_letter' => $data['coverLetter'] ?? null,
                    'cv_public_id' => $publicId,
                    'cv_original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'cv_mime_type' => $file->getMimeType(),
                    'cv_size' => $file->getSize(),
                    'status' => 'pending',
                ])->save();

                return $application;
            });
        } catch (Throwable $exception) {
            $storage->delete($publicId);
            if ($exception instanceof UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['cv' => 'Bạn đã ứng tuyển việc làm này.']);
            }
            throw $exception;
        }

        return response()->json([
            'isSuccess' => true,
            'message' => 'Gửi CV thành công.',
            'data' => ['id' => $application->id, 'jobId' => $job, 'createdAt' => $application->created_at->toISOString()],
        ], 201);
    }

    private function ensureOpen(JobPosting $job): void
    {
        if ($job->company?->status !== 'active' || $job->start_date->gt(today()) || $job->end_date->lt(today())) {
            throw ValidationException::withMessages(['cv' => 'Việc làm này hiện không nhận hồ sơ ứng tuyển.']);
        }
    }
}
