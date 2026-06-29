<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::firstOrCreate(['email'=>'admin@acharyasetu.com'], ['name'=>'Admin','password'=>Hash::make('Admin@123'),'role'=>'admin','is_active'=>true]);

        $this->command->info('✅ Seeded! Admin: admin@acharyasetu.com / Admin@123 | Mentor: rajesh@mentor.com / Mentor@123 | Student: rahul@student.com / Student@123');
    }
}
