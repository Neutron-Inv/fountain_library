<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject_Routing extends Model
{
    use HasFactory;

    protected $table = "subject_routings";
    protected $fillable = [
        'school_id',
        'grade_id',
        'subject_id',
        'teacher_id'
    ];
}
