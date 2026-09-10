<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }

        if (is_string($this->input('username'))) {
            $this->merge(['username' => trim($this->input('username'))]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', Rule::unique('accounts_user', 'email')],
            'password' => [
                'bail', 'required', 'string', 'min:12', 'max:72',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9\s]/u',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('Mật khẩu không được vượt quá 72 byte.');
                    }
                },
            ],
            'termsAccepted' => ['required', 'accepted'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập họ và tên.',
            'username.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 12 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 72 ký tự.',
            'password.regex' => 'Mật khẩu phải có chữ hoa, chữ thường, số và ký tự đặc biệt.',
            'termsAccepted.required' => 'Vui lòng đồng ý với điều khoản và chính sách quyền riêng tư.',
            'termsAccepted.accepted' => 'Vui lòng đồng ý với điều khoản và chính sách quyền riêng tư.',
        ];
    }
}
