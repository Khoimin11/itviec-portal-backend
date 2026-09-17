<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangeCompanyPasswordRequest;
use App\Models\AccountCompanyInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangeCompanyPasswordController extends Controller
{
    public function __invoke(ChangeCompanyPasswordRequest $request): JsonResponse
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
