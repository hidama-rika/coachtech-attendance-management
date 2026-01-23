<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'check_in', 'check_out', 'remark'];

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
}
