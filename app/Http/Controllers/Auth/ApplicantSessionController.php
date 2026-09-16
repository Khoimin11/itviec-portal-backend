<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginApplicantRequest;
use App\Http\Resources\AccountCompanyResource;
use App\Http\Resources\AccountUserResource;
use App\Models\AccountCompanyInfo;
use App\Models\AccountUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

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
        $account = $request->user();
        if ($account instanceof AccountCompanyInfo) {
            abort_unless($account->status === 'active', 403);
            $resource = new AccountCompanyResource($account);
        } else {
            $resource = new AccountUserResource($this->account($request));
        }

        return response()->json([
            'isSuccess' => true,
            'message' => '',
            'data' => $resource,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $account = $request->user();
        abort_unless($account instanceof AccountUser || $account instanceof AccountCompanyInfo, 403);

        $token = $account->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            abort(401);
        }

        return response()->json([
            'isSuccess' => true,
            'message' => 'Đăng xuất thành công',
            'data' => null,
        ]);
    }

    private function account(Request $request): AccountUser
    {
        $account = $request->user();
        abort_unless($account instanceof AccountUser, 403);

        return $account;
    }
}
