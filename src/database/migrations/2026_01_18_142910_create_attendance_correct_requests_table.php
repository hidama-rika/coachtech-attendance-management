<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();

            // どのユーザーの申請か
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // どの勤怠データに対する修正か
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            // 状態：1:承認待ち, 2:承認済み
            $table->tinyInteger('status')->default(1)->comment('1:承認待ち, 2:承認済み');

            // 申請理由（管理者へのメッセージ）
            $table->text('reason')->comment('申請理由');

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
        Schema::dropIfExists('attendance_correct_requests');
    }
}
