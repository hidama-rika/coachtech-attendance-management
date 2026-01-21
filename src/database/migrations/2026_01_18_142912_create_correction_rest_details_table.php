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

            // 親の申請データと紐付け
            $table->foreignId('attendance_correct_request_id')
                ->constrained('attendance_correct_requests', 'id', 'correct_request_rest_fk')
                ->cascadeOnDelete();

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
