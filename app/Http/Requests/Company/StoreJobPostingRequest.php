<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobPostingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'label' => ['nullable', Rule::in(['HOT', 'SUPER HOT', 'NEW'])],
            'currencySalary' => ['required', Rule::in(['VND', 'USD'])],
            'minSalary' => ['required', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'],
            'maxSalary' => ['required', 'numeric', 'gte:minSalary', 'max:999999999999.99', 'decimal:0,2'],
            'level' => ['required', Rule::in(['Fresher', 'Junior', 'Senior', 'Manager'])],
            'workingModel' => ['required', Rule::in(['At office', 'Remote', 'Hybrid'])],
            'location' => ['required', Rule::in(['Ho Chi Minh', 'Ha Noi', 'Da Nang', 'Others'])],
            'address' => ['nullable', 'string', 'max:255'],
            'startDate' => ['required', 'date_format:Y-m-d'],
            'endDate' => ['required', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'skillIds' => ['required', 'array', 'min:1', 'max:3'],
            'skillIds.*' => ['required', 'integer', 'distinct', 'exists:skills,id'],
            'description' => ['nullable', 'string', 'max:20000'],
            'requirement' => ['nullable', 'string', 'max:20000'],
            'reason' => ['nullable', 'string', 'max:20000'],
            'companyId' => ['prohibited'],
            'company_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'in' => ':attribute không hợp lệ.',
            'max' => ':attribute vượt quá giới hạn cho phép (:max).',
            'numeric' => ':attribute phải là số.',
            'minSalary.min' => 'Mức lương không được âm.',
            'maxSalary.gte' => 'Mức lương tối đa phải lớn hơn hoặc bằng mức lương tối thiểu.',
            'decimal' => 'Mức lương chỉ được có tối đa 2 chữ số thập phân.',
            'date_format' => ':attribute không hợp lệ.',
            'endDate.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            'skillIds.required' => 'Vui lòng chọn ít nhất một kỹ năng.',
            'skillIds.max' => 'Chỉ được chọn tối đa 3 kỹ năng.',
            'skillIds.*.exists' => 'Kỹ năng đã chọn không tồn tại.',
            'skillIds.*.distinct' => 'Không được chọn kỹ năng trùng nhau.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'tên việc làm', 'label' => 'nhãn',
            'currencySalary' => 'đơn vị tiền tệ', 'minSalary' => 'lương tối thiểu',
            'maxSalary' => 'lương tối đa', 'level' => 'cấp bậc',
            'workingModel' => 'hình thức làm việc', 'location' => 'thành phố',
            'address' => 'địa chỉ', 'startDate' => 'ngày bắt đầu',
            'endDate' => 'ngày kết thúc', 'skillIds' => 'kỹ năng',
            'description' => 'mô tả công việc', 'requirement' => 'yêu cầu',
            'reason' => 'phúc lợi',
        ];
    }
}
