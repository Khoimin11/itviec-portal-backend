<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginCompanyRequest;
use App\Http\Resources\AccountCompanyResource;
use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginCompanyController extends Controller
{
    public function __invoke(LoginCompanyRequest $request): JsonResponse
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
}
