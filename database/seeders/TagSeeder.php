<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['history', 'language', 'pop-culture'];
        $user = User::first();

        foreach ($types as $type) {
            Tag::factory()->create([
                'user_id' => $user->id,
                'name' => $type,
            ]);
        }
    }
}
