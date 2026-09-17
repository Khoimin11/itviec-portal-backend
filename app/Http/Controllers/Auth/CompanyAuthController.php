<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangeCompanyPasswordRequest;
use App\Http\Requests\Auth\LoginCompanyRequest;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Http\Resources\Company\AccountCompanyResource;
use App\Models\AccountCompanyInfo;
use App\Notifications\CompanyRegistered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyAuthController extends Controller
{
    public function register(RegisterCompanyRequest $request): JsonResponse
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

    public function login(LoginCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $company = AccountCompanyInfo::where('email', $data['email'])->first();

        if (! $company || $company->status !== 'active' || ! $company->password
            || ! Hash::check($data['password'], $company->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng, hoặc tài khoản chưa hoạt động.',
            ]);
        }

        if (Hash::needsRehash($company->password)) {
            $company->forceFill(['password' => Hash::make($data['password'])])->save();
        }

        $token = $company->createToken('company-web', ['company'], now()->addDay());

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'accessToken' => $token->plainTextToken,
                'user' => new AccountCompanyResource($company),
            ],
        ]);
    }

    public function changePassword(ChangeCompanyPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data): void {
            $company = AccountCompanyInfo::lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($company->status === 'active', 403);

            if (! $company->password || ! Hash::check($data['currentPassword'], $company->password)) {
                throw ValidationException::withMessages([
                    'currentPassword' => 'Mật khẩu hiện tại không đúng.',
                ]);
            }

            $company->forceFill(['password' => $data['newPassword']])->save();
        });

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đổi mật khẩu thành công.',
            'data' => true,
        ]);
    }
}
