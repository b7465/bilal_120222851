<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_code',
        'course_name',
        'instructor',
        'days',
        'time',
        'room',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
