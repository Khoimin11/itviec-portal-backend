<?php

namespace App\Http\Requests\Auth;

use App\Rules\UniqueCompanyName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['username', 'position', 'email', 'phoneNumber', 'source', 'companyName', 'location', 'website'] as $field) {
            if (is_string($this->input($field))) {
                $value = trim($this->input($field));
                $this->merge([$field => $field === 'email' ? mb_strtolower($value) : $value]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:4', 'max:255'],
            'position' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', Rule::unique('accounts_company_info', 'email')],
            'phoneNumber' => ['required', 'string', 'regex:/^0[1-9][0-9]{8,9}$/'],
            'source' => ['nullable', 'string', 'max:255'],
            'companyName' => ['bail', 'required', 'string', 'min:4', 'max:255', new UniqueCompanyName],
            'location' => ['required', Rule::in(['Ho Chi Minh', 'Ha Noi', 'Da Nang', 'Others'])],
            'website' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'termsAccepted' => ['required', 'accepted'],
            'status' => ['prohibited'],
            'password' => ['prohibited'],
            'password_setup_token' => ['prohibited'],
            'password_setup_expires_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'min' => ':attribute phải có ít nhất :min ký tự.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được đăng ký cho tài khoản công ty.',
            'phoneNumber.regex' => 'Số điện thoại không hợp lệ.',
            'location.in' => 'Vui lòng chọn địa chỉ công ty hợp lệ.',
            'website.url' => 'Website phải là URL bắt đầu bằng http:// hoặc https://.',
            'termsAccepted.required' => 'Vui lòng đồng ý với điều khoản và chính sách quyền riêng tư.',
            'termsAccepted.accepted' => 'Vui lòng đồng ý với điều khoản và chính sách quyền riêng tư.',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'họ và tên', 'position' => 'chức vụ', 'email' => 'email',
            'phoneNumber' => 'số điện thoại', 'companyName' => 'tên công ty',
            'location' => 'địa chỉ công ty', 'website' => 'website', 'source' => 'nguồn giới thiệu',
        ];
    }
}
