<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueCompanyName implements ValidationRule
{
    public function __construct(private ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = Str::slug($value);
        if ($slug === '' || strlen($slug) > 255 || in_array($slug, ['profile', 'password', 'all-job', 'top-employers'], true)) {
            $fail('Tên công ty không tạo được đường dẫn hợp lệ. Vui lòng chọn tên khác.');

            return;
        }

        $exists = DB::table('accounts_company_info')
            ->when($this->ignoreId, fn ($query) => $query->where('id', '!=', $this->ignoreId))
            ->where(fn ($query) => $query->where('company_name', trim($value))->orWhere('slug', $slug))
            ->exists();

        if ($exists) {
            $fail('Tên công ty hoặc đường dẫn tương ứng đã được sử dụng.');
        }
    }
}
