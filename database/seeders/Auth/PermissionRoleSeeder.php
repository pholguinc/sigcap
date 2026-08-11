<?php

namespace Database\Seeders\Auth;

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

/**
 * Class PermissionRoleTableSeeder.
 */
class PermissionRoleSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Create Roles
        Role::firstOrCreate(
            ['id' => 1],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'Administrator',
            ]
        );

        // Non Grouped Permissions
        //

        // Grouped permissions
        // Users category
        $users = Permission::firstOrCreate(
            ['name' => 'admin.access.user'],
            [
                'type' => User::TYPE_ADMIN,
                'description' => 'All User Permissions',
            ]
        );

        $childPermissions = [
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.list',
                'description' => 'View Users',
            ],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.deactivate',
                'description' => 'Deactivate Users',
                'sort' => 2,
            ],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.reactivate',
                'description' => 'Reactivate Users',
                'sort' => 3,
            ],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.clear-session',
                'description' => 'Clear User Sessions',
                'sort' => 4,
            ],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.impersonate',
                'description' => 'Impersonate Users',
                'sort' => 5,
            ],
            [
                'type' => User::TYPE_ADMIN,
                'name' => 'admin.access.user.change-password',
                'description' => 'Change User Passwords',
                'sort' => 6,
            ],
        ];

        foreach ($childPermissions as $perm) {
            if (!Permission::where('name', $perm['name'])->exists()) {
                $users->children()->save(new Permission($perm));
            }
        }

        // Assign Permissions to other Roles
        //

        $this->enableForeignKeys();
    }
}
