<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preregistro extends Model {
    use HasFactory;
    protected $hidden = [
        'password',
        'otp',
        'is_student',
        'auth_profesor',
        'token',
        'created_at',
        'updated_at'
    ];
}
