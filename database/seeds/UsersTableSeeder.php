<?php

use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Run
     */
    public function run()
    {
        factory(\App\Models\User::class, 50)->create();
        factory(\App\Models\User::class)->create(
            [
                'username' => 'admin',
                'email'    => 'admin@app.com',
                'active'   => true,
                'roles'    => [\App\Models\Role::ROLE_ADMIN],
                'password' => '123'
            ]
        );
        factory(
            \App\Models\User::class
        )->create(
            [
                'username' => 'user',
                'email'    => 'user@app.com',
                'active'   => true,
                'roles'    => [\App\Models\Role::ROLE_USER],
                'password' => '123'
            ]
        );
    }
}
