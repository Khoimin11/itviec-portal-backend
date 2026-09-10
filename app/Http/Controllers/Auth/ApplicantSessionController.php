<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginApplicantRequest;
use App\Http\Resources\AccountUserResource;
use App\Models\AccountUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApplicantSessionController extends Controller
{
    public function store(LoginApplicantRequest $request): JsonResponse
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

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => new AccountUserResource($this->account($request)),
        ]);
    }

    private function account(Request $request): AccountUser
    {
        $account = $request->user();
        abort_unless($account instanceof AccountUser, 403);

        return $account;
    }
}
