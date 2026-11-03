<?php

namespace Database\Seeders;

use App\Models\MentorAvailability;
use App\Models\User;
use App\Services\MentorAvailabilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyTwoMentorsSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            ['Monday', '10:00', '11:00'],
            ['Monday', '18:00', '19:00'],
            ['Tuesday', '10:00', '11:00'],
            ['Tuesday', '18:00', '19:00'],
            ['Wednesday', '10:00', '11:00'],
            ['Wednesday', '18:00', '19:00'],
            ['Thursday', '10:00', '11:00'],
            ['Thursday', '18:00', '19:00'],
            ['Friday', '10:00', '11:00'],
            ['Friday', '18:00', '19:00'],
            ['Saturday', '11:00', '12:00'],
        ];

        $mentors = [
            [
                'name'             => 'Neha Kapoor',
                'email'            => 'neha.kapoor.demo@vedrix.test',
                'phone'            => '9876501101',
                'gender'           => 'female',
                'field'            => 'Product Management',
                'company'          => 'Flipkart',
                'designation'      => 'Senior Product Manager',
                'experience_years' => 8,
                'expertise'        => ['Product strategy', 'PRD writing', 'User research', 'Roadmapping', 'Stakeholder management'],
                'strengths'        => ['Clear communication', 'Structured thinking', 'Interview prep'],
                'bio'              => '<p>I help early-career PMs and career-switchers break into product. We will work through real PRDs, interview cases, and how to talk about impact in your current role.</p><p>Eight years across Flipkart and B2B SaaS, shipping consumer and seller tools used by millions.</p>',
                'linkedin'         => 'https://www.linkedin.com/in/neha-kapoor-demo',
                'location'         => 'Bengaluru',
                'rate_per_minute'  => 12,
                'rating'           => 4.8,
                'total_sessions'   => 64,
                'avatar_url'       => 'https://i.pravatar.cc/300?img=47',
                'preferences'      => [
                    'preferred_time'   => 'evening',
                    'session_length'   => 60,
                    'session_modes'    => ['video'],
                    'mentoring_format' => 'one_on_one',
                    'accepting_bookings' => true,
                ],
            ],
            [
                'name'             => 'Vikram Rao',
                'email'            => 'vikram.rao.demo@vedrix.test',
                'phone'            => '9876501102',
                'gender'           => 'male',
                'field'            => 'Engineering & Technology',
                'company'          => 'Razorpay',
                'designation'      => 'Staff Software Engineer',
                'experience_years' => 11,
                'expertise'        => ['System design', 'Backend', 'DSA', 'Career growth', 'L4–L6 interviews'],
                'strengths'        => ['System design drills', 'Code reviews', 'Offer negotiation'],
                'bio'              => '<p>Staff engineer mentoring developers who want stronger system design and a clearer path to senior roles.</p><p>We will map your current work to interview stories, practice 60-minute design problems, and fix gaps in backend fundamentals.</p>',
                'linkedin'         => 'https://www.linkedin.com/in/vikram-rao-demo',
                'location'         => 'Hyderabad',
                'rate_per_minute'  => 15,
                'rating'           => 4.9,
                'total_sessions'   => 118,
                'avatar_url'       => 'https://i.pravatar.cc/300?img=12',
                'preferences'      => [
                    'preferred_time'   => 'morning',
                    'session_length'   => 60,
                    'session_modes'    => ['video'],
                    'mentoring_format' => 'one_on_one',
                    'accepting_bookings' => true,
                ],
            ],
        ];

        $availability = app(MentorAvailabilityService::class);

        foreach ($mentors as $data) {
            $mentor = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'                 => $data['name'],
                    'password'             => Hash::make('Mentor@123'),
                    'phone'                => $data['phone'],
                    'gender'               => $data['gender'],
                    'role'                 => 'mentor',
                    'field'                => $data['field'],
                    'company'              => $data['company'],
                    'designation'          => $data['designation'],
                    'experience_years'     => $data['experience_years'],
                    'expertise'            => $data['expertise'],
                    'strengths'            => $data['strengths'],
                    'bio'                  => $data['bio'],
                    'linkedin'             => $data['linkedin'],
                    'location'             => $data['location'],
                    'rate_per_minute'      => $data['rate_per_minute'],
                    'rating'               => $data['rating'],
                    'total_sessions'       => $data['total_sessions'],
                    'avatar_url'           => $data['avatar_url'],
                    'preferences'          => $data['preferences'],
                    'is_active'            => true,
                    'mentor_status'        => User::MENTOR_STATUS_APPROVED,
                    'onboarding_step'      => 5,
                    'onboarding_completed' => true,
                    'approved_at'          => now(),
                    'isVerifiedEmail'      => true,
                ]
            );

            $mentor->ensureSlug();

            MentorAvailability::query()->where('mentor_id', $mentor->id)->delete();
            foreach ($slots as [$day, $start, $end]) {
                MentorAvailability::create([
                    'mentor_id'    => $mentor->id,
                    'day_of_week'  => $day,
                    'start_time'   => $start,
                    'end_time'     => $end,
                    'is_available' => true,
                ]);
            }
            $availability->syncPreferencesFromTable($mentor, 60);

            $this->command?->info($mentor->name.' — '.$mentor->profile_url);
        }
    }
}
