<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // roleカラムを追加。
            // default(0)により、新規登録時は全員「一般ユーザー」になります
            // after('password')で、テーブル内のパスワード項目の後ろに配置します
            $table->tinyInteger('role')->default(0)->comment('0:一般, 1:管理者')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // ロールバック（やり直し）した時に追加したカラムを削除します
            $table->dropColumn('role');
        });
    }
}
