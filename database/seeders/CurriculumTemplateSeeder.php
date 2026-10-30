<?php

namespace Database\Seeders;

use App\Models\CurriculumMonth;
use App\Models\CurriculumTask;
use App\Models\CurriculumWeek;
use App\Models\EducationStream;
use App\Support\MentorIndustry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $keepSlugs = [];

        foreach ($this->templates() as $index => $template) {
            $keepSlugs[] = $template['slug'];
            $stream = EducationStream::query()->updateOrCreate(
                [
                    'is_template' => true,
                    'slug'        => $template['slug'],
                ],
                [
                    'mentee_id'        => null,
                    'mentor_id'        => null,
                    'name'             => $template['name'],
                    'icon'             => $template['icon'],
                    'color'            => $template['color'],
                    'description'      => $template['description'],
                    'is_active'        => true,
                    'expertise_stream' => $template['expertise_field'],
                    'expertise_field'  => $template['expertise_field'],
                    'expertise_tags'   => $template['expertise_tags'],
                    'sort_order'       => $index + 1,
                ]
            );

            $this->syncMonths($stream, $template['months']);
        }

        $legacyIds = EducationStream::query()
            ->templates()
            ->where(function ($q) use ($keepSlugs) {
                $q->whereNotIn('slug', $keepSlugs)
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('expertise_field')
                            ->whereNotIn('expertise_field', MentorIndustry::all());
                    });
            })
            ->whereNotIn('slug', $keepSlugs)
            ->pluck('id');

        if ($legacyIds->isNotEmpty()) {
            CurriculumMonth::query()->whereIn('stream_id', $legacyIds)->delete();
            EducationStream::query()->whereIn('id', $legacyIds)->delete();
        }
    }

    private function syncMonths(EducationStream $stream, array $months): void
    {
        $keepMonthIds = [];

        foreach ($months as $monthNumber => $monthData) {
            $month = CurriculumMonth::query()->updateOrCreate(
                [
                    'stream_id'    => $stream->id,
                    'month_number' => $monthNumber,
                ],
                [
                    'mentee_id'         => null,
                    'title'             => $monthData['title'],
                    'theme'             => $monthData['theme'],
                    'description'       => $monthData['description'],
                    'learning_outcomes' => $monthData['outcomes'],
                    'milestone_badge'   => $monthData['badge'],
                    'is_active'         => true,
                    'sort_order'        => $monthNumber,
                ]
            );
            $keepMonthIds[] = $month->id;

            foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                $week = CurriculumWeek::query()->updateOrCreate(
                    [
                        'month_id'    => $month->id,
                        'week_number' => $weekNumber,
                    ],
                    [
                        'mentee_id'    => null,
                        'title'        => $weekData['title'],
                        'focus'        => $weekData['focus'],
                        'mentor_guide' => $weekData['guide'] ?? null,
                        'is_active'    => true,
                        'sort_order'   => $weekNumber,
                    ]
                );

                $week->tasks()->delete();
                foreach ($weekData['tasks'] as $order => $task) {
                    CurriculumTask::create([
                        'week_id'           => $week->id,
                        'mentee_id'         => null,
                        'title'             => $task[0],
                        'description'       => $task[1],
                        'type'              => $task[2],
                        'order_index'       => $order,
                        'estimated_minutes' => $task[3],
                        'is_required'       => true,
                        'is_active'         => true,
                        'submission_type'   => $task[4],
                    ]);
                }
            }
        }

        CurriculumMonth::query()
            ->where('stream_id', $stream->id)
            ->whereNotIn('id', $keepMonthIds)
            ->delete();
    }

    private function templates(): array
    {
        $defs = [
            ['Engineering & Technology', '💻', '#4f46e5', ['DSA', 'System Design', 'Software Engineering'], 'Programming, systems, projects, and interviews for engineering mentees.', $this->cseMonths()],
            ['Product Management', '🧭', '#4338ca', ['Product Sense', 'Roadmaps', 'Discovery'], 'Discovery, roadmaps, delivery, and PM interviews.', $this->productMonths()],
            ['Design & UX / UI', '🎨', '#c026d3', ['UX Research', 'UI', 'Figma'], 'Research, UI craft, design systems, and a portfolio case.', $this->designMonths()],
            ['Finance & Investment Banking', '💰', '#b45309', ['Accounting', 'Modelling', 'Markets'], 'Accounting, analysis, markets, modelling, and finance interviews.', $this->financeMonths()],
            ['MBA & Management Consulting', '📊', '#1d4ed8', ['Case Interviews', 'Strategy', 'Leadership'], 'Business fundamentals, cases, leadership, and a strategy capstone.', $this->managementMonths()],
            ['Marketing & Growth', '📈', '#db2777', ['Growth', 'Brand', 'Performance'], 'Positioning, channels, experiments, and a growth case.', $this->marketingMonths()],
            ['Data Science & AI / ML', '🤖', '#0f766e', ['Python', 'ML', 'SQL'], 'Data wrangling, modelling, ML projects, and interview prep.', $this->dataMonths()],
            ['Law & Legal', '⚖️', '#334155', ['Research', 'Drafting', 'Litigation'], 'Legal research, drafting, chambers/firm prep, and ethics.', $this->lawMonths()],
            ['Medicine & Healthcare', '🩺', '#be123c', ['Clinical', 'NEET PG', 'Healthcare'], 'Clinical foundations, exams, research, and specialty choice.', $this->healthMonths()],
            ['Arts & Humanities', '🧠', '#7c3aed', ['Writing', 'Research', 'Portfolio'], 'Research, writing, applied projects, and career options in the humanities.', $this->psychologyMonths()],
            ['Government & Public Policy', '🏛️', '#1e3a8a', ['Policy', 'UPSC', 'Governance'], 'Polity, policy memos, optional/exam prep, and public-sector careers.', $this->policyMonths()],
            ['Education & Academia', '🎓', '#0369a1', ['Teaching', 'Research', 'Pedagogy'], 'Teaching practice, research methods, and academic/edtech careers.', $this->educationMonths()],
            ['Entrepreneurship & Startups', '🚀', '#be185d', ['Founder', 'MVP', 'Fundraising'], 'Problem, MVP, customers, and fundraising basics.', $this->startupMonths()],
        ];

        $out = [];
        foreach ($defs as $row) {
            [$industry, $icon, $color, $tags, $description, $months] = $row;
            $out[] = [
                'slug'             => 'tpl-'.Str::slug($industry),
                'name'             => $industry.' Path',
                'icon'             => $icon,
                'color'            => $color,
                'expertise_field'  => $industry,
                'expertise_tags'   => $tags,
                'description'      => $description,
                'months'           => $months,
            ];
        }

        return $out;
    }

    private function productMonths(): array
    {
        return $this->sixMonths([
            [1, 'Product foundations', 'Fundamentals', 'What a PM actually owns.', 'PM Starter', [
                ['Role map', 'Discovery vs delivery vs go-to-market.', 'Write the PM job the mentee wants in one page.', 'reading', 60, 'text'],
                ['User problems', 'Jobs-to-be-done.', 'Interview or observe 3 users; log pains.', 'task', 90, 'text'],
                ['Metrics literacy', 'Activation, retention, revenue.', 'Define 6 metrics for a known product.', 'task', 75, 'text'],
                ['Problem brief', 'One sharp problem.', '1-page problem statement.', 'project', 60, 'file'],
            ]],
            [2, 'Discovery', 'Research', 'Talk to users before building.', 'Discoverer', [
                ['Research plan', 'Who, what, why.', 'Write a 5-interview script.', 'task', 45, 'text'],
                ['Synthesis', 'Patterns not quotes.', 'Affinity map of 8 insights.', 'task', 90, 'file'],
                ['Opportunity sizing', 'Reach × value × effort.', 'Score 5 opportunities.', 'task', 60, 'file'],
                ['PRD lite', 'Scope a v1.', 'One-pager with non-goals.', 'project', 90, 'file'],
            ]],
            [3, 'Roadmaps & delivery', 'Build', 'Sequence work with engineering.', 'Shipped', [
                ['Roadmap story', 'Now / next / later.', 'Draft a 2-quarter view.', 'task', 60, 'file'],
                ['Spec a slice', 'Acceptance criteria.', 'User stories for one epic.', 'task', 75, 'text'],
                ['Ceremony design', 'Standup, review, retro.', 'Write a team cadence.', 'reading', 40, 'text'],
                ['Launch checklist', 'Comms, metrics, rollback.', 'Checklist for a feature launch.', 'project', 60, 'file'],
            ]],
            [4, 'Product sense project', 'Craft', 'A case that can go in interviews.', 'Product Sense', [
                ['Pick a product', 'Improve or 0→1.', 'Write the prompt and constraints.', 'task', 40, 'text'],
                ['Design the solution', 'Flows and trade-offs.', '5-slide product sense case.', 'project', 150, 'file'],
                ['Prioritise', 'What you would not build.', 'RICE or similar with commentary.', 'task', 45, 'file'],
                ['Critique', 'Mentor red-team.', 'Revise from feedback.', 'reflection', 40, 'text'],
            ]],
            [5, 'PM interviews', 'Career', 'Product sense + execution + behavioural.', 'Interview Ready', [
                ['Resume for PMs', 'Impact bullets.', 'Rewrite 6 bullets with metrics.', 'task', 75, 'file'],
                ['Story bank', 'Leadership and conflict.', '8 STAR stories.', 'reflection', 60, 'text'],
                ['Case drills', 'Estimate and design.', 'Log 4 practice cases.', 'task', 120, 'text'],
                ['Mock loop', 'Full PM loop.', 'Notes from one mock.', 'task', 90, 'text'],
            ]],
            [6, 'Land the role', 'Launch', 'Applications and first 90 days.', 'Launch', [
                ['Target list', 'Companies and team types.', '15 roles with why-this-team.', 'task', 45, 'text'],
                ['Applications', 'Tailored.', '8 applications sent.', 'task', 90, 'text'],
                ['Offer filter', 'Scope vs brand vs pay.', 'Decision rubric.', 'reading', 30, 'text'],
                ['90-day plan', 'Listen, ship, relationships.', '30-60-90 for a PM seat.', 'project', 60, 'text'],
            ]],
        ]);
    }

    private function designMonths(): array
    {
        return $this->sixMonths([
            [1, 'Design craft foundations', 'Fundamentals', 'See, sketch, and give critique.', 'Craft', [
                ['Visual literacy', 'Hierarchy, type, color.', 'Redline 3 screens you admire.', 'reading', 60, 'file'],
                ['Figma hygiene', 'Auto-layout, components.', 'Rebuild one screen pixel-close.', 'task', 120, 'link'],
                ['Accessibility basics', 'Contrast, tap targets.', 'Audit 5 issues on a live site.', 'task', 60, 'text'],
                ['Critique notes', 'Say why, not I like.', 'Written critique of a peer file.', 'reflection', 40, 'text'],
            ]],
            [2, 'UX research', 'Research', 'Design from evidence.', 'Researcher', [
                ['Plan a study', 'Goal and method.', 'Recruit script + 5 questions.', 'task', 45, 'text'],
                ['Run sessions', 'Listen more than talk.', 'Notes from 3 conversations.', 'task', 90, 'file'],
                ['Journey map', 'Current experience.', 'As-is map with pain points.', 'project', 90, 'file'],
                ['Insight memo', 'So what for design.', '1-page insight brief.', 'reflection', 40, 'file'],
            ]],
            [3, 'UI systems', 'UI', 'Consistent product UI.', 'Systems', [
                ['Audit a product', 'Patterns and gaps.', 'Screenshot board of 12 patterns.', 'task', 75, 'file'],
                ['Component set', 'Buttons to forms.', 'Mini library in Figma.', 'project', 150, 'link'],
                ['Responsive pass', 'Mobile first.', 'Adapt 2 screens to small viewport.', 'task', 90, 'link'],
                ['Handoff', 'Dev-ready.', 'Specs + redlines for one flow.', 'task', 60, 'file'],
            ]],
            [4, 'Portfolio case', 'Build', 'One story with before/after.', 'Portfolio', [
                ['Pick the case', 'Problem with stakes.', 'Case outline: context, role, outcome.', 'task', 40, 'text'],
                ['Design the solution', 'Flows and UI.', 'Hi-fi for the core flow.', 'project', 180, 'link'],
                ['Write the story', 'Process over decoration.', 'Case study draft.', 'task', 90, 'file'],
                ['Mentor critique', 'Tighten the narrative.', 'Revise 3 pages.', 'reflection', 40, 'text'],
            ]],
            [5, 'Design interviews', 'Career', 'Portfolio + whiteboard + culture.', 'Interview Ready', [
                ['Portfolio polish', '3 cases max.', 'Homepage + one deep case live.', 'task', 90, 'link'],
                ['Whiteboard practice', 'Timeboxed flows.', '2 timed exercises logged.', 'task', 90, 'file'],
                ['Resume', 'Impact, not tools list.', 'One-page design resume.', 'task', 45, 'file'],
                ['Mock interview', 'Walk a case.', 'Notes from one mock.', 'reflection', 60, 'text'],
            ]],
            [6, 'Applications', 'Launch', 'Studios, product orgs, freelance.', 'Launch', [
                ['Target list', 'Product vs agency.', '12 teams and why.', 'task', 40, 'text'],
                ['Outreach', 'Warm > cold.', '8 applications or intros.', 'task', 90, 'text'],
                ['Take-home hygiene', 'Timebox and communicate.', 'Checklist for assignments.', 'reading', 30, 'text'],
                ['90-day plan', 'First design hire habits.', 'Onboarding questions list.', 'project', 45, 'text'],
            ]],
        ]);
    }

    private function marketingMonths(): array
    {
        return $this->sixMonths([
            [1, 'Positioning', 'Fundamentals', 'Who it is for and why they care.', 'Marketer', [
                ['Category & offer', 'What you are not.', 'Positioning canvas for one brand.', 'reading', 60, 'text'],
                ['Audience', 'Jobs and objections.', 'One persona + JTBD.', 'task', 60, 'text'],
                ['Message house', 'Headline to proof.', '5 value props with evidence.', 'task', 75, 'file'],
                ['Competitive scan', 'How others talk.', 'Table of 4 competitors.', 'project', 60, 'file'],
            ]],
            [2, 'Channels', 'Growth', 'Where demand actually comes from.', 'Channels', [
                ['Channel map', 'Paid, owned, earned.', 'Score 6 channels for the offer.', 'task', 60, 'file'],
                ['Content engine', 'One idea, many cuts.', '4-week content calendar.', 'project', 90, 'file'],
                ['Performance basics', 'CAC, CTR, CVR.', 'Define a tracking plan.', 'reading', 45, 'text'],
                ['Creative test', 'Copy and creative.', '3 ad/landing variants.', 'task', 75, 'file'],
            ]],
            [3, 'Experiments', 'Build', 'Learn faster than you spend.', 'Growth', [
                ['Hypothesis', 'If we X then Y because Z.', 'Write 5 test cards.', 'task', 40, 'text'],
                ['Run a test', 'Small and clean.', 'One live or paper experiment.', 'project', 120, 'file'],
                ['Read results', 'Guardrail metrics.', 'Decision: scale, iterate, kill.', 'task', 60, 'text'],
                ['Retro', 'What you would change.', '1-page learning log.', 'reflection', 30, 'text'],
            ]],
            [4, 'Growth case', 'Craft', 'A story with numbers.', 'Case', [
                ['Pick a product', 'Stalled metric.', 'Problem + north-star metric.', 'task', 40, 'text'],
                ['Growth model', 'Loops not hacks.', 'Sketch the loop.', 'project', 90, 'file'],
                ['90-day plan', 'Bets and owners.', 'Roadmap of 6 bets.', 'task', 75, 'file'],
                ['Narrative', 'Tell it in 6 slides.', 'Case deck.', 'reflection', 60, 'file'],
            ]],
            [5, 'Career', 'Career', 'Brand, performance, or growth.', 'Interview Ready', [
                ['Track choice', 'Fit.', 'Primary track + 10 firms.', 'task', 40, 'text'],
                ['Resume', 'Campaigns with numbers.', 'Rewrite 6 bullets.', 'task', 75, 'file'],
                ['Case drills', 'Diagnose a funnel.', '2 practice cases logged.', 'task', 90, 'text'],
                ['Mock', 'Portfolio walkthrough.', 'Notes from one mock.', 'reflection', 45, 'text'],
            ]],
            [6, 'Launch', 'Launch', 'Applications and first 90 days.', 'Launch', [
                ['Pipeline', 'Roles.', '12 tailored applications.', 'task', 90, 'text'],
                ['Work samples', 'One live artefact.', 'Folder of 3 pieces.', 'project', 60, 'link'],
                ['Offer filter', 'Learning vs brand.', 'Rubric.', 'reading', 25, 'text'],
                ['90-day plan', 'Audit, quick win, system.', 'Plan for a growth seat.', 'task', 45, 'text'],
            ]],
        ]);
    }

    private function dataMonths(): array
    {
        return $this->sixMonths([
            [1, 'Data foundations', 'Fundamentals', 'SQL, Python, and clean tables.', 'Analyst', [
                ['SQL daily', 'Joins and aggregations.', 'Finish 20 practice queries.', 'task', 120, 'link'],
                ['Python / notebooks', 'Pandas hygiene.', 'Clean one messy dataset.', 'task', 120, 'link'],
                ['Stats intuition', 'Distributions, bias.', 'Explain 5 pitfalls in your own words.', 'reading', 60, 'text'],
                ['Mini EDA', 'Ask a question.', 'Notebook with 4 charts + conclusion.', 'project', 90, 'link'],
            ]],
            [2, 'Analytics in product', 'Applied', 'Metrics that change decisions.', 'Decision', [
                ['Metric design', 'North star vs vanity.', 'Define a metric tree.', 'task', 60, 'file'],
                ['Dashboard', 'One audience.', '4-chart dashboard.', 'project', 120, 'link'],
                ['Experiment basics', 'A/B literacy.', 'Critique one experiment write-up.', 'reading', 45, 'text'],
                ['Insight memo', 'So what.', '1-page recommendation.', 'reflection', 40, 'file'],
            ]],
            [3, 'ML starter', 'ML', 'A model with a real question.', 'Modeller', [
                ['Problem framing', 'Prediction vs explanation.', 'Write the ML brief.', 'task', 40, 'text'],
                ['Baseline model', 'Simple first.', 'Train and report metrics.', 'project', 180, 'link'],
                ['Error analysis', 'Where it fails.', 'Slice metrics + next steps.', 'task', 75, 'text'],
                ['Ethics check', 'Leakage, bias, consent.', 'One-page risk note.', 'reflection', 40, 'text'],
            ]],
            [4, 'Flagship project', 'Build', 'End-to-end and documented.', 'Shipper', [
                ['Scope', 'Data + user of the insight.', 'Project one-pager.', 'task', 40, 'text'],
                ['Build', 'Reproducible.', 'Repo with README and notebook/app.', 'project', 240, 'link'],
                ['Communicate', 'Non-technical audience.', '5-slide story.', 'task', 60, 'file'],
                ['Review', 'Mentor critique.', 'Apply 3 changes.', 'reflection', 40, 'text'],
            ]],
            [5, 'Interviews', 'Career', 'SQL + case + ML depth as needed.', 'Interview Ready', [
                ['Resume', 'Projects with impact.', 'Rewrite bullets.', 'task', 75, 'file'],
                ['SQL sprint', 'Timed.', '15 interview-style queries.', 'task', 120, 'link'],
                ['Case', 'Business + data.', '2 cases logged.', 'task', 90, 'text'],
                ['Mock', 'Take-home or live.', 'Notes from one mock.', 'reflection', 60, 'text'],
            ]],
            [6, 'Land the role', 'Launch', 'Analyst, DS, or ML-lite.', 'Launch', [
                ['Targets', 'Team type.', '15 roles tagged.', 'task', 40, 'text'],
                ['Apply', 'Portfolio first.', '10 applications.', 'task', 90, 'text'],
                ['Take-homes', 'Timebox.', 'Checklist.', 'reading', 25, 'text'],
                ['90-day plan', 'Data stack onboarding.', 'Questions to ask week 1.', 'project', 40, 'text'],
            ]],
        ]);
    }

    private function lawMonths(): array
    {
        return $this->sixMonths([
            [1, 'Legal foundations', 'Fundamentals', 'How to read and research.', 'Foundations', [
                ['Map the paths', 'Litigation, corporate, policy, in-house.', '1-page path comparison.', 'reading', 50, 'text'],
                ['Research method', 'Statutes, cases, commentary.', 'Research note on one live issue.', 'task', 90, 'file'],
                ['Ethics', 'Conflict, confidentiality.', 'Summarise 6 duties with examples.', 'reading', 45, 'text'],
                ['Citation drill', 'Clean footnotes.', '10 correctly cited authorities.', 'task', 60, 'file'],
            ]],
            [2, 'Drafting', 'Practice', 'Clear writing is the craft.', 'Drafter', [
                ['Memo structure', 'Issue, rule, application.', '2-page office memo.', 'project', 120, 'file'],
                ['Contracts skim', 'Risk allocation.', 'Markup 8 clauses on a sample.', 'task', 90, 'file'],
                ['Client email', 'Plain language.', 'Rewrite a dense update.', 'task', 40, 'text'],
                ['Feedback loop', 'Mentor redlines.', 'Revise the memo.', 'reflection', 40, 'text'],
            ]],
            [3, 'Applied practice', 'Chambers', 'How work actually moves.', 'Practitioner', [
                ['Matter map', 'From brief to filing.', 'Process map of one matter type.', 'task', 60, 'file'],
                ['Oral skills', 'Structure an argument.', '5-minute submission outline.', 'task', 60, 'text'],
                ['Due diligence lite', 'Checklists.', 'DD list for a simple deal or case.', 'project', 90, 'file'],
                ['Court / deal diary', 'Observe.', 'Notes from 1 hearing or recorded argument.', 'reflection', 40, 'text'],
            ]],
            [4, 'Specialisation file', 'Depth', 'One area to be known for.', 'Specialist', [
                ['Pick a desk', 'IP, disputes, corp, crime, etc.', 'Why this desk + 5 readings.', 'task', 45, 'text'],
                ['Research brief', 'Live controversy.', '4-page brief.', 'project', 150, 'file'],
                ['Draft artefact', 'Petition, opinion, or clause set.', 'One artefact.', 'task', 120, 'file'],
                ['Critique', 'Mentor comments.', 'Redline round.', 'reflection', 40, 'text'],
            ]],
            [5, 'Recruitment', 'Career', 'Firms, judiciary, in-house, LLM.', 'Interview Ready', [
                ['CV & internships', 'Matters not marks only.', 'Rewrite CV.', 'task', 60, 'file'],
                ['Interview Qs', 'Law + commercial awareness.', '20 answers.', 'task', 90, 'text'],
                ['Writing test', 'Timed.', 'One timed memo.', 'task', 90, 'file'],
                ['Mock', 'Partner-style.', 'Notes from one mock.', 'reflection', 45, 'text'],
            ]],
            [6, 'Join-up', 'Launch', 'Applications and first seat.', 'Launch', [
                ['Applications', 'Chambers / firms / exams.', '8 submissions.', 'task', 90, 'text'],
                ['Portfolio', 'Memos and drafts.', 'Folder of 3 anonymised pieces.', 'project', 60, 'file'],
                ['Exam or LLM plan', 'If relevant.', '12-week outline or skip note.', 'task', 40, 'text'],
                ['90-day plan', 'How juniors add value.', 'Learning checklist.', 'reflection', 30, 'text'],
            ]],
        ]);
    }

    private function healthMonths(): array
    {
        return $this->sixMonths([
            [1, 'Clinical foundations', 'Fundamentals', 'Strengthen the base you will use daily.', 'Clinician', [
                ['Weak-topic map', 'From college/exams.', '10 must-know topics for the mentee’s stage.', 'reading', 60, 'text'],
                ['Case method', 'History, exam, differentials.', 'Write 4 structured cases.', 'task', 90, 'file'],
                ['Guidelines', 'Standard of care.', 'Summarise 3 current guidelines.', 'reading', 75, 'text'],
                ['Ethics & consent', 'Communication.', 'Script for a difficult conversation.', 'reflection', 40, 'text'],
            ]],
            [2, 'Exam or skills block', 'Practice', 'NEET PG, INI, or skills log as relevant.', 'Prepared', [
                ['Study system', 'Spaced + questions.', 'Weekly timetable.', 'task', 40, 'text'],
                ['Q bank sprint', 'Error log.', 'Timed set + 20 error notes.', 'task', 150, 'file'],
                ['OSCE / practical', 'If applicable.', 'Checklist for 5 stations.', 'task', 90, 'file'],
                ['Recovery', 'Burnout check.', 'What to drop this month.', 'reflection', 25, 'text'],
            ]],
            [3, 'Research & evidence', 'Evidence', 'Read and write like a clinician.', 'Scholar', [
                ['Paper club', 'Methods first.', 'Critique one paper.', 'reading', 75, 'text'],
                ['Question', 'PICO.', 'Write a research question.', 'task', 40, 'text'],
                ['Mini review', 'Narrow scope.', '2-page evidence table.', 'project', 120, 'file'],
                ['Stats sense', 'p-values and CI.', 'Interpret 5 results sections.', 'task', 60, 'text'],
            ]],
            [4, 'Specialty exploration', 'Depth', 'Choose with eyes open.', 'Specialty', [
                ['Role reality', 'Lifestyle and training.', 'Compare 3 specialties.', 'task', 60, 'text'],
                ['Shadow notes', 'Or recorded grand rounds.', 'One observation log.', 'reflection', 40, 'text'],
                ['Skills unique to the field', 'Procedures or clinics.', 'Learning list.', 'task', 45, 'text'],
                ['Decision memo', 'Tentative choice.', '1-page why.', 'project', 40, 'text'],
            ]],
            [5, 'Applications', 'Career', 'Residency, jobs, or overseas.', 'Interview Ready', [
                ['CV & SOP', 'Clinical narrative.', 'Draft SOP + CV.', 'task', 90, 'file'],
                ['Interview stories', 'Ethics and cases.', '10 answers.', 'task', 75, 'text'],
                ['Mock', 'Panel.', 'Notes from one mock.', 'reflection', 45, 'text'],
                ['Backup plan', 'Plan B year.', 'Written contingency.', 'task', 30, 'text'],
            ]],
            [6, 'First posting', 'Launch', 'Survive and learn in month 1.', 'Launch', [
                ['Onboarding list', 'Protocols, people, kit.', 'Week-1 checklist.', 'task', 40, 'text'],
                ['Safety', 'Escalation.', 'When to call for help — one pager.', 'reading', 30, 'text'],
                ['Logbook', 'Procedures / cases.', 'Template started.', 'project', 40, 'file'],
                ['90-day learning', 'Topics to cover.', 'Personal curriculum.', 'reflection', 30, 'text'],
            ]],
        ]);
    }

    private function policyMonths(): array
    {
        return $this->sixMonths([
            [1, 'State & society', 'Fundamentals', 'How policy is actually made.', 'Citizen', [
                ['Institutions map', 'Union, state, local.', '1-page map of actors for one issue.', 'reading', 60, 'text'],
                ['Read a budget or bill', 'Follow the money.', 'Summary of 5 allocations or clauses.', 'task', 90, 'file'],
                ['Current affairs system', 'Notes not hoarding.', 'Weekly brief template used once.', 'task', 45, 'text'],
                ['Ethics of public power', 'Discretion.', 'Case reflection.', 'reflection', 30, 'text'],
            ]],
            [2, 'Policy analysis', 'Analysis', 'Define the problem before the scheme.', 'Analyst', [
                ['Problem tree', 'Causes vs symptoms.', 'Tree for a live issue.', 'task', 60, 'file'],
                ['Options memo', '3 options, 1 rec.', '2-page memo.', 'project', 120, 'file'],
                ['Stakeholder map', 'Winners and losers.', 'Power/interest grid.', 'task', 45, 'file'],
                ['Data for policy', 'What is measurable.', 'Indicator list.', 'reading', 40, 'text'],
            ]],
            [3, 'Exam or fellowship track', 'Prep', 'UPSC, state PSC, or policy schools.', 'Prepared', [
                ['Path choice', 'Service vs research vs NGOs.', 'Decision note.', 'reflection', 40, 'text'],
                ['GS / optional plan', 'If exam.', '12-week outline or skip.', 'task', 45, 'file'],
                ['Answer writing', 'Structure.', '4 timed answers.', 'task', 120, 'file'],
                ['Essay / SOP', 'Voice.', 'One long-form piece.', 'project', 90, 'file'],
            ]],
            [4, 'Field file', 'Depth', 'One sector to know cold.', 'Specialist', [
                ['Pick a sector', 'Health, ed, climate, cities.', 'Reading list of 8.', 'task', 40, 'text'],
                ['Sector brief', 'Problems and instruments.', '6-page brief.', 'project', 150, 'file'],
                ['Compare two states/countries', 'What travelled.', '2-page comparison.', 'task', 75, 'file'],
                ['Mentor critique', 'Sharpen recs.', 'Revise.', 'reflection', 30, 'text'],
            ]],
            [5, 'Applications', 'Career', 'Services, think tanks, CSR, multilaterals.', 'Interview Ready', [
                ['CV', 'Projects and internships.', 'Rewrite.', 'task', 60, 'file'],
                ['Interview', 'Opinion with evidence.', '15 Q&A.', 'task', 90, 'text'],
                ['Writing test', 'Timed memo.', 'One test.', 'task', 90, 'file'],
                ['Mock', 'Panel.', 'Notes.', 'reflection', 45, 'text'],
            ]],
            [6, 'Public career launch', 'Launch', 'First role in the ecosystem.', 'Launch', [
                ['Target list', '10 organisations.', 'Why each.', 'task', 40, 'text'],
                ['Applications', '8 submissions.', 'Sent.', 'task', 90, 'text'],
                ['Network', 'Informational chats.', '5 outreaches.', 'task', 40, 'text'],
                ['90-day plan', 'Listen, map, one memo.', 'Plan.', 'project', 40, 'text'],
            ]],
        ]);
    }

    private function educationMonths(): array
    {
        return $this->sixMonths([
            [1, 'Learning science', 'Fundamentals', 'How people actually learn.', 'Educator', [
                ['Core ideas', 'Retrieval, spacing, feedback.', 'Notes on 5 principles.', 'reading', 60, 'text'],
                ['Observe a class', 'Or a recorded lesson.', 'Observation log.', 'task', 75, 'file'],
                ['Learner profile', 'One student type.', 'Persona + barriers.', 'task', 40, 'text'],
                ['Teaching stance', 'Why you teach.', 'Personal note.', 'reflection', 25, 'text'],
            ]],
            [2, 'Lesson design', 'Practice', 'Plan, teach, adjust.', 'Designer', [
                ['Objective & assessment', 'Begin with the end.', 'One lesson plan.', 'task', 60, 'file'],
                ['Teach a slice', 'Peer or recording.', '15-min session.', 'project', 90, 'link'],
                ['Feedback', 'From learners or mentor.', 'What to change.', 'reflection', 30, 'text'],
                ['Materials', 'Worksheet or slides.', 'One artefact.', 'task', 60, 'file'],
            ]],
            [3, 'Assessment & inclusion', 'Inclusion', 'Fair measures and access.', 'Inclusive', [
                ['Design a quiz', 'Valid not tricky.', '10 items + key.', 'task', 75, 'file'],
                ['Formative checks', 'During learning.', '3 in-class techniques.', 'reading', 40, 'text'],
                ['Inclusion pass', 'Language, disability, device.', 'Audit of one lesson.', 'task', 45, 'text'],
                ['Report to a parent / manager', 'Plain language.', 'One update.', 'reflection', 30, 'text'],
            ]],
            [4, 'Ed project', 'Build', 'Classroom, edtech, or research.', 'Builder', [
                ['Brief', 'Problem in learning.', 'One-pager.', 'task', 40, 'text'],
                ['Prototype', 'Module, club, or tool.', 'v1 delivered.', 'project', 150, 'file'],
                ['Evidence', 'Did it help?', 'Small evaluation.', 'task', 75, 'file'],
                ['Case write-up', 'Portfolio.', '2-page case.', 'reflection', 45, 'file'],
            ]],
            [5, 'Career tracks', 'Career', 'Schools, HE, edtech, research.', 'Interview Ready', [
                ['Path map', 'Fit.', 'Score 3 tracks.', 'task', 40, 'text'],
                ['CV & teaching statement', 'Evidence of impact.', 'Two documents.', 'task', 90, 'file'],
                ['Demo lesson', 'If hiring asks.', 'Plan + slides.', 'project', 90, 'file'],
                ['Mock', 'Panel or demo.', 'Notes.', 'reflection', 40, 'text'],
            ]],
            [6, 'First term', 'Launch', 'Survive week 1 and set a system.', 'Launch', [
                ['Applications', '8 roles or programmes.', 'Sent.', 'task', 90, 'text'],
                ['Classroom systems', 'Routines.', 'Week-1 playbook.', 'task', 45, 'file'],
                ['90-day learning', 'Curriculum + classroom mgmt.', 'Plan.', 'project', 40, 'text'],
                ['Support net', 'Mentors and peers.', 'Who you will ask.', 'reflection', 20, 'text'],
            ]],
        ]);
    }

    private function startupMonths(): array
    {
        return $this->sixMonths([
            [1, 'Founder thesis', 'Foundations', 'A problem worth a company.', 'Founder', [
                ['Why this', 'Motivation vs market.', '1-page founder thesis.', 'reflection', 40, 'text'],
                ['Problem interviews', 'Before the pitch.', 'Notes from 5 conversations.', 'task', 90, 'file'],
                ['Market sketch', 'Who pays.', 'TAM/SAM note without fake precision.', 'task', 60, 'text'],
                ['Risks', 'Why it dies.', 'Top 5 risks.', 'reading', 30, 'text'],
            ]],
            [2, 'MVP', 'Build', 'Ugly but real.', 'Builder', [
                ['Scope v1', 'One job.', 'MVP brief.', 'task', 40, 'text'],
                ['Ship', 'No-code or code.', 'Working demo.', 'project', 240, 'link'],
                ['Measure', 'One activation metric.', 'Tracking defined.', 'task', 40, 'text'],
                ['Learn', 'What users did.', 'Learning log.', 'reflection', 30, 'text'],
            ]],
            [3, 'Customers', 'Demand', 'Someone pays or commits.', 'Sales', [
                ['Offer', 'Price and promise.', 'One-pager offer.', 'task', 45, 'file'],
                ['Pipeline', '10 conversations.', 'CRM-style log.', 'task', 90, 'file'],
                ['First revenue or LOI', 'Even tiny.', 'Evidence of demand.', 'project', 60, 'file'],
                ['Objection map', 'What they said no to.', 'Playbook v1.', 'reflection', 30, 'text'],
            ]],
            [4, 'Team & ops', 'Company', 'How work will happen.', 'Operator', [
                ['Roles', 'What you will not do.', 'Founder split.', 'task', 40, 'text'],
                ['Legal basics', 'Entity, IP, equity (overview).', 'Question list for a CA/lawyer.', 'reading', 45, 'text'],
                ['Runway', 'Simple model.', '6-month cash sketch.', 'project', 75, 'file'],
                ['Cadence', 'Weekly operating rhythm.', 'One-page OS.', 'task', 30, 'text'],
            ]],
            [5, 'Story & raise (optional)', 'Narrative', 'Pitch only if it helps.', 'Pitch', [
                ['Narrative', 'Problem, why now, why you.', 'Deck outline.', 'task', 60, 'file'],
                ['Deck or update', '10 slides or a memo.', 'v1 artefact.', 'project', 120, 'file'],
                ['Practice', 'Timeboxed.', 'Recorded 5-min pitch.', 'task', 40, 'link'],
                ['Decision', 'Raise, bootstrap, or pause.', 'Written choice.', 'reflection', 30, 'text'],
            ]],
            [6, 'Next 90 days', 'Launch', 'Focus over theatre.', 'Launch', [
                ['OKRs', 'One product, one channel.', '90-day OKRs.', 'task', 40, 'text'],
                ['Hiring or help', 'First support.', 'Role scorecard or no-hire note.', 'task', 30, 'text'],
                ['Mentor board', 'Who you will use.', 'Ask list.', 'reflection', 20, 'text'],
                ['Public artefact', 'Update or landing page.', 'Shipped.', 'project', 60, 'link'],
            ]],
        ]);
    }

    private function cseMonths(): array
    {
        return $this->sixMonths([
            [1, 'Programming foundations', 'Fundamentals', 'Write clean code and solve beginner DSA.', 'Code Starter', [
                ['Language refresh', 'Revise syntax, Git, and a daily coding habit.', 'Set up GitHub and complete 5 warm-up problems.', 'reading', 60, 'none'],
                ['Problem-solving patterns', 'Arrays, strings, hashing.', 'Explain 3 patterns the mentee should master this week.', 'task', 90, 'text'],
                ['Complexity basics', 'Big-O in interviews.', 'Solve 12 easy-medium problems and log approaches.', 'task', 120, 'link'],
                ['Mini coding kata', 'Apply the week in a tiny CLI or script.', 'Submit a GitHub repo with README.', 'project', 150, 'link'],
            ]],
            [2, 'Data structures & web basics', 'Core skills', 'Build DSA confidence and a first web project.', 'Builder', [
                ['Trees & graphs intro', 'Core interview structures.', 'Implement 4 structures with 2 problems each.', 'task', 120, 'link'],
                ['SQL & APIs', 'How backend data actually moves.', 'Design 8 tables and 6 API endpoints on paper.', 'reading', 90, 'text'],
                ['Frontend or backend slice', 'Pick the mentee’s track.', 'Ship one CRUD feature with auth.', 'project', 180, 'link'],
                ['Code review week', 'Quality over speed.', 'Review the project against a checklist and refactor.', 'reflection', 60, 'text'],
            ]],
            [3, 'System design & specialisation', 'Depth', 'Learn to talk about scale and pick a stack.', 'Designer', [
                ['System design 101', 'Clients, DBs, caches, queues.', 'Sketch 2 designs: URL shortener and news feed.', 'reading', 90, 'text'],
                ['Stack deep-dive', 'One language/framework to mastery.', 'Complete a guided module in the chosen stack.', 'task', 120, 'link'],
                ['Design a feature', 'From PRD to components.', 'Write a 1-page design for a mentee-chosen product.', 'project', 150, 'file'],
                ['Mock design interview', 'Talk through trade-offs.', 'Record or notes from a 30-min design conversation.', 'reflection', 45, 'text'],
            ]],
            [4, 'Flagship project', 'Build', 'Ship something that can go on a resume.', 'Shipper', [
                ['Scope the project', 'One problem, 4-week plan.', 'Write problem statement, users, and milestones.', 'task', 60, 'text'],
                ['Build v1', 'Working vertical slice.', 'Demo a usable v1 with seed data.', 'project', 240, 'link'],
                ['Harden & document', 'Tests, README, deploy.', 'Deploy and add setup + architecture notes.', 'task', 150, 'link'],
                ['Peer / mentor review', 'Feedback loop.', 'List 5 changes from review and apply 3.', 'reflection', 60, 'text'],
            ]],
            [5, 'Interview readiness', 'Career', 'DSA + stories + resume.', 'Interview Ready', [
                ['Resume & LinkedIn', 'One sharp profile.', 'Rewrite resume bullets with impact numbers.', 'task', 90, 'file'],
                ['DSA sprint', 'Timed practice.', 'Complete 20 mixed problems this week.', 'task', 180, 'link'],
                ['Behavioural stories', 'STAR for projects.', 'Write 6 STAR stories from this curriculum.', 'reflection', 60, 'text'],
                ['Mock interview', 'Full loop dry-run.', 'Do one coding + one behavioural mock; note gaps.', 'task', 90, 'text'],
            ]],
            [6, 'Offers & first 90 days', 'Launch', 'Applications, negotiation, joining plan.', 'Career Launch', [
                ['Target list', 'Companies and roles.', 'Shortlist 15 roles that fit the stack.', 'task', 60, 'text'],
                ['Application sprint', 'Volume with quality.', 'Apply to 10 roles with tailored notes.', 'task', 120, 'text'],
                ['Offer / negotiation basics', 'Comp, joining date, questions.', 'Prepare a negotiation script.', 'reading', 45, 'text'],
                ['90-day plan', 'Start strong.', 'Draft a 30-60-90 plan for the first role.', 'project', 90, 'file'],
            ]],
        ]);
    }

    private function engineeringMonths(): array
    {
        return $this->sixMonths([
            [1, 'Engineering foundations', 'Fundamentals', 'Refresh core theory the mentee will use at work.', 'Foundation', [
                ['Core theory recap', 'Map weak topics from college.', 'List 10 must-know concepts for this branch.', 'reading', 90, 'text'],
                ['Tools of the trade', 'CAD / MATLAB / circuits / site tools as relevant.', 'Install or refresh the primary tool and complete a tutorial.', 'task', 120, 'link'],
                ['Standards & safety', 'Industry context.', 'Summarise 5 standards or codes used in this field.', 'reading', 60, 'text'],
                ['Mini calculation set', 'Apply theory.', 'Solve 8 applied problems with working shown.', 'task', 120, 'file'],
            ]],
            [2, 'Applied skills', 'Practice', 'Move from classroom to shop-floor thinking.', 'Practitioner', [
                ['Process walkthrough', 'How work actually happens.', 'Document one real process end-to-end.', 'reading', 75, 'text'],
                ['Software / lab practice', 'Hands-on hours.', 'Complete a guided lab or CAD exercise.', 'task', 150, 'file'],
                ['Quality & documentation', 'Drawings, reports, BOMs.', 'Write a 1-page technical note.', 'task', 90, 'file'],
                ['Site or case study', 'Reality check.', 'Analyse a failure or site photo set.', 'reflection', 60, 'text'],
            ]],
            [3, 'Domain project', 'Build', 'A small project in the mentee’s branch.', 'Project Lead', [
                ['Problem framing', 'Scope that fits 4 weeks.', 'Write objective, constraints, and success metric.', 'task', 60, 'text'],
                ['Design / analysis', 'Core engineering work.', 'Submit calculations, drawings, or simulation.', 'project', 180, 'file'],
                ['Review against spec', 'Did it meet the brief?', 'Create a punch-list and close 3 items.', 'task', 90, 'text'],
                ['Present the work', 'Talk like an engineer.', '5-slide walkthrough of decisions.', 'reflection', 45, 'file'],
            ]],
            [4, 'Industry exposure', 'Context', 'Understand roles, plants, and products.', 'Industry Aware', [
                ['Role map', 'Design, production, QA, projects, sales-eng.', 'Interview or research 2 roles; compare skills.', 'reading', 75, 'text'],
                ['Company deep-dive', 'Target employers.', 'Profile 5 companies and their hiring bar.', 'task', 90, 'text'],
                ['Domain current affairs', 'Codes, products, news.', 'Share 3 insights from recent industry news.', 'task', 45, 'text'],
                ['Mentor AMA notes', 'Capture tacit knowledge.', 'Write 10 Q&A from a session.', 'reflection', 40, 'text'],
            ]],
            [5, 'Placement prep', 'Career', 'GATE / core jobs / internships as relevant.', 'Placement Ready', [
                ['Resume for core roles', 'Projects over CGPA padding.', 'Rewrite resume for core engineering.', 'task', 90, 'file'],
                ['Technical Q bank', 'Oral + written.', 'Prepare 30 likely technical questions.', 'task', 120, 'text'],
                ['Aptitude / GATE slice', 'As needed.', 'Timed practice set and error log.', 'task', 120, 'file'],
                ['Mock technical', 'Talk through a drawing or problem.', 'Notes from one mock with mentor.', 'reflection', 60, 'text'],
            ]],
            [6, 'Join-up plan', 'Launch', 'First job or higher studies path.', 'Launch', [
                ['Path choice', 'Job, MS, PSU, startup.', 'Write a 1-page decision with reasons.', 'reflection', 45, 'text'],
                ['Application batch', 'Targeted outreach.', 'Apply / mail 8 opportunities.', 'task', 90, 'text'],
                ['Portfolio pack', 'Project + CAD + report.', 'Zip a clean portfolio folder.', 'project', 90, 'file'],
                ['First 90 days', 'If joining.', 'Onboarding checklist for the chosen path.', 'task', 60, 'text'],
            ]],
        ]);
    }

    private function financeMonths(): array
    {
        return $this->sixMonths([
            [1, 'Accounting foundations', 'Fundamentals', 'Statements that actually make sense.', 'Accountant', [
                ['Three statements', 'P&L, BS, CF links.', 'Explain how a sale flows through all three.', 'reading', 75, 'text'],
                ['Journal to trial balance', 'Mechanics.', 'Complete a 20-entry practice set.', 'task', 120, 'file'],
                ['Ratio starter pack', 'Liquidity, leverage, profitability.', 'Compute ratios for one listed company.', 'task', 90, 'file'],
                ['Week recap', 'Teach-back.', 'Record a 5-min explanation of working capital.', 'reflection', 30, 'link'],
            ]],
            [2, 'Financial analysis', 'Analysis', 'Read a company like an analyst.', 'Analyst', [
                ['Annual report tour', 'MD&A and notes.', 'Summarise 5 red flags or strengths.', 'reading', 90, 'text'],
                ['Excel hygiene', 'Clean models.', 'Build a 3-statement stub in Excel.', 'task', 150, 'file'],
                ['Peer comparison', 'Relative view.', 'Compare 2 competitors on 8 metrics.', 'project', 120, 'file'],
                ['Insight memo', 'So what?', '1-page investment or credit view.', 'reflection', 45, 'file'],
            ]],
            [3, 'Markets & instruments', 'Markets', 'How money is priced.', 'Market Smart', [
                ['Time value of money', 'NPV, IRR, compounding.', 'Solve 10 TVM problems.', 'task', 90, 'file'],
                ['Equity vs debt', 'Risk and return.', 'Explain 4 instruments to a beginner.', 'reading', 60, 'text'],
                ['Market diary', 'Watch one index / stock.', 'Log 5 days of moves with a hypothesis.', 'task', 75, 'text'],
                ['Case: raise capital', 'Founder vs CFO view.', 'Recommend a funding mix for a case firm.', 'project', 90, 'text'],
            ]],
            [4, 'Corporate finance project', 'Build', 'A decision memo with numbers.', 'Modeller', [
                ['Scope a decision', 'Capex, pricing, or working capital.', 'Write the decision question and data needed.', 'task', 45, 'text'],
                ['Simple model', 'Drivers, not decoration.', 'Build a 3-year model with scenarios.', 'project', 180, 'file'],
                ['Sensitivity', 'What actually moves the needle.', 'Tornado or two-way table + commentary.', 'task', 75, 'file'],
                ['Recommendation', 'Decide and defend.', '2-page memo to a mock CFO.', 'reflection', 60, 'file'],
            ]],
            [5, 'Career tracks', 'Career', 'IB, FP&A, audit, markets, fintech.', 'Career Ready', [
                ['Track map', 'Fit vs interest.', 'Score 4 tracks and pick a primary.', 'reflection', 40, 'text'],
                ['Resume for finance', 'Deals, numbers, tools.', 'Rewrite 6 bullets with metrics.', 'task', 75, 'file'],
                ['Technical Qs', 'Accounting + markets.', 'Prepare 25 interview answers.', 'task', 120, 'text'],
                ['Mock case', 'Short case interview.', 'Notes from one case with mentor.', 'task', 60, 'text'],
            ]],
            [6, 'Applications & certifications', 'Launch', 'CFA/CA/jobs as relevant.', 'Launch', [
                ['Cert plan', 'If needed.', '12-week study outline or skip-rationale.', 'task', 45, 'text'],
                ['Target list', 'Firms and roles.', '15 roles with why-this-firm notes.', 'task', 60, 'text'],
                ['Outreach', 'Referrals + apps.', '8 applications or informational chats.', 'task', 90, 'text'],
                ['90-day plan', 'First seat.', 'Learning goals for FP&A / audit / markets.', 'project', 60, 'text'],
            ]],
        ]);
    }

    private function psychologyMonths(): array
    {
        return $this->sixMonths([
            [1, 'Foundations of psychology', 'Fundamentals', 'Core schools and ethical practice.', 'Foundations', [
                ['Map the field', 'Clinical, org, counselling, research.', '1-page map of paths and typical work.', 'reading', 60, 'text'],
                ['Ethics first', 'Consent, confidentiality, competence.', 'Summarise 8 ethical principles with examples.', 'reading', 75, 'text'],
                ['Concept deck', 'Must-know theories.', 'Flashcards for 15 core concepts.', 'task', 90, 'file'],
                ['Reflective journal', 'Why this field.', 'Write a personal purpose note.', 'reflection', 30, 'text'],
            ]],
            [2, 'Research methods', 'Methods', 'Read and run simple studies.', 'Researcher', [
                ['Study design', 'Qual vs quant.', 'Critique one paper’s method.', 'reading', 90, 'text'],
                ['Stats literacy', 'What numbers mean.', 'Interpret 5 graphs / tables from papers.', 'task', 75, 'text'],
                ['Mini survey', 'Practice.', 'Design a 10-item survey and collect 8 responses.', 'project', 120, 'file'],
                ['Write-up', 'IMRAD sketch.', '2-page results + limits.', 'task', 90, 'file'],
            ]],
            [3, 'Applied skills', 'Practice', 'Listening and formulation.', 'Practitioner', [
                ['Helping conversations', 'Listening skills.', 'Practice 2 recorded role-plays (peer).', 'task', 90, 'link'],
                ['Case formulation', 'Not diagnosis theatre.', 'Formulate 1 anonymised case from a vignette.', 'task', 75, 'text'],
                ['Org or education track', 'If not clinical.', 'Design a 45-min workshop outline.', 'project', 90, 'file'],
                ['Supervision notes', 'Learning loop.', 'What went well / what to change.', 'reflection', 30, 'text'],
            ]],
            [4, 'Specialisation project', 'Depth', 'One niche the mentee may work in.', 'Specialist', [
                ['Pick a niche', 'Child, workplace, health, etc.', 'Why this niche + 5 readings.', 'task', 60, 'text'],
                ['Literature brief', 'What is known.', '4-page annotated brief.', 'reading', 150, 'file'],
                ['Intervention sketch', 'Ethical, scoped.', 'Session plan or program outline.', 'project', 120, 'file'],
                ['Feedback round', 'Mentor critique.', 'Revise from comments.', 'reflection', 40, 'text'],
            ]],
            [5, 'Career & credentials', 'Career', 'Masters, RCI, HR, research.', 'Career Ready', [
                ['Path options', 'India-specific reality.', 'Compare 3 realistic next steps.', 'reading', 60, 'text'],
                ['CV for psychology', 'Clinicals / projects / research.', 'Rewrite CV and a 200-word SOP draft.', 'task', 90, 'file'],
                ['Interview stories', 'Ethics + cases.', 'Prepare 8 answers.', 'task', 60, 'text'],
                ['Mock interview', 'Panel style.', 'Notes and improvements.', 'reflection', 45, 'text'],
            ]],
            [6, 'Portfolio & next step', 'Launch', 'Applications or supervised practice plan.', 'Launch', [
                ['Portfolio pack', 'Projects + ethics statement.', 'Folder with 3 artefacts.', 'project', 90, 'file'],
                ['Applications', 'Courses or roles.', 'Submit 5 applications.', 'task', 90, 'text'],
                ['Supervision plan', 'If practising-adjacent.', 'Who, how often, on what.', 'task', 40, 'text'],
                ['90-day learning', 'Stay current.', 'Reading and skill list.', 'reflection', 30, 'text'],
            ]],
        ]);
    }

    private function managementMonths(): array
    {
        return $this->sixMonths([
            [1, 'Business fundamentals', 'Fundamentals', 'How a company actually makes money.', 'Business Basics', [
                ['Value chain', 'From customer to P&L.', 'Map a company the mentee knows.', 'reading', 60, 'text'],
                ['Numbers for managers', 'Margins, CAC, unit economics.', 'Compute 8 basic metrics from a case.', 'task', 90, 'file'],
                ['Functions tour', 'Sales, ops, finance, people, product.', 'Interview or research one function lead role.', 'task', 75, 'text'],
                ['Problem framing', 'Define before solving.', 'Write a problem statement using SCQA.', 'reflection', 40, 'text'],
            ]],
            [2, 'Marketing & customers', 'Customers', 'Who buys and why.', 'Marketer', [
                ['Segmentation', 'Who is the user.', 'One-page persona + JTBD.', 'task', 75, 'text'],
                ['Channel basics', 'Where demand comes from.', 'Compare 3 channels with costs.', 'reading', 60, 'text'],
                ['Mini campaign', 'Message and offer.', 'Draft a landing-page outline + 5 ads.', 'project', 120, 'file'],
                ['Review metrics', 'What you would measure.', 'Dashboard sketch of 6 KPIs.', 'reflection', 40, 'text'],
            ]],
            [3, 'Operations & analytics', 'Systems', 'Process and data.', 'Operator', [
                ['Process map', 'Bottlenecks.', 'As-is map of one process with wait times.', 'task', 90, 'file'],
                ['Spreadsheet / BI', 'Analyst hygiene.', 'Clean a dataset and 4 charts.', 'task', 120, 'file'],
                ['Ops case', 'Capacity or quality.', 'Recommend 3 changes with impact.', 'project', 90, 'text'],
                ['Automation ideas', 'Where tools help.', 'List 5 automations ranked by effort/impact.', 'reflection', 30, 'text'],
            ]],
            [4, 'Leadership & people', 'People', 'Influence without theatre.', 'Lead', [
                ['Feedback practice', 'SBI / radical candor lite.', 'Write 3 feedback scripts.', 'task', 45, 'text'],
                ['Stakeholder map', 'Who matters.', 'Power/interest grid for a project.', 'task', 40, 'file'],
                ['Team ritual design', 'Meetings that work.', 'Design a weekly operating cadence.', 'project', 60, 'text'],
                ['Self review', 'Strengths under stress.', '360-style self assessment.', 'reflection', 40, 'text'],
            ]],
            [5, 'Strategy capstone', 'Strategy', 'A full case for the resume.', 'Strategist', [
                ['Choose a company', 'Public or known SME.', 'Issue tree of the core problem.', 'task', 60, 'text'],
                ['Analysis pack', 'Market, competitors, internals.', '8-slide fact pack.', 'project', 180, 'file'],
                ['Recommendation', 'So what, now what.', '3 options, 1 recommendation, risks.', 'task', 90, 'file'],
                ['Storyline', 'Present it.', 'Rehearse 8-minute narrative.', 'reflection', 45, 'text'],
            ]],
            [6, 'Placement & first role', 'Launch', 'Consulting, product, ops, general management.', 'Launch', [
                ['Track choice', 'Fit.', 'Pick a primary track and 10 target firms.', 'task', 45, 'text'],
                ['Resume & stories', 'Impact bullets.', 'Rewrite resume + 6 PEI/STAR stories.', 'task', 90, 'file'],
                ['Case / guesstimate', 'As needed.', '5 cases logged with structures.', 'task', 150, 'text'],
                ['90-day plan', 'First manager role.', 'Learning, relationships, quick wins.', 'project', 60, 'text'],
            ]],
        ]);
    }

    private function techSwitchMonths(): array
    {
        return $this->sixMonths([
            [1, 'Mindset & foundations', 'Foundations', 'A realistic switch plan, not a bootcamp fantasy.', 'Switcher', [
                ['Why tech', 'Motivation vs market.', 'Write a 1-page switch thesis.', 'reflection', 40, 'text'],
                ['Skill gap map', 'From / to.', 'Current skills vs target role skills.', 'task', 60, 'text'],
                ['Computer basics', 'Git, CLI, how the web works.', 'Complete a foundations checklist.', 'reading', 90, 'text'],
                ['Weekly cadence', 'Sustainable hours.', 'Calendar 8–12 focused hours/week.', 'task', 30, 'text'],
            ]],
            [2, 'Core skill stack', 'Skills', 'One role, one stack.', 'Builder', [
                ['Pick the role', 'SDE, QA, data, product, support-eng.', 'Role scorecard with reasons.', 'task', 45, 'text'],
                ['Guided module 1', 'Syntax + small programs.', 'Finish module and push code.', 'task', 150, 'link'],
                ['Guided module 2', 'APIs or SQL or test tools.', 'Ship a tiny demo.', 'project', 150, 'link'],
                ['Learning log', 'What stuck.', 'Write 5 concepts in your own words.', 'reflection', 30, 'text'],
            ]],
            [3, 'Portfolio project', 'Proof', 'Evidence you can do the job.', 'Portfolio', [
                ['Problem that matters', 'Domain from past career if possible.', 'One-pager: user, pain, scope.', 'task', 45, 'text'],
                ['Build v1', 'Ugly but real.', 'Working demo with README.', 'project', 240, 'link'],
                ['Story the project', 'Past domain × new skill.', 'Write the LinkedIn/case narrative.', 'task', 60, 'text'],
                ['Review & polish', 'Deploy + screenshots.', 'Public URL and 3 screenshots.', 'task', 90, 'link'],
            ]],
            [4, 'Translate past experience', 'Bridge', 'You are not starting from zero.', 'Bridge', [
                ['Skill translation', 'Ops → systems, sales → discovery, teaching → comms.', 'Map 8 transferable skills to the new role.', 'task', 60, 'text'],
                ['Network with intent', 'Informational chats.', '5 outreach messages sent.', 'task', 45, 'text'],
                ['Positioning', 'Headline and about.', 'Rewrite LinkedIn headline + about.', 'task', 60, 'text'],
                ['Mentor critique', 'Does the story land?', 'Revise from feedback.', 'reflection', 30, 'text'],
            ]],
            [5, 'Interview the switch', 'Career', 'They will ask “why now?”.', 'Interview Ready', [
                ['Why-switch story', '2 minutes.', 'Script and record it.', 'reflection', 40, 'link'],
                ['Role technicals', 'At junior bar.', '20 Q&A for the chosen role.', 'task', 120, 'text'],
                ['Resume for switchers', 'Projects first.', 'One-page resume, no degree padding.', 'task', 75, 'file'],
                ['Mock loop', 'Story + technical.', 'One full mock with notes.', 'task', 90, 'text'],
            ]],
            [6, 'Land and ramp', 'Launch', 'Applications and first 90 days in tech.', 'Launch', [
                ['Target list', 'Switch-friendly teams.', '20 roles, tagged easy/reach.', 'task', 60, 'text'],
                ['Application sprint', 'Volume.', '12 tailored applications.', 'task', 120, 'text'],
                ['Offer filter', 'Learning vs brand vs pay.', 'Decision rubric.', 'reading', 30, 'text'],
                ['Ramp plan', 'Survive month 1.', 'Questions to ask, docs to read, people to meet.', 'project', 60, 'text'],
            ]],
        ]);
    }

    /**
     * @param  list<array{0:int,1:string,2:string,3:string,4:string,5:list<array{0:string,1:string,2:string,3:string,4:int,5:string}>}>  $months
     */
    private function sixMonths(array $months): array
    {
        $out = [];
        foreach ($months as $row) {
            [$number, $title, $theme, $description, $badge, $weeks] = $row;
            $weekMap = [];
            foreach ($weeks as $i => $week) {
                $weekMap[$i + 1] = [
                    'title' => $week[0],
                    'focus' => $week[1],
                    'guide' => 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.',
                    'tasks' => [
                        [$week[2], $week[1], $week[3], $week[4], $week[5]],
                        ['Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 20, 'text'],
                    ],
                ];
            }
            $out[$number] = [
                'title'       => $title,
                'theme'       => $theme,
                'description' => $description,
                'badge'       => $badge,
                'outcomes'    => [
                    $description,
                    'Leave with artefacts the mentee can show (notes, repo, memo, or slides).',
                ],
                'weeks'       => $weekMap,
            ];
        }

        return $out;
    }
};
