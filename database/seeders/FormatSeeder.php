<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Format;

class FormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Format::create([
            'name' => 'cpe',
            'version' => '2.3',
        ]);
        Format::create([
            'name' => 'purl',
        ]);
    }
}
