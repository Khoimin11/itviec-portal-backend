<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ChangeCompanyPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        $byteLimit = function (string $attribute, mixed $value, Closure $fail): void {
            if (strlen($value) > 72) {
                $fail('Mật khẩu không được vượt quá 72 byte.');
            }
        };

        return [
            'currentPassword' => ['bail', 'required', 'string', 'max:72', $byteLimit],
            'newPassword' => [
                'bail', 'required', 'string', 'min:12', 'max:72', $byteLimit,
                'different:currentPassword',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9\s]/u',
            ],
            'confirmPassword' => ['bail', 'required', 'string', 'same:newPassword'],
        ];
    }

    public function messages(): array
    {
        return [
            'currentPassword.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'newPassword.required' => 'Vui lòng nhập mật khẩu mới.',
            'newPassword.min' => 'Mật khẩu mới phải có ít nhất 12 ký tự.',
            'newPassword.regex' => 'Mật khẩu mới phải có chữ hoa, chữ thường, số và ký tự đặc biệt.',
            'newPassword.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'max' => 'Mật khẩu không được vượt quá 72 ký tự.',
            'confirmPassword.required' => 'Vui lòng nhập lại mật khẩu mới.',
            'confirmPassword.same' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}
