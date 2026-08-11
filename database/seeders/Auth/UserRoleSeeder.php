<?php

namespace Database\Seeders\Auth;

use App\Domains\Auth\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

/**
 * Class UserRoleTableSeeder.
 */
class UserRoleSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            $admin->assignRole(config('boilerplate.access.role.admin'));
        }

        $this->enableForeignKeys();
    }
}
