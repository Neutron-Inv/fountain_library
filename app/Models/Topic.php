<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'grade_id',
        'term_id',
        'week',
        'title',
        'introduction',
        'video',
        'file',
        'cover',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}