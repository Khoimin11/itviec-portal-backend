<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Applicant\AccountUserResource;
use App\Http\Resources\Company\AccountCompanyResource;
use App\Models\AccountCompanyInfo;
use App\Models\AccountUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SessionController extends Controller
{
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
