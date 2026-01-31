<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_rest_details', function (Blueprint $table) {
            $table->id();

            // foreignId ではなく、一度 unsignedBigInteger でカラムだけ作る
            $table->unsignedBigInteger('attendance_correct_request_id');

            // 親の申請データと紐付け
            $table->foreign('attendance_correct_request_id', 'corr_rest_req_fk')
                ->references('id')
                ->on('attendance_correct_requests')
                ->cascadeOnDelete();

            // 修正対象の休憩ID（新規追加の場合はnullを許容）
            $table->foreignId('rest_id')->nullable()->constrained('rests')->cascadeOnDelete();

            // 修正後の休憩時間
            $table->time('start_time')->comment('修正後の休憩開始');
            $table->time('end_time')->comment('修正後の休憩終了');

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
        Schema::dropIfExists('correction_rest_details');
    }
}
