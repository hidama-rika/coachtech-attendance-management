<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 必須：いつのデータか特定するため
            $table->date('date')->comment('勤務日');

            // 任意：打刻されるまでは空
            $table->time('check_in')->nullable()->comment('出勤時刻');
            $table->time('check_out')->nullable()->comment('退勤時刻');

            // 任意：備考
            $table->string('remark', 255)->nullable()->comment('備考');

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
        Schema::dropIfExists('attendances');
    }
}
