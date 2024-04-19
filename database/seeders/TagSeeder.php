<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['history', 'language', 'pop-culture'];

        foreach ($types as $type) {
            Tag::create([
                'name' => $type,
            ]);
        }
    }
}
