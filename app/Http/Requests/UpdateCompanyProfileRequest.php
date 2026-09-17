<?php

namespace App\Http\Requests;

use App\Models\AccountCompanyInfo;
use App\Rules\UniqueCompanyName;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof AccountCompanyInfo && $this->user()->status === 'active';
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (['username', 'position', 'email', 'phoneNumber', 'companyName', 'website', 'tagline', 'country'] as $field) {
            if (is_string($this->input($field))) {
                $values[$field] = trim($this->input($field));
            }
        }
        if (isset($values['email'])) {
            $values['email'] = mb_strtolower($values['email']);
        }
        if ($this->input('skillIds') === '' || $this->input('skillIds') === null) {
            $values['skillIds'] = [];
        }
        $this->merge($values);
    }

    public function rules(): array
    {
        $richText = ['nullable', 'string', 'max:20000', function (string $attribute, mixed $value, Closure $fail): void {
            if (mb_strlen(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) > 2500) {
                $fail('Nội dung không được vượt quá 2500 ký tự.');
            }
        }];

        return [
            'username' => ['required', 'string', 'min:4', 'max:255'],
            'position' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('accounts_company_info', 'email')->ignore($this->user()->id)],
            'phoneNumber' => ['required', 'string', 'regex:/^0[1-9][0-9]{8,9}$/'],
            'companyName' => ['bail', 'required', 'string', 'min:4', 'max:255', new UniqueCompanyName($this->user()->id)],
            'location' => ['required', Rule::in(['Ho Chi Minh', 'Ha Noi', 'Da Nang', 'Others'])],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'companyType' => ['required', Rule::in(['IT Outsourcing', 'IT Product', 'Headhunt', 'IT Service and IT Consulting', 'Non-IT'])],
            'industryId' => ['required', 'integer', 'exists:industries,id'],
            'companySize' => ['required', Rule::in(['10-50 employees', '51-150 employees', '301-500 employees', '1000+ employees'])],
            'country' => ['required', 'string', 'max:100'],
            'workingDay' => ['required', Rule::in(['Monday - Friday', 'Monday - Saturday'])],
            'overtimePolicy' => ['required', Rule::in(['No OT', 'Extra salary for OT'])],
            'overview' => $richText,
            'perks' => $richText,
            'skillIds' => ['present', 'array', 'max:10'],
            'skillIds.*' => ['integer', 'distinct', 'exists:skills,id'],
            'logo' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'email.unique' => 'Email đã được đăng ký cho tài khoản công ty khác.',
            'email.email' => 'Email không hợp lệ.',
            'phoneNumber.regex' => 'Số điện thoại không hợp lệ.',
            'website.url' => 'Website phải là URL hợp lệ, bắt đầu bằng http hoặc https.',
            'in' => 'Giá trị :attribute không hợp lệ.',
            'exists' => 'Giá trị :attribute không tồn tại.',
            'logo.image' => 'Logo phải là hình ảnh.',
            'logo.mimes' => 'Logo phải là ảnh JPG, PNG hoặc WebP.',
            'logo.max' => 'Logo không được vượt quá 2 MB.',
            'skillIds.max' => 'Chỉ được chọn tối đa 10 kỹ năng.',
        ];
    }
}
