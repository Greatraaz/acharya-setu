<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\DownloadCentre;
use App\Models\GatedInsightItem;
use App\Models\InsightEvent;
use App\Models\InsightVideo;
use App\Models\LearnerSuccessStory;
use App\Models\MentorshipGuide;
use App\Models\Podcast;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WhitePaper;
use App\Support\GatedInsightKind;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UtilitiesContentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('role', 'admin')->value('id');

        $this->seedPlaceholderFiles();

        $this->seedBlogs($adminId);
        $this->seedWhitePapers($adminId);
        $this->seedCaseStudies($adminId);
        $this->seedTestimonials($adminId);
        $this->seedLearnerSuccessStories($adminId);
        $this->seedMentorshipGuides($adminId);
        $this->seedCareerGuides($adminId);
        $this->seedIndustryReports($adminId);
        $this->seedPodcasts($adminId);
        $this->seedVideos($adminId);
        $this->seedDownloads($adminId);
        $this->seedEvents($adminId);

        $this->command?->info('Utilities dummy content seeded (3+ entries per module).');
    }

    private function seedPlaceholderFiles(): void
    {
        Storage::disk('public')->makeDirectory('utilities/seed');

        $imagePath = 'utilities/seed/vedrix-cover.svg';
        if (! Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->put($imagePath, <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0f172a"/>
      <stop offset="100%" stop-color="#1d4ed8"/>
    </linearGradient>
  </defs>
  <rect width="800" height="450" fill="url(#g)"/>
  <text x="400" y="220" text-anchor="middle" fill="#ffffff" font-family="Arial,sans-serif" font-size="42" font-weight="700">Vedrix</text>
  <text x="400" y="270" text-anchor="middle" fill="#bfdbfe" font-family="Arial,sans-serif" font-size="20">Mentorship Insights</text>
</svg>
SVG);
        }

        $docPath = 'utilities/seed/vedrix-guide.txt';
        if (! Storage::disk('public')->exists($docPath)) {
            Storage::disk('public')->put(
                $docPath,
                "Vedrix Mentorship Resource Guide\n\nThis is a sample downloadable resource for mentees and mentors on the Vedrix platform.\n"
            );
        }
    }

    private function cover(): string
    {
        return 'utilities/seed/vedrix-cover.svg';
    }

    private function seedBlogs(?int $adminId): void
    {
        $items = [
            [
                'title' => 'How Mentorship Accelerates Career Growth on Vedrix',
                'category' => 'Mentorship',
                'author' => 'Vedrix Team',
                'description' => '<p>Discover how structured mentor–mentee journeys on Vedrix help students build skills faster, stay accountable, and land better opportunities.</p><p>From weekly check-ins to curriculum milestones, learn the habits that turn guidance into measurable progress.</p>',
            ],
            [
                'title' => '5 Ways Mentees Can Get More From Every Session',
                'category' => 'Mentee Tips',
                'author' => 'Aaradhya Negi',
                'description' => '<p>Come prepared with goals, share updates early, and ask focused questions. This guide shows mentees how to maximize every Vedrix mentoring session.</p>',
            ],
            [
                'title' => 'Building a Strong Mentor Profile That Attracts Mentees',
                'category' => 'Mentor Tips',
                'author' => 'Raaz',
                'description' => '<p>A clear bio, defined expertise, and consistent availability help mentors stand out. Learn how top Vedrix mentors present themselves and grow their mentee network.</p>',
            ],
        ];

        foreach ($items as $i => $item) {
            Blog::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'author' => $item['author'],
                    'blog_date' => now()->subDays(10 - $i)->toDateString(),
                    'status' => 'active',
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'meta_title' => $item['title'],
                    'meta_description' => strip_tags($item['description']),
                    'meta_keywords' => 'vedrix, mentorship, career, mentee, mentor',
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedWhitePapers(?int $adminId): void
    {
        $items = [
            [
                'title' => 'The State of Digital Mentorship in Higher Education',
                'description' => '<p>An overview of how platforms like Vedrix connect students with industry mentors, and why guided learning journeys improve outcomes.</p>',
            ],
            [
                'title' => 'Curriculum-Led Mentoring: A Framework for Skill Building',
                'description' => '<p>Explore how month-wise and week-wise curricula paired with mentor reviews create clarity for mentees and accountability for mentors.</p>',
            ],
            [
                'title' => 'Measuring Mentorship Impact: Progress, Reviews & Retention',
                'description' => '<p>Metrics that matter—completion rates, review turnaround, session consistency—and how Vedrix surfaces them for mentors and admins.</p>',
            ],
        ];

        foreach ($items as $item) {
            WhitePaper::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'status' => 'active',
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedCaseStudies(?int $adminId): void
    {
        $items = [
            [
                'title' => 'From Confused Fresher to Internship-Ready in 3 Months',
                'industry' => 'Technology',
                'description' => '<p>A mentee used Vedrix curriculum tasks, weekly mentor reviews, and mock interviews to land a product internship.</p>',
                'result' => 'Secured a product internship within 12 weeks of active mentoring.',
            ],
            [
                'title' => 'Mentor-Led Career Switch into Data Analytics',
                'industry' => 'Data & Analytics',
                'description' => '<p>With a dedicated mentor track, assessments, and project feedback, the mentee rebuilt their portfolio around analytics use cases.</p>',
                'result' => 'Completed analytics track and received 2 interview callbacks.',
            ],
            [
                'title' => 'College Cohort Mentoring Improves Placement Readiness',
                'industry' => 'Education',
                'description' => '<p>A campus cohort used Vedrix community channels, shared assessments, and group webinars to prepare for placements together.</p>',
                'result' => '85% of cohort mentees completed readiness assessments before placement season.',
            ],
        ];

        foreach ($items as $item) {
            CaseStudy::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'industry' => $item['industry'],
                    'status' => 'active',
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'result' => $item['result'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedTestimonials(?int $adminId): void
    {
        $items = [
            [
                'name' => 'Vijay Kumar',
                'designation' => 'Mentee · Computer Science',
                'message' => 'Vedrix helped me stay consistent. My mentor reviewed every task and MCQ, and the journey tracker made progress feel real—not just another course.',
            ],
            [
                'name' => 'Aaradhya Negi',
                'designation' => 'Mentor · Product & Career Guidance',
                'message' => 'The mentor dashboard makes it easy to review submissions, guide mentees through curriculum weeks, and keep conversations going in community channels.',
            ],
            [
                'name' => 'Kiran Negi',
                'designation' => 'Mentee · Aspiring Data Analyst',
                'message' => 'Assessments and mentor feedback on Vedrix gave me clarity on what to improve. I finally know what “placement ready” looks like for me.',
            ],
        ];

        foreach ($items as $item) {
            Testimonial::query()->updateOrCreate(
                ['name' => $item['name'], 'designation' => $item['designation']],
                [
                    'image' => $this->cover(),
                    'message' => $item['message'],
                    'status' => 'active',
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedLearnerSuccessStories(?int $adminId): void
    {
        $items = [
            [
                'name' => 'Ananya Sharma',
                'designation' => 'Mentee · Software Engineer at a product startup',
                'message' => '<p>I joined Vedrix as a confused final-year student. Weekly mentor reviews, mock interviews, and a clear 12-week curriculum helped me land my first product-engineering role.</p><p>The biggest shift was accountability — I always knew what to finish before the next session.</p>',
            ],
            [
                'name' => 'Rohit Mehta',
                'designation' => 'Mentee · Career switch into Data Analytics',
                'message' => '<p>Switching from operations to analytics felt overwhelming until my Vedrix mentor broke it into portfolio projects, SQL drills, and interview stories.</p><p>Within a few months I had a sharper resume, two callbacks, and a role I actually wanted.</p>',
            ],
            [
                'name' => 'Priya Nair',
                'designation' => 'Mentee · Product Associate',
                'message' => '<p>Vedrix gave me structure I never got from random YouTube advice. Case-interview practice, mentor feedback, and a tracked journey made product roles feel achievable.</p><p>I still reuse the session notes from my first 90 days.</p>',
            ],
        ];

        foreach ($items as $item) {
            LearnerSuccessStory::query()->updateOrCreate(
                ['name' => $item['name'], 'designation' => $item['designation']],
                [
                    'image' => $this->cover(),
                    'message' => $item['message'],
                    'status' => LearnerSuccessStory::STATUS_ACTIVE,
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedMentorshipGuides(?int $adminId): void
    {
        $items = [
            [
                'title' => 'How to Get the Most from Every 1-on-1 Mentorship Session',
                'description' => '<p>Come in with a goal, leave with a next action. This guide shows mentees how to prepare agendas, share progress early, and ask questions that unlock better advice.</p><ul><li>Set one outcome for the session</li><li>Send a 5-line update 24 hours before</li><li>Capture decisions and homework before you log off</li></ul><p>Use this playbook for the first month of any Vedrix mentorship.</p>',
            ],
            [
                'title' => 'A Mentor’s Framework for Giving Useful Feedback',
                'description' => '<p>Great mentors are specific, timely, and kind. Learn a simple loop: observe, name the gap, suggest the next practice, and review it next week.</p><p>Includes conversation starters for career, skills, and interview feedback without overwhelming the mentee.</p>',
            ],
            [
                'title' => 'Goal Setting for a 12-Week Mentorship Journey',
                'description' => '<p>Turn a vague “get better at interviews” into weekly checkpoints. This guide helps mentors and mentees pick 3 outcomes, map tasks, and review progress every fortnight.</p><p>Works for students, working professionals, and career switchers on Vedrix.</p>',
            ],
        ];

        foreach ($items as $item) {
            MentorshipGuide::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'status' => MentorshipGuide::STATUS_ACTIVE,
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedCareerGuides(?int $adminId): void
    {
        $this->seedGatedItems($adminId, GatedInsightKind::CAREER_GUIDE, [
            [
                'title' => 'Breaking into Product Management',
                'description' => '<p>A practical roadmap for students and engineers moving into product: case interviews, execution stories, and a reading list that actually gets used.</p><p>Covers how to talk about impact, structure a product sense answer, and build a 90-day learning plan with a Vedrix mentor.</p>',
            ],
            [
                'title' => 'First 90 Days in a Tech Consulting Role',
                'description' => '<p>How to manage client expectations, structure findings, and build credibility fast. Use this guide in your first quarter with a mentor reviewing weekly checkpoints.</p><ul><li>Week 1–2: map stakeholders</li><li>Week 3–6: own a workstream</li><li>Week 7–12: present with confidence</li></ul>',
            ],
            [
                'title' => 'System Design Interview Handbook for Early Careers',
                'description' => '<p>Core architectures, caching, databases, and interview pacing — without the noise. Pair each chapter with a mock session on Vedrix.</p><p>Includes a simple template for clarifying requirements, sketching trade-offs, and wrapping up on time.</p>',
            ],
        ]);
    }

    private function seedIndustryReports(?int $adminId): void
    {
        $this->seedGatedItems($adminId, GatedInsightKind::INDUSTRY_REPORT, [
            [
                'title' => 'Employability Outlook 2026: The Skill-Context Gap',
                'description' => '<p>Why degrees alone are not enough in modern hiring, and which contextual skills mentors should coach for interviews and internships.</p><p>This sample report summarises hiring signals across engineering, product, and analytics roles for Vedrix learners.</p>',
            ],
            [
                'title' => 'The Mentorship Multiplier in Early-Career Retention',
                'description' => '<p>How structured internal and external mentoring improves promotion velocity and reduces early attrition.</p><p>Use the findings with placement cells, campus partners, and mentees planning their first two years at work.</p>',
            ],
            [
                'title' => 'Tech Hiring Benchmarks: Skills, Salaries & Role Mix',
                'description' => '<p>A snapshot of in-demand skills, typical early-career salary bands, and where mentees should focus practice time this year.</p><p>Designed as a downloadable briefing you can share with mentors, colleges, and career teams.</p>',
            ],
        ]);
    }

    /**
     * @param  list<array{title:string,description:string}>  $items
     */
    private function seedGatedItems(?int $adminId, string $type, array $items): void
    {
        foreach ($items as $item) {
            GatedInsightItem::query()->updateOrCreate(
                [
                    'type' => $type,
                    'slug' => Str::slug($item['title']),
                ],
                [
                    'title' => $item['title'],
                    'status' => GatedInsightItem::STATUS_ACTIVE,
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedPodcasts(?int $adminId): void
    {
        $items = [
            [
                'title' => 'Vedrix Talks: Finding the Right Mentor',
                'description' => '<p>A conversation on choosing mentors by expertise, communication style, and career goals—plus how Vedrix matching helps.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'title' => 'Career Clarity Sessions for Students',
                'description' => '<p>Mentors discuss how short, focused sessions help mentees decide between roles, skills, and next steps.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            ],
            [
                'title' => 'Building Habits That Stick in Mentorship',
                'description' => '<p>Weekly reviews, curriculum checkpoints, and community support—habits that keep mentees moving forward on Vedrix.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
            ],
        ];

        foreach ($items as $item) {
            Podcast::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'image' => $this->cover(),
                    'description' => $item['description'],
                    'podcast_type' => Podcast::TYPE_YOUTUBE,
                    'audio' => null,
                    'youtube_url' => $item['youtube_url'],
                    'status' => 'active',
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedVideos(?int $adminId): void
    {
        $items = [
            [
                'title' => 'Welcome to Vedrix: Platform Walkthrough',
                'description' => '<p>Quick tour of mentee journey, mentor progress hub, assessments, and community channels.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'title' => 'How Mentors Review Curriculum Submissions',
                'description' => '<p>See how mentors approve tasks and MCQs, leave feedback, and unlock the next week for mentees.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            ],
            [
                'title' => 'Using Community Channels Effectively',
                'description' => '<p>Tips for asking better questions, replying in threads, and learning with your cohort on Vedrix.</p>',
                'youtube_url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
            ],
        ];

        foreach ($items as $item) {
            InsightVideo::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'youtube_url' => $item['youtube_url'],
                    'status' => 'active',
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedDownloads(?int $adminId): void
    {
        $doc = 'utilities/seed/vedrix-guide.txt';

        $items = [
            [
                'title' => 'Mentee Onboarding Checklist',
                'description' => '<p>A printable checklist for new mentees: complete profile, pick a mentor, join community, start week 1.</p>',
            ],
            [
                'title' => 'Mentor Session Prep Template',
                'description' => '<p>A simple template mentors can use before sessions—goals, blockers, homework, and next actions.</p>',
            ],
            [
                'title' => 'Placement Readiness Self-Assessment',
                'description' => '<p>Self-score your resume, projects, communication, and interview readiness before your next mentor review.</p>',
            ],
        ];

        foreach ($items as $item) {
            DownloadCentre::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'status' => 'active',
                    'image' => $this->cover(),
                    'document' => $doc,
                    'description' => $item['description'],
                    'created_by' => $adminId,
                ]
            );
        }
    }

    private function seedEvents(?int $adminId): void
    {
        $items = [
            [
                'type' => InsightEvent::TYPE_WEBINAR,
                'title' => 'Vedrix Live: Resume Reviews with Mentors',
                'speaker' => 'Raaz · Mentor',
                'location' => 'Online · Zoom',
                'start_date' => now()->addDays(14)->toDateString(),
                'end_date' => now()->addDays(14)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '19:30',
                'description' => '<p>Join mentors for live resume feedback tailored to tech and analytics roles.</p>',
                'who_should_attend' => 'Mentees preparing for internships or placements.',
                'what_you_will_learn' => 'How to structure impact bullets, showcase projects, and highlight mentorship outcomes.',
            ],
            [
                'type' => InsightEvent::TYPE_WEBINAR,
                'title' => 'Ask a Mentor: Breaking Into Product Roles',
                'speaker' => 'Aaradhya Negi · Mentor',
                'location' => 'Online · Vedrix Community',
                'start_date' => now()->addDays(21)->toDateString(),
                'end_date' => now()->addDays(21)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '18:00',
                'description' => '<p>Open Q&A on product thinking, case interviews, and building a portfolio with mentor guidance.</p>',
                'who_should_attend' => 'Students exploring product management careers.',
                'what_you_will_learn' => 'What mentors look for and how to practice product sense weekly.',
            ],
            [
                'type' => InsightEvent::TYPE_EVENT,
                'title' => 'Vedrix Mentorship Meetup — Career Fair Prep',
                'speaker' => 'Vedrix Mentors Panel',
                'location' => 'Hybrid · Campus + Online',
                'start_date' => now()->addDays(30)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'start_time' => '11:00',
                'end_time' => '15:00',
                'description' => '<p>Half-day meetup with mock interviews, mentor roundtables, and networking for Vedrix mentees.</p>',
                'event_agenda' => "11:00 Welcome\n12:00 Mock interviews\n13:30 Mentor roundtables\n14:30 Networking",
                'who_should_attend' => 'Active Vedrix mentees and mentors.',
                'what_you_will_learn' => 'Interview practice and peer learning with your cohort.',
            ],
        ];

        foreach ($items as $item) {
            InsightEvent::query()->updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                array_merge($item, [
                    'image' => $this->cover(),
                    'status' => 'active',
                    'faq' => 'Q: Is registration free?\nA: Yes for active Vedrix users.',
                    'created_by' => $adminId,
                ])
            );
        }
    }
}
