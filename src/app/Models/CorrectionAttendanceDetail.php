<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionAttendanceDetail extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_correct_request_id', 'check_in', 'check_out', 'remark'];

    // 親となる申請への逆リレーション
    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
