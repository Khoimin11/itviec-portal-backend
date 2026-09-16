<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[Fillable(['contact_name', 'position', 'email', 'phone_number', 'source', 'company_name', 'location', 'website', 'status', 'terms_accepted_at', 'password'])]
#[Hidden(['password'])]
class AccountCompanyInfo extends Model
{
    use Notifiable;

    protected $table = 'accounts_company_info';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
        ];
    }
}
