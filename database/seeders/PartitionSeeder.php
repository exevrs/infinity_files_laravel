<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('partitions')->insert([
            'name' => 'abrfabr',
            'code' => 'xabc3221',
        ]);

        DB::table('partitions')->insert([
            'name' => 'exe',
            'code' => 'xabc3221',
        ]);

        DB::table('partitions')->insert([
            'name' => 'test',
            'code' => 'xabc3221',
        ]);

        DB::table('partitions')->insert([
            'name' => 'tnmuzeum',
            'code' => 'xabc3221',
        ]);
    }
}
