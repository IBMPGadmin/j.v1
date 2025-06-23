<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RCICDeadlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rcic_deadlines')->insert([
            [
                'title' => 'Initial Application Submission',
                'category' => 'Application',
                'description' => 'Deadline for submitting the initial application documents.',
                'deadline_date' => Carbon::now()->addDays(30),
                'days_before' => 30,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Document Review',
                'category' => 'Review',
                'description' => 'Deadline for reviewing submitted documents.',
                'deadline_date' => Carbon::now()->addDays(45),
                'days_before' => 15,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Final Submission',
                'category' => 'Submission',
                'description' => 'Final deadline for all required documents.',
                'deadline_date' => Carbon::now()->addDays(60),
                'days_before' => 15,
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
