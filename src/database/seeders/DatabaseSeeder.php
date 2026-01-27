<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // ❗ 作成したシーダーをここで呼び出す
        $this->call([
            UsersTableSeeder::class,
            AttendancesTableSeeder::class,
        ]);
    }
}
