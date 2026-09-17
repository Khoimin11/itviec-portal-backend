<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanyProfileRequest;
use App\Http\Resources\Company\CompanyProfileResource;
use App\Models\AccountCompanyInfo;
use App\Services\CompanyLogoStorage;
use App\Support\RichText;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->response($request->user());
    }

    public function update(UpdateCompanyProfileRequest $request, CompanyLogoStorage $logos): JsonResponse
    {
        $company = $request->user();
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
            if ($exception instanceof UniqueConstraintViolationException) {
                Validator::make($data, $request->rules(), $request->messages(), $request->attributes())->validate();
            }
            throw $exception;
        }

        $logos->delete($oldLogo);

        return $this->response($company, 'Cập nhật hồ sơ thành công');
    }

    private function response(AccountCompanyInfo $company, string $message = ''): JsonResponse
    {
        return response()->json([
            'isSuccess' => true, 'message' => $message,
            'data' => new CompanyProfileResource($company->load(['industry', 'skills'])),
        ]);
    }
}
