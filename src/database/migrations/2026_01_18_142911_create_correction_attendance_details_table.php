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

            // foreignId ではなく、一度 unsignedBigInteger でカラムだけ作る
            $table->unsignedBigInteger('attendance_correct_request_id');

            // 親の申請データと紐付け
            // その後、明示的に短い名前で制約を付ける
            $table->foreign('attendance_correct_request_id', 'corr_att_req_fk')
                ->references('id')
                ->on('attendance_correct_requests')
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
