<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'check_in', 'check_out', 'remark'];

    protected $casts = [
        'date' => 'date',
]   ;

    // 休憩時間の合計を計算
    public function getTotalRestTimeAttribute()
    {
        $totalSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->end_time) {
                // 開始と終了の差分を秒で加算
                $totalSeconds += Carbon::parse($rest->start_time)->diffInSeconds(Carbon::parse($rest->end_time));
            }
        }
        // 秒を H:i 形式に変換
        return gmdate('H:i', $totalSeconds);
    }

    // 勤務時間の合計を計算 (退勤 - 出勤 - 休憩合計)
    public function getTotalWorkingTimeAttribute()
    {
        if (!$this->check_out) return '00:00';

        // (退勤 - 出勤) の総秒数
        $workSeconds = Carbon::parse($this->check_in)->diffInSeconds(Carbon::parse($this->check_out));

        // 休憩時間の総秒数を引く
        $restSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->end_time) {
                $restSeconds += Carbon::parse($rest->start_time)->diffInSeconds(Carbon::parse($rest->end_time));
            }
        }

        return gmdate('H:i', max(0, $workSeconds - $restSeconds));
    }

    // ユーザーへの逆リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠に紐付く複数の休憩データを取得
    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    /**
     * この勤怠に関連付けられた修正申請を取得
     */
    public function attendanceCorrectRequest()
    {
        // 1つの勤怠データに対して、修正申請は1つ（HasOne）紐付きます
        return $this->hasOne(AttendanceCorrectRequest::class);
    }
}
