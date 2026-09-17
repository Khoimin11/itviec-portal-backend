<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginApplicantRequest;
use App\Http\Requests\Auth\RegisterApplicantRequest;
use App\Http\Resources\Applicant\AccountUserResource;
use App\Models\AccountUser;
use App\Notifications\ApplicantRegistered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApplicantAuthController extends Controller
{
    public function register(RegisterApplicantRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $account = AccountUser::create([
                'name' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'terms_accepted_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            if (! AccountUser::where('email', $data['email'])->exists()) {
                throw $exception;
            }

            throw ValidationException::withMessages(['email' => 'Email đã được sử dụng.']);
        }

        try {
            $account->notify(new ApplicantRegistered);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đăng ký thành công',
            'data' => null,
        ], 201);
    }

    public function login(LoginApplicantRequest $request): JsonResponse
    {
        $data = $request->validated();
        $account = AccountUser::where('email', $data['email'])->first();

        if (! $account || ! Hash::check($data['password'], $account->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        if (Hash::needsRehash($account->password)) {
            $account->forceFill(['password' => Hash::make($data['password'])])->save();
        }

        $token = $account->createToken('applicant-web', ['applicant'], now()->addDay());

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'accessToken' => $token->plainTextToken,
                'user' => new AccountUserResource($account),
            ],
        ]);
    }
}
