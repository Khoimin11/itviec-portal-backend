<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected function casts(): array
    {
        return ['locations' => 'array'];
    }
}
