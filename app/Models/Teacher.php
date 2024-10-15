<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'title',
        'first_name', 
        'last_name', 
        'email', 
        'dob',
        'number', 
        'gender',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}