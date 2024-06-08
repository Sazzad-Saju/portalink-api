<?php

namespace Database\Seeders;

use App\Enumeration\PermissionType;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'username' => 'Super Admin',
            'email' => 'admin@portalink.com',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'password' => Hash::make(123456),
            'user_id' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $permissions = Permission::where('type', PermissionType::$Admin)->get();

        foreach ($permissions as $permission){
            UserPermission::create([
                'module' => $permission->module,
                'user_id' => 1,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
