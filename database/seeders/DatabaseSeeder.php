<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::factory()->create([
            'username' => 'tester',
            'display_name' => 'Demo user',
        ]);

        $demo->roles()->attach([Role::where('code', 'user')->first()]);
    }
}
