<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rests', function (Blueprint $table) {
            $table->id();

            // 外部キー：どの勤怠データに紐付く休憩か
            // attendanceテーブルのIDと紐付け、勤怠データが消えたら休憩も消えるように設定
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            // 休憩開始・終了時刻（H:i:s）
            // 出勤テーブルの仕様 に合わせ、打刻前を考慮して nullable に設定
            $table->time('start_time')->nullable()->comment('休憩開始時刻');
            $table->time('end_time')->nullable()->comment('休憩終了時刻');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rests');
    }
}
