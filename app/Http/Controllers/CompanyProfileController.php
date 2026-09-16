<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\AccountCompanyInfo;
use App\Services\CompanyLogoStorage;
use App\Support\RichText;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->response($this->company($request));
    }

    public function update(UpdateCompanyProfileRequest $request, CompanyLogoStorage $logos): JsonResponse
    {
        $company = $this->company($request);
        $data = $request->validated();
        $newLogo = null;
        $oldLogo = null;

        try {
            if ($request->hasFile('logo')) {
                $newLogo = $logos->upload($request->file('logo'), $company->id);
            }

            DB::transaction(function () use (&$company, $data, $newLogo, &$oldLogo): void {
                $company = AccountCompanyInfo::lockForUpdate()->findOrFail($company->id);
                $attributes = [
                    'contact_name' => $data['username'], 'email' => $data['email'],
                    'phone_number' => $data['phoneNumber'], 'position' => $data['position'],
                    'company_name' => $data['companyName'], 'location' => $data['location'],
                    'website' => $data['website'] ?? null, 'tagline' => $data['tagline'] ?? null,
                    'company_type' => $data['companyType'], 'industry_id' => $data['industryId'],
                    'company_size' => $data['companySize'], 'country' => $data['country'],
                    'working_day' => $data['workingDay'], 'overtime_policy' => $data['overtimePolicy'],
                    'overview' => RichText::clean($data['overview'] ?? null),
                    'perks' => RichText::clean($data['perks'] ?? null),
                ];
                if ($newLogo) {
                    $oldLogo = ['public_id' => $company->logo_public_id, 'path' => $company->logo_path];
                    $attributes['logo_path'] = null;
                    $attributes['logo_url'] = $newLogo['url'];
                    $attributes['logo_public_id'] = $newLogo['public_id'];
                }
                $company->forceFill($attributes)->save();
                $company->skills()->sync($data['skillIds']);
            });
        } catch (Throwable $exception) {
            $logos->delete($newLogo);
            if ($exception instanceof UniqueConstraintViolationException
                && AccountCompanyInfo::where('email', $data['email'])->where('id', '!=', $company->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'Email đã được đăng ký cho tài khoản công ty khác.']);
            }
            throw $exception;
        }

        $logos->delete($oldLogo);

        return $this->response($company, 'Cập nhật hồ sơ thành công');
    }

    private function company(Request $request): AccountCompanyInfo
    {
        $company = $request->user();
        abort_unless($company instanceof AccountCompanyInfo && $company->status === 'active', 403);

        return $company;
    }

    private function response(AccountCompanyInfo $company, string $message = ''): JsonResponse
    {
        return response()->json([
            'isSuccess' => true, 'message' => $message,
            'data' => new CompanyProfileResource($company->load(['industry', 'skills'])),
        ]);
    }
}
