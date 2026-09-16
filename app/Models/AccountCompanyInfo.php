<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['contact_name', 'position', 'email', 'phone_number', 'source', 'company_name', 'location', 'website', 'status', 'terms_accepted_at', 'password'])]
#[Hidden(['password'])]
class AccountCompanyInfo extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'accounts_company_info';

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'company_skill', 'company_id', 'skill_id');
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
        ];
    }
}
