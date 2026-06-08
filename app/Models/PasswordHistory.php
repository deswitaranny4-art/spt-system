<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $table = 'password_histories';

    protected $fillable = [
        'user_id',
        'password'
    ];
}