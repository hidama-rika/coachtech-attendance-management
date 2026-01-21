<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionAttendanceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_attendance_details', function (Blueprint $table) {
            $table->id();

            // 親の申請データと紐付け
            $table->foreignId('attendance_correct_request_id')
                ->constrained('attendance_correct_requests', 'id', 'correct_request_attendance_fk') // 名前が長すぎる場合があるため指定
                ->cascadeOnDelete();

            // 修正後の予定時間
            $table->time('check_in')->comment('修正後の出勤時刻');
            $table->time('check_out')->comment('修正後の退勤時刻');

            // 修正時の備考
            $table->string('remark', 255)->comment('備考');

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
        Schema::dropIfExists('correction_attendance_details');
    }
}
