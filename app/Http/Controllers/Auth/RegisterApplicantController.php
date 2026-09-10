<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterApplicantRequest;
use App\Models\AccountUser;
use App\Notifications\ApplicantRegistered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RegisterApplicantController extends Controller
{
    public function __invoke(RegisterApplicantRequest $request): JsonResponse
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
}
