<?php

namespace Database\Seeders;

use App\Enumeration\PermissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->truncate();
        $modules = array(
            array('name' => 'settings', 'module' => 'settings', 'type' => PermissionType::$Admin),
            array('name' => 'customer', 'module' => 'customer', 'type' => PermissionType::$Admin),
            array('name' => 'user', 'module' => 'user', 'type' => PermissionType::$Customer),
            array('name' => 'uploads', 'module' => 'uploads', 'type' => PermissionType::$Customer),
        );

        DB::table('permissions')->insert($modules);
    }
}
