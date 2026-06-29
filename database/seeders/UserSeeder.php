<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Google-hosted profile images (via randomuser.me which uses real photos)
     * Using UI Avatars as fallback, and pravatar.cc for realistic faces
     */
    private function getMentorAvatar(int $index, string $gender): string
    {
        $maleIds   = [10, 11, 12, 13, 14, 15, 32, 33, 36, 52, 53, 54, 55, 56, 57, 60, 61, 62, 63, 64];
        $femaleIds = [44, 45, 46, 47, 48, 49, 50, 51, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 90];

        $ids = $gender === 'male' ? $maleIds : $femaleIds;
        $id  = $ids[$index % count($ids)];

        return "https://i.pravatar.cc/300?img={$id}";
    }

    private function getMenteeAvatar(int $index, string $gender): string
    {
        $maleIds   = [1, 2, 3, 4, 5, 6, 7, 8, 20, 21, 22, 23, 24, 25, 26, 30, 31, 34, 35, 58];
        $femaleIds = [76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96];

        $ids = $gender === 'male' ? $maleIds : $femaleIds;
        $id  = $ids[$index % count($ids)];

        return "https://i.pravatar.cc/300?img={$id}";
    }

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        User::firstOrCreate(['email'=>'admin@acharyasetu.com'], ['name'=>'Admin','password'=>Hash::make('Admin@123'),'role'=>'admin','is_active'=>true]);

        $mentorIds = [];

        // ─────────────────────────────────────────────
        // 20 MENTORS
        // ─────────────────────────────────────────────
        $mentors = [
            [
                'name'              => 'Arjun Mehta',
                'email'             => 'arjun.mehta@example.com',
                'gender'            => 'male',
                'field'             => 'Software Engineering',
                'company'           => 'Google',
                'designation'       => 'Senior Software Engineer',
                'experience_years'  => 9,
                'expertise'         => ['System Design', 'DSA', 'Backend Development', 'Cloud Architecture'],
                'bio'               => 'Ex-Amazon SDE with 9 years in distributed systems. Passionate about helping students crack top tech companies.',
                'linkedin'          => 'https://linkedin.com/in/arjunmehta',
                'rate_per_minute'   => 15.00,
                'rating'            => 4.85,
                'total_sessions'    => 312,
            ],
            [
                'name'              => 'Priya Sharma',
                'email'             => 'priya.sharma@example.com',
                'gender'            => 'female',
                'field'             => 'Data Science',
                'company'           => 'Microsoft',
                'designation'       => 'Principal Data Scientist',
                'experience_years'  => 11,
                'expertise'         => ['Machine Learning', 'Python', 'NLP', 'Deep Learning'],
                'bio'               => 'ML lead at Microsoft. Love mentoring aspiring data scientists through real-world projects.',
                'linkedin'          => 'https://linkedin.com/in/priyasharma',
                'rate_per_minute'   => 18.00,
                'rating'            => 4.92,
                'total_sessions'    => 478,
            ],
            [
                'name'              => 'Rahul Verma',
                'email'             => 'rahul.verma@example.com',
                'gender'            => 'male',
                'field'             => 'Product Management',
                'company'           => 'Flipkart',
                'designation'       => 'Group Product Manager',
                'experience_years'  => 8,
                'expertise'         => ['Product Strategy', 'User Research', 'Agile', 'OKRs'],
                'bio'               => 'Built products used by 100M+ Indians. Helping future PMs sharpen their thinking.',
                'linkedin'          => 'https://linkedin.com/in/rahulverma',
                'rate_per_minute'   => 14.00,
                'rating'            => 4.76,
                'total_sessions'    => 223,
            ],
            [
                'name'              => 'Sneha Iyer',
                'email'             => 'sneha.iyer@example.com',
                'gender'            => 'female',
                'field'             => 'UX Design',
                'company'           => 'Adobe',
                'designation'       => 'Senior UX Designer',
                'experience_years'  => 7,
                'expertise'         => ['Figma', 'User Testing', 'Design Systems', 'Prototyping'],
                'bio'               => 'Designing experiences at Adobe. Mentor with a focus on portfolio building and design thinking.',
                'linkedin'          => 'https://linkedin.com/in/snehaiyer',
                'rate_per_minute'   => 12.00,
                'rating'            => 4.88,
                'total_sessions'    => 189,
            ],
            [
                'name'              => 'Karan Gupta',
                'email'             => 'karan.gupta@example.com',
                'gender'            => 'male',
                'field'             => 'Finance',
                'company'           => 'Goldman Sachs',
                'designation'       => 'VP - Investment Banking',
                'experience_years'  => 12,
                'expertise'         => ['Financial Modelling', 'M&A', 'Valuation', 'CFA Prep'],
                'bio'               => 'Wall Street veteran helping finance aspirants navigate IB interviews and CFA.',
                'linkedin'          => 'https://linkedin.com/in/karangupta',
                'rate_per_minute'   => 20.00,
                'rating'            => 4.79,
                'total_sessions'    => 341,
            ],
            [
                'name'              => 'Divya Nair',
                'email'             => 'divya.nair@example.com',
                'gender'            => 'female',
                'field'             => 'Marketing',
                'company'           => 'HubSpot',
                'designation'       => 'Head of Growth Marketing',
                'experience_years'  => 9,
                'expertise'         => ['Digital Marketing', 'SEO', 'Content Strategy', 'Performance Marketing'],
                'bio'               => 'Growth hacker who scaled HubSpot India\'s user base 3x. Mentor focused on marketing careers.',
                'linkedin'          => 'https://linkedin.com/in/divyanair',
                'rate_per_minute'   => 13.00,
                'rating'            => 4.81,
                'total_sessions'    => 267,
            ],
            [
                'name'              => 'Vikram Singh',
                'email'             => 'vikram.singh@example.com',
                'gender'            => 'male',
                'field'             => 'Entrepreneurship',
                'company'           => 'FinStartup (Self)',
                'designation'       => 'Co-Founder & CEO',
                'experience_years'  => 10,
                'expertise'         => ['Startup Strategy', 'Fundraising', 'Pitch Decks', 'Business Model'],
                'bio'               => 'Founded two startups. Raised $5M in seed funding. Mentoring first-time founders.',
                'linkedin'          => 'https://linkedin.com/in/vikramsingh',
                'rate_per_minute'   => 16.00,
                'rating'            => 4.70,
                'total_sessions'    => 198,
            ],
            [
                'name'              => 'Anjali Patel',
                'email'             => 'anjali.patel@example.com',
                'gender'            => 'female',
                'field'             => 'HR & People Operations',
                'company'           => 'Zomato',
                'designation'       => 'Director of Human Resources',
                'experience_years'  => 14,
                'expertise'         => ['Resume Writing', 'Interview Prep', 'HR Strategy', 'People Culture'],
                'bio'               => 'HR leader who\'s hired 1000+ professionals. Expert in resume, GD, and PI rounds.',
                'linkedin'          => 'https://linkedin.com/in/anjalipatel',
                'rate_per_minute'   => 11.00,
                'rating'            => 4.93,
                'total_sessions'    => 521,
            ],
            [
                'name'              => 'Rohan Kapoor',
                'email'             => 'rohan.kapoor@example.com',
                'gender'            => 'male',
                'field'             => 'Cybersecurity',
                'company'           => 'Palo Alto Networks',
                'designation'       => 'Security Architect',
                'experience_years'  => 8,
                'expertise'         => ['Ethical Hacking', 'Penetration Testing', 'CISSP', 'Network Security'],
                'bio'               => 'Security architect with multiple CVEs to my name. Passionate about teaching offensive security.',
                'linkedin'          => 'https://linkedin.com/in/rohankapoor',
                'rate_per_minute'   => 17.00,
                'rating'            => 4.83,
                'total_sessions'    => 156,
            ],
            [
                'name'              => 'Meera Krishnan',
                'email'             => 'meera.krishnan@example.com',
                'gender'            => 'female',
                'field'             => 'Law',
                'company'           => 'Cyril Amarchand Mangaldas',
                'designation'       => 'Senior Associate',
                'experience_years'  => 6,
                'expertise'         => ['Corporate Law', 'CLAT Prep', 'Legal Research', 'Contracts'],
                'bio'               => 'Corporate lawyer at one of India\'s top law firms. Mentoring law aspirants for entrance exams and careers.',
                'linkedin'          => 'https://linkedin.com/in/meerakrishnan',
                'rate_per_minute'   => 13.00,
                'rating'            => 4.77,
                'total_sessions'    => 143,
            ],
            [
                'name'              => 'Aditya Bose',
                'email'             => 'aditya.bose@example.com',
                'gender'            => 'male',
                'field'             => 'Mechanical Engineering',
                'company'           => 'Tata Motors',
                'designation'       => 'Lead Design Engineer',
                'experience_years'  => 10,
                'expertise'         => ['CAD/CAM', 'GATE Prep', 'Automotive Design', 'FEA Analysis'],
                'bio'               => 'Designing the future of mobility at Tata. Mentor specializing in GATE ME and core engineering placements.',
                'linkedin'          => 'https://linkedin.com/in/adityabose',
                'rate_per_minute'   => 10.00,
                'rating'            => 4.65,
                'total_sessions'    => 212,
            ],
            [
                'name'              => 'Nandini Rao',
                'email'             => 'nandini.rao@example.com',
                'gender'            => 'female',
                'field'             => 'Medicine',
                'company'           => 'AIIMS Delhi',
                'designation'       => 'Resident Doctor (MD)',
                'experience_years'  => 5,
                'expertise'         => ['NEET PG Prep', 'Clinical Skills', 'Research Writing', 'Medical Career'],
                'bio'               => 'AIIMS resident passionate about guiding MBBS students through PG entrance and research.',
                'linkedin'          => 'https://linkedin.com/in/nandiniaro',
                'rate_per_minute'   => 14.00,
                'rating'            => 4.90,
                'total_sessions'    => 178,
            ],
            [
                'name'              => 'Suresh Pillai',
                'email'             => 'suresh.pillai@example.com',
                'gender'            => 'male',
                'field'             => 'Civil Services',
                'company'           => 'IAS (Government of India)',
                'designation'       => 'IAS Officer (2015 Batch)',
                'experience_years'  => 9,
                'expertise'         => ['UPSC Strategy', 'Essay Writing', 'Mains Answer Writing', 'GS Papers'],
                'bio'               => 'IAS officer from Kerala cadre. Cleared UPSC in first attempt. Mentoring serious UPSC aspirants.',
                'linkedin'          => 'https://linkedin.com/in/sureshpillai',
                'rate_per_minute'   => 18.00,
                'rating'            => 4.96,
                'total_sessions'    => 634,
            ],
            [
                'name'              => 'Ishita Joshi',
                'email'             => 'ishita.joshi@example.com',
                'gender'            => 'female',
                'field'             => 'Architecture',
                'company'           => 'Morphogenesis',
                'designation'       => 'Project Architect',
                'experience_years'  => 7,
                'expertise'         => ['NATA Prep', 'Portfolio Design', 'AutoCAD', 'Sustainable Design'],
                'bio'               => 'Architect working on award-winning sustainable projects. Mentor for NATA and architecture college prep.',
                'linkedin'          => 'https://linkedin.com/in/ishitajoshi',
                'rate_per_minute'   => 11.00,
                'rating'            => 4.74,
                'total_sessions'    => 124,
            ],
            [
                'name'              => 'Nikhil Chatterjee',
                'email'             => 'nikhil.chatterjee@example.com',
                'gender'            => 'male',
                'field'             => 'Investment & Trading',
                'company'           => 'Morgan Stanley',
                'designation'       => 'Equity Research Analyst',
                'experience_years'  => 6,
                'expertise'         => ['Stock Analysis', 'SEBI Grade A', 'Options Trading', 'Portfolio Management'],
                'bio'               => 'Equity researcher at Morgan Stanley. Helping students crack finance certifications and investment roles.',
                'linkedin'          => 'https://linkedin.com/in/nikhilchatterjee',
                'rate_per_minute'   => 16.00,
                'rating'            => 4.68,
                'total_sessions'    => 189,
            ],
            [
                'name'              => 'Pooja Desai',
                'email'             => 'pooja.desai@example.com',
                'gender'            => 'female',
                'field'             => 'Content & Media',
                'company'           => 'Netflix India',
                'designation'       => 'Senior Content Strategist',
                'experience_years'  => 8,
                'expertise'         => ['Scriptwriting', 'Content Marketing', 'Social Media', 'Brand Storytelling'],
                'bio'               => 'Content strategist at Netflix India. Mentoring aspiring writers and content creators.',
                'linkedin'          => 'https://linkedin.com/in/poojadesai',
                'rate_per_minute'   => 12.00,
                'rating'            => 4.82,
                'total_sessions'    => 201,
            ],
            [
                'name'              => 'Abhishek Malhotra',
                'email'             => 'abhishek.malhotra@example.com',
                'gender'            => 'male',
                'field'             => 'DevOps & Cloud',
                'company'           => 'AWS',
                'designation'       => 'Solutions Architect',
                'experience_years'  => 7,
                'expertise'         => ['AWS', 'Kubernetes', 'CI/CD', 'Terraform', 'Docker'],
                'bio'               => 'AWS SA helping engineers get cloud certified and transition to DevOps roles.',
                'linkedin'          => 'https://linkedin.com/in/abhishekmalhotra',
                'rate_per_minute'   => 15.00,
                'rating'            => 4.87,
                'total_sessions'    => 245,
            ],
            [
                'name'              => 'Lakshmi Subramaniam',
                'email'             => 'lakshmi.subramaniam@example.com',
                'gender'            => 'female',
                'field'             => 'MBA Prep',
                'company'           => 'IIM Ahmedabad',
                'designation'       => 'MBA Graduate & Consultant',
                'experience_years'  => 5,
                'expertise'         => ['CAT Prep', 'WAT-PI', 'B-School Selection', 'MBA Essays'],
                'bio'               => 'IIM-A alum with 99.8 percentile in CAT. Full-time mentor for MBA aspirants.',
                'linkedin'          => 'https://linkedin.com/in/lakshmisubramaniam',
                'rate_per_minute'   => 19.00,
                'rating'            => 4.94,
                'total_sessions'    => 712,
            ],
            [
                'name'              => 'Gaurav Tiwari',
                'email'             => 'gaurav.tiwari@example.com',
                'gender'            => 'male',
                'field'             => 'Blockchain & Web3',
                'company'           => 'Polygon',
                'designation'       => 'Blockchain Developer',
                'experience_years'  => 5,
                'expertise'         => ['Solidity', 'Smart Contracts', 'DeFi', 'Web3.js', 'NFTs'],
                'bio'               => 'Core dev at Polygon. Mentoring engineers looking to enter the blockchain space.',
                'linkedin'          => 'https://linkedin.com/in/gauravtiwari',
                'rate_per_minute'   => 17.00,
                'rating'            => 4.72,
                'total_sessions'    => 132,
            ],
            [
                'name'              => 'Rekha Anand',
                'email'             => 'rekha.anand@example.com',
                'gender'            => 'female',
                'field'             => 'Psychology & Counselling',
                'company'           => 'Vandrevala Foundation',
                'designation'       => 'Licensed Psychologist',
                'experience_years'  => 13,
                'expertise'         => ['Career Counselling', 'Stress Management', 'ADHD Coaching', 'Study Skills'],
                'bio'               => 'Licensed psychologist helping students navigate academic pressure and career anxiety.',
                'linkedin'          => 'https://linkedin.com/in/rekhaanand',
                'rate_per_minute'   => 14.00,
                'rating'            => 4.89,
                'total_sessions'    => 389,
            ],
        ];

        foreach ($mentors as $index => $mentor) {
            $genderIndex = $index < 10 ? $index : $index - 10;
            $id = DB::table('users')->insertGetId([
                'name'                => $mentor['name'],
                'email'               => $mentor['email'],
                'password'            => Hash::make('password123'),
                'role'                => 'mentor',
                'gender'              => $mentor['gender'],
                'field'               => $mentor['field'],
                'company'             => $mentor['company'],
                'designation'         => $mentor['designation'],
                'experience_years'    => $mentor['experience_years'],
                'expertise'           => json_encode($mentor['expertise']),
                'bio'                 => $mentor['bio'],
                'linkedin'            => $mentor['linkedin'],
                'rate_per_minute'     => $mentor['rate_per_minute'],
                'rating'              => $mentor['rating'],
                'total_sessions'      => $mentor['total_sessions'],
                'wallet_balance'      => round(rand(500, 20000) / 100 * 100, 2),
                'avatar_url'          => $this->getMentorAvatar($index, $mentor['gender']),
                'phone'               => '+91' . rand(7000000000, 9999999999),
                'college'             => null,
                'year'                => null,
                'is_active'           => true,
                'mentor_status'       => 'approved',
                'subscription_plan'   => 'pro',
                'onboarding_step'     => 5,
                'onboarding_completed'=> true,
                'education_stream'    => null,
                'career_goals'        => null,
                'strengths'           => json_encode(['Communication', 'Technical Depth', 'Patience']),
                'preferences'         => json_encode(['preferred_time' => 'evening', 'session_length' => 60]),
                'assigned_mentor_id'  => null,
                'created_at'          => now()->subDays(rand(90, 400)),
                'updated_at'          => now(),
            ]);

            $mentorIds[] = $id;
        }

        // ─────────────────────────────────────────────
        // 35 MENTEES
        // ─────────────────────────────────────────────
        $mentees = [
            ['name' => 'Aarav Shah',        'email' => 'aarav.shah@student.com',        'gender' => 'male',   'field' => 'Computer Science',      'college' => 'IIT Bombay',          'year' => '3rd Year', 'stream' => 'Engineering', 'goals' => ['Get into FAANG', 'Master DSA']],
            ['name' => 'Bhavna Reddy',      'email' => 'bhavna.reddy@student.com',      'gender' => 'female', 'field' => 'Data Science',           'college' => 'BITS Pilani',         'year' => '4th Year', 'stream' => 'Engineering', 'goals' => ['Crack ML interviews', 'Research publication']],
            ['name' => 'Chirag Agarwal',    'email' => 'chirag.agarwal@student.com',    'gender' => 'male',   'field' => 'MBA',                    'college' => 'Delhi University',    'year' => 'Final Year','stream' => 'Commerce',    'goals' => ['Get into IIM', 'Score 99+ in CAT']],
            ['name' => 'Deepika Menon',     'email' => 'deepika.menon@student.com',     'gender' => 'female', 'field' => 'UX Design',              'college' => 'NID Ahmedabad',       'year' => '2nd Year', 'stream' => 'Design',      'goals' => ['Land design internship', 'Build portfolio']],
            ['name' => 'Eshan Kulkarni',    'email' => 'eshan.kulkarni@student.com',    'gender' => 'male',   'field' => 'Civil Services',         'college' => 'Fergusson College',   'year' => 'Graduate',  'stream' => 'Arts',        'goals' => ['Clear UPSC Prelims', 'Build daily study habit']],
            ['name' => 'Fatima Sheikh',     'email' => 'fatima.sheikh@student.com',     'gender' => 'female', 'field' => 'Medicine',               'college' => 'Grant Medical College','year' => '2nd MBBS', 'stream' => 'Medical',     'goals' => ['Crack NEET PG', 'Specialise in Cardiology']],
            ['name' => 'Gaurang Joshi',     'email' => 'gaurang.joshi@student.com',     'gender' => 'male',   'field' => 'Finance',                'college' => 'Symbiosis',           'year' => 'MBA 1st',  'stream' => 'Commerce',    'goals' => ['Investment banking career', 'Clear CFA L1']],
            ['name' => 'Harshita Bansal',   'email' => 'harshita.bansal@student.com',   'gender' => 'female', 'field' => 'Marketing',              'college' => 'MICA',                'year' => 'MBA 2nd',  'stream' => 'Management',  'goals' => ['Brand management role', 'Build personal brand']],
            ['name' => 'Ishan Trivedi',     'email' => 'ishan.trivedi@student.com',     'gender' => 'male',   'field' => 'Cybersecurity',          'college' => 'VIT Vellore',         'year' => '3rd Year', 'stream' => 'Engineering', 'goals' => ['CEH certification', 'Bug bounty hunting']],
            ['name' => 'Jyoti Kumari',      'email' => 'jyoti.kumari@student.com',      'gender' => 'female', 'field' => 'Law',                    'college' => 'NLU Delhi',           'year' => '4th Year', 'stream' => 'Law',         'goals' => ['Corporate law internship', 'Crack CLAT PG']],
            ['name' => 'Kabir Saxena',      'email' => 'kabir.saxena@student.com',      'gender' => 'male',   'field' => 'Entrepreneurship',       'college' => 'IIT Delhi',           'year' => '4th Year', 'stream' => 'Engineering', 'goals' => ['Launch EdTech startup', 'Find co-founder']],
            ['name' => 'Lavanya Pillai',    'email' => 'lavanya.pillai@student.com',    'gender' => 'female', 'field' => 'Architecture',           'college' => 'SPA Delhi',           'year' => '3rd Year', 'stream' => 'Architecture','goals' => ['Win design competition', 'Internship at top firm']],
            ['name' => 'Manish Dubey',      'email' => 'manish.dubey@student.com',      'gender' => 'male',   'field' => 'DevOps',                 'college' => 'NIT Trichy',          'year' => 'Final Year','stream' => 'Engineering', 'goals' => ['AWS certification', 'DevOps engineer role']],
            ['name' => 'Nisha Gogoi',       'email' => 'nisha.gogoi@student.com',       'gender' => 'female', 'field' => 'Content Creation',       'college' => 'Symbiosis Media',     'year' => '2nd Year', 'stream' => 'Media',       'goals' => ['Get into Netflix/Amazon', 'Publish short film']],
            ['name' => 'Om Prakash',        'email' => 'om.prakash@student.com',        'gender' => 'male',   'field' => 'Mechanical Engineering', 'college' => 'IIT Kanpur',          'year' => '3rd Year', 'stream' => 'Engineering', 'goals' => ['GATE top 100 rank', 'Core PSU job']],
            ['name' => 'Pallavi Ghosh',     'email' => 'pallavi.ghosh@student.com',     'gender' => 'female', 'field' => 'Psychology',             'college' => 'Jamia Millia',        'year' => 'MA 1st',   'stream' => 'Arts',        'goals' => ['Counselling certification', 'School counsellor job']],
            ['name' => 'Qasim Ali',         'email' => 'qasim.ali@student.com',         'gender' => 'male',   'field' => 'Blockchain',             'college' => 'IIIT Hyderabad',      'year' => 'Final Year','stream' => 'Engineering', 'goals' => ['Web3 developer role', 'Build DeFi project']],
            ['name' => 'Riya Malhotra',     'email' => 'riya.malhotra@student.com',     'gender' => 'female', 'field' => 'Investment',             'college' => 'SRCC',                'year' => '3rd Year', 'stream' => 'Commerce',    'goals' => ['Equity research role', 'SEBI Grade A']],
            ['name' => 'Siddharth Nair',    'email' => 'siddharth.nair@student.com',    'gender' => 'male',   'field' => 'Software Engineering',   'college' => 'BITS Goa',            'year' => '2nd Year', 'stream' => 'Engineering', 'goals' => ['Open source contributions', 'SDE internship']],
            ['name' => 'Tanvi Mishra',      'email' => 'tanvi.mishra@student.com',      'gender' => 'female', 'field' => 'HR',                     'college' => 'Tata Institute',      'year' => 'MSW 2nd',  'stream' => 'Social Work', 'goals' => ['HR generalist role', 'SHRM certification']],
            ['name' => 'Uday Rathore',      'email' => 'uday.rathore@student.com',      'gender' => 'male',   'field' => 'Civil Services',         'college' => 'Rajasthan University','year' => 'Graduate',  'stream' => 'Arts',        'goals' => ['UPSC 2025 attempt', 'Optional subject mastery']],
            ['name' => 'Vandana Singh',     'email' => 'vandana.singh@student.com',     'gender' => 'female', 'field' => 'Data Science',           'college' => 'Anna University',     'year' => 'Final Year','stream' => 'Engineering', 'goals' => ['Data analyst internship', 'Kaggle competitions']],
            ['name' => 'Waqar Ahmed',       'email' => 'waqar.ahmed@student.com',       'gender' => 'male',   'field' => 'Product Management',     'college' => 'MDI Gurgaon',         'year' => 'MBA 1st',  'stream' => 'Management',  'goals' => ['PM role at startup', 'Product sense interviews']],
            ['name' => 'Xenia D\'Souza',    'email' => 'xenia.dsouza@student.com',      'gender' => 'female', 'field' => 'Marketing',              'college' => 'St. Xavier\'s',       'year' => '3rd Year', 'stream' => 'Commerce',    'goals' => ['Digital marketing job', 'Google Analytics cert']],
            ['name' => 'Yash Pandey',       'email' => 'yash.pandey@student.com',       'gender' => 'male',   'field' => 'Entrepreneurship',       'college' => 'IIM Bangalore',       'year' => 'MBA 2nd',  'stream' => 'Management',  'goals' => ['Launch fintech startup', 'Seed funding']],
            ['name' => 'Zara Hussain',      'email' => 'zara.hussain@student.com',      'gender' => 'female', 'field' => 'UX Design',              'college' => 'Pearl Academy',       'year' => '3rd Year', 'stream' => 'Design',      'goals' => ['UI/UX internship', 'Master Figma prototyping']],
            ['name' => 'Aryan Kapoor',      'email' => 'aryan.kapoor@student.com',      'gender' => 'male',   'field' => 'Finance',                'college' => 'NMIMS Mumbai',        'year' => 'MBA 1st',  'stream' => 'Commerce',    'goals' => ['Investment banking analyst', 'CFA L1']],
            ['name' => 'Bindiya Rao',       'email' => 'bindiya.rao@student.com',       'gender' => 'female', 'field' => 'Software Engineering',   'college' => 'IIIT Bangalore',      'year' => '3rd Year', 'stream' => 'Engineering', 'goals' => ['Backend developer role', 'System design mastery']],
            ['name' => 'Chetan Verma',      'email' => 'chetan.verma@student.com',      'gender' => 'male',   'field' => 'Cybersecurity',          'college' => 'Amity University',    'year' => 'Final Year','stream' => 'Engineering', 'goals' => ['SOC analyst job', 'CompTIA Security+']],
            ['name' => 'Drishti Patel',     'email' => 'drishti.patel@student.com',     'gender' => 'female', 'field' => 'Medicine',               'college' => 'BJ Medical College',  'year' => '3rd MBBS', 'stream' => 'Medical',     'goals' => ['MD in Dermatology', 'Research paper publication']],
            ['name' => 'Eshaan Bose',       'email' => 'eshaan.bose@student.com',       'gender' => 'male',   'field' => 'DevOps',                 'college' => 'NIT Durgapur',        'year' => 'Final Year','stream' => 'Engineering', 'goals' => ['Cloud engineer role', 'Kubernetes certification']],
            ['name' => 'Freya Thomas',      'email' => 'freya.thomas@student.com',      'gender' => 'female', 'field' => 'Content Creation',       'college' => 'Sophia College',      'year' => '2nd Year', 'stream' => 'Media',       'goals' => ['YouTube channel growth', 'Brand deals']],
            ['name' => 'Girish Naik',       'email' => 'girish.naik@student.com',       'gender' => 'male',   'field' => 'Mechanical Engineering', 'college' => 'NIT Surathkal',       'year' => '4th Year', 'stream' => 'Engineering', 'goals' => ['GATE AIR under 500', 'M.Tech from IIT']],
            ['name' => 'Himani Tiwari',     'email' => 'himani.tiwari@student.com',     'gender' => 'female', 'field' => 'Law',                    'college' => 'NLU Jodhpur',         'year' => '3rd Year', 'stream' => 'Law',         'goals' => ['Litigation career', 'Moot court winner']],
            ['name' => 'Ishaan Gupta',      'email' => 'ishaan.gupta@student.com',      'gender' => 'male',   'field' => 'MBA',                    'college' => 'Christ University',   'year' => 'Final Year','stream' => 'Commerce',    'goals' => ['IIM Calcutta', 'GDPI preparation']],
        ];

        // Field → mentor index mapping (so mentees get a relevant mentor)
        $fieldToMentorIndex = [
            'Software Engineering'   => 0,   // Arjun Mehta
            'Data Science'           => 1,   // Priya Sharma
            'Product Management'     => 2,   // Rahul Verma
            'UX Design'              => 3,   // Sneha Iyer
            'Finance'                => 4,   // Karan Gupta
            'Marketing'              => 5,   // Divya Nair
            'Entrepreneurship'       => 6,   // Vikram Singh
            'HR'                     => 7,   // Anjali Patel
            'Cybersecurity'          => 8,   // Rohan Kapoor
            'Law'                    => 9,   // Meera Krishnan
            'Mechanical Engineering' => 10,  // Aditya Bose
            'Medicine'               => 11,  // Nandini Rao
            'Civil Services'         => 12,  // Suresh Pillai
            'Architecture'           => 13,  // Ishita Joshi
            'Investment'             => 14,  // Nikhil Chatterjee
            'Content Creation'       => 15,  // Pooja Desai
            'DevOps'                 => 16,  // Abhishek Malhotra
            'MBA'                    => 17,  // Lakshmi Subramaniam
            'Blockchain'             => 18,  // Gaurav Tiwari
            'Psychology'             => 19,  // Rekha Anand
        ];

        foreach ($mentees as $index => $mentee) {
            // Assign relevant mentor; fallback to random if field not mapped
            $mentorIndex     = $fieldToMentorIndex[$mentee['field']] ?? array_rand($mentorIds);
            $assignedMentorId = $mentorIds[$mentorIndex];

            DB::table('users')->insert([
                'name'                => $mentee['name'],
                'email'               => $mentee['email'],
                'password'            => Hash::make('password123'),
                'role'                => 'mentee',
                'gender'              => $mentee['gender'],
                'field'               => $mentee['field'],
                'college'             => $mentee['college'],
                'year'                => $mentee['year'],
                'education_stream'    => $mentee['stream'],
                'career_goals'        => json_encode($mentee['goals']),
                'avatar_url'          => $this->getMenteeAvatar($index, $mentee['gender']),
                'phone'               => '+91' . rand(7000000000, 9999999999),
                'bio'                 => "I'm a {$mentee['year']} student at {$mentee['college']} looking to grow in {$mentee['field']}.",
                'wallet_balance'      => round(rand(0, 5000) / 100 * 100, 2),
                'rating'              => 0,
                'total_sessions'      => rand(0, 20),
                'is_active'           => true,
                'mentor_status'       => 'approved',
                'subscription_plan'   => collect(['free', 'basic', 'pro'])->random(),
                'assigned_mentor_id'  => $assignedMentorId,
                'onboarding_step'     => rand(3, 5),
                'onboarding_completed'=> true,
                'expertise'           => null,
                'strengths'           => json_encode(['Curiosity', 'Hard Work', 'Consistency']),
                'preferences'         => json_encode(['preferred_time' => 'weekend', 'session_length' => 30]),
                'company'             => null,
                'designation'         => null,
                'experience_years'    => 0,
                'linkedin'            => null,
                'rate_per_minute'     => 0,
                'created_at'          => now()->subDays(rand(10, 180)),
                'updated_at'          => now(),
            ]);
        }

        $this->command->info('✅ Seeded 20 mentors and 35 mentees successfully!');
        $this->command->info('🔑 Default password for all users: password123');
        $this->command->info('🖼️  Profile images loaded from pravatar.cc (Google CDN backed)');
    }
}
