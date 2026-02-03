<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correct_request_id',
        'rest_id',
        'start_time',
        'end_time',
    ];

    /**
     * 親の修正申請データとの紐付け
     */
    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
