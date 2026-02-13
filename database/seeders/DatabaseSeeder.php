<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use App\Models\Sheet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Standard Student User
        $student = User::factory()->create([
            'name' => 'Abdullah Student',
            'email' => 'student@uob.edu.ly',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // 2. Create an Admin User
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@uob.edu.ly',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 3. Define Realistic Subjects
        $subjectsData = [
            ['code' => 'SE311', 'name' => 'Software Engineering 1'],
            ['code' => 'CS101', 'name' => 'Introduction to Computer Science'],
            ['code' => 'CS102', 'name' => 'Programming in C++'],
            ['code' => 'IT201', 'name' => 'Web Development'],
            ['code' => 'MA101', 'name' => 'Calculus I'],
            ['code' => 'CS322', 'name' => 'Database Systems'],
            ['code' => 'SE321', 'name' => 'Software Architecture'],
            ['code' => 'CS211', 'name' => 'Data Structures'],
        ];

        foreach ($subjectsData as $data) {
            $subject = Subject::create($data);

            // Add Chapters (Approved)
            for ($i = 1; $i <= 5; $i++) {
                Sheet::factory()->create([
                    'subject_id' => $subject->id,
                    'user_id' => $student->id,
                    'type' => 'chapter',
                    'chapter_number' => $i,
                    'status' => 'approved',
                    'title' => "Chapter $i Summary - " . $subject->code,
                    'downloads_count' => rand(10, 100),
                ]);
            }

            // Add Midterms (Approved)
            Sheet::factory()->create([
                'subject_id' => $subject->id,
                'user_id' => $student->id,
                'type' => 'midterm',
                'status' => 'approved',
                'title' => "Midterm 2024 - " . $subject->code,
                'downloads_count' => rand(50, 200),
            ]);

            // Add Finals (Approved)
            Sheet::factory()->create([
                'subject_id' => $subject->id,
                'user_id' => $student->id,
                'type' => 'final',
                'status' => 'approved',
                'title' => "Final Exam 2023 - " . $subject->code,
                'downloads_count' => rand(100, 500),
            ]);

            // Add Pending Sheets (For Admin Dashboard testing)
            Sheet::factory()->create([
                'subject_id' => $subject->id,
                'user_id' => $student->id, // Uploaded by student
                'type' => 'chapter',
                'chapter_number' => 6,
                'status' => 'pending',
                'title' => "Pending Chapter 6 - " . $subject->code,
            ]);
        }
    }
}
