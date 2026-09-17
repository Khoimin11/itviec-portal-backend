<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Models\AccountCompanyInfo;
use App\Notifications\CompanyRegistered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterCompanyController extends Controller
{
    public function __invoke(RegisterCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data): void {
                $password = Str::password(20);
                $company = AccountCompanyInfo::create([
                    'contact_name' => $data['username'],
                    'position' => $data['position'],
                    'email' => $data['email'],
                    'phone_number' => $data['phoneNumber'],
                    'source' => $data['source'] ?? null,
                    'company_name' => $data['companyName'],
                    'location' => $data['location'],
                    'website' => $data['website'] ?? null,
                    'status' => 'active',
                    'terms_accepted_at' => now(),
                    'password' => $password,
                ]);

                $company->notify(new CompanyRegistered($password));
            });
        } catch (UniqueConstraintViolationException $exception) {
            Validator::make($data, $request->rules(), $request->messages(), $request->attributes())->validate();
            throw $exception;
        }

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đăng ký công ty thành công. Vui lòng kiểm tra email để nhận thông tin đăng nhập.',
            'data' => null,
        ], 201);
    }
}
