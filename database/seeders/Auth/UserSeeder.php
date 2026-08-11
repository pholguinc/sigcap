<?php

namespace Database\Seeders\Auth;

use App\Domains\Auth\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder.
 */
class UserSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Add the master administrator, user id of 1
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'Super Admin',
                'password' => 'secret',
                'email_verified_at' => now(),
                'active' => true,
            ]
        );

        if (app()->environment(['local', 'testing'])) {
            User::firstOrCreate(
                ['email' => 'user@user.com'],
                [
                    'type' => User::TYPE_USER,
                    'name' => 'Test User',
                    'password' => 'secret',
                    'email_verified_at' => now(),
                    'active' => true,
                ]
            );
        }

        $this->enableForeignKeys();
    }
}
