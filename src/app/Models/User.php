<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail // implementsを追加
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ユーザーに紐付く複数の勤怠データを取得
     */
    public function attendances()
    {
        // 1人のユーザーは、日々の勤怠データをたくさん（HasMany）持っています
        return $this->hasMany(Attendance::class);
    }

    /**
     * ユーザーが行った修正申請の一覧を取得
     */
    public function attendanceCorrectRequests()
    {
        // 1人のユーザーは複数の修正申請（HasMany）を持つことができます
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}
