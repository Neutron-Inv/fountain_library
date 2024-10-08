<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    
    protected $table = "admins";
    protected $fillable = [
        'school_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
    ];
}
