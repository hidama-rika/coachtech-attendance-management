<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'attendance_id', 'status'];

    // 申請をしたユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 修正対象の勤怠
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 修正後の具体的な打刻内容（1対1のリレーション）
    public function correctionAttendanceDetail()
    {
        return $this->hasOne(CorrectionAttendanceDetail::class, 'attendance_correct_request_id');
    }

    // 修正後の具体的な休憩内容（1対多）
    // モデル名は適宜作成したもの（例: CorrectionRestDetail）に合わせる
    public function restDetails()
    {
        return $this->hasMany(CorrectionRestDetail::class);
    }
}
