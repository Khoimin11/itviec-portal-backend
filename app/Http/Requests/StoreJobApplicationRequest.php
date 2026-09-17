<?php

namespace App\Http\Requests;

use App\Models\AccountUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof AccountUser;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'regex:/^0[1-9][0-9]{8,9}$/'],
            'locations' => ['required', 'array', 'min:1', 'max:3'],
            'locations.*' => ['required', 'string', 'distinct', Rule::in(['Ho Chi Minh', 'Ha Noi', 'Da Nang', 'Others'])],
            'coverLetter' => ['nullable', 'string', 'max:500'],
            'cv' => ['required', 'file', 'min:1', 'max:3072', 'mimes:pdf,doc,docx', 'extensions:pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullName.required' => 'Vui lòng nhập họ và tên.',
            'fullName.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'phoneNumber.required' => 'Vui lòng nhập số điện thoại.',
            'phoneNumber.regex' => 'Số điện thoại không hợp lệ.',
            'locations.required' => 'Vui lòng chọn địa điểm làm việc.',
            'locations.max' => 'Chỉ được chọn tối đa 3 địa điểm.',
            'locations.*.in' => 'Địa điểm làm việc không hợp lệ.',
            'locations.*.distinct' => 'Địa điểm làm việc không được trùng.',
            'coverLetter.max' => 'Thư giới thiệu không được vượt quá 500 ký tự.',
            'cv.required' => 'Vui lòng chọn CV.',
            'cv.file' => 'CV phải là file.',
            'cv.min' => 'File CV không được rỗng.',
            'cv.max' => 'CV không được vượt quá 3 MB.',
            'cv.mimes' => 'CV phải là PDF, DOC hoặc DOCX.',
            'cv.extensions' => 'CV phải có đuôi PDF, DOC hoặc DOCX.',
        ];
    }
}
