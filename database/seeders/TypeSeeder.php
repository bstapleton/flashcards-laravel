<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Statement', 'Question', 'Multiple choice'];

        foreach ($types as $type) {
            Type::create([
                'name' => $type,
            ]);
        }
    }
}
