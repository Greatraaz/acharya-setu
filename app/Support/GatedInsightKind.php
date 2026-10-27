<?php

namespace App\Support;

class GatedInsightKind
{
    public const CAREER_GUIDE = 'career_guide';
    public const INDUSTRY_REPORT = 'industry_report';

    /**
     * @return array<string, mixed>
     */
    public static function meta(string $type): array
    {
        return match ($type) {
            self::CAREER_GUIDE => [
                'type' => self::CAREER_GUIDE,
                'label' => 'Career Guides',
                'singular' => 'Career Guide',
                'icon' => '🧭',
                'badge' => 'Guide',
                'placeholder_icon' => 'bi-compass',
                'empty_icon' => 'bi-compass',
                'team_label' => 'Vedrix Career Team',
                'admin' => 'admin.career-guides',
                'public_index' => 'insights.career-guides',
                'public_show' => 'insights.career-guides.show',
                'gated' => false,
                'storage' => 'career-guides',
                'table_id' => 'career-guides-table',
                'leads_table_id' => 'career-guide-downloads-table',
                'page_title' => 'Career Guides — Insights',
                'meta_description' => 'Actionable career roadmaps and transition playbooks from Vedrix.',
                'eyebrow' => 'Career Navigation & Playbooks',
                'eyebrow_icon' => 'bi-compass-fill',
                'banner_title' => 'Actionable Career Playbooks & Transition Roadmaps',
                'banner_sub' => 'Step-by-step guidance designed by senior industry leaders to help you master role transitions, ace technical evaluations, and accelerate your career trajectory.',
                'section_eyebrow' => 'CAREER PLAYBOOKS',
                'section_title' => 'Guides You Can Use in Your Next Role',
                'section_sub' => 'Open a guide to read the full playbook on role transitions, interviews, and growth.',
                'empty_title' => 'Career playbooks coming soon',
                'empty_body' => 'Step-by-step roadmaps for transitions, interviews, and growth will appear here.',
                'download_label' => 'Download Guide',
                'form_title' => 'Download this guide',
                'more_title' => 'More Guides',
                'pdf_eyebrow' => 'Career Guides',
                'cta_title' => 'Need a mentor?',
                'cta_body' => 'Put the guide into practice with a verified Vedrix mentor.',
            ],
            self::INDUSTRY_REPORT => [
                'type' => self::INDUSTRY_REPORT,
                'label' => 'Industry Reports',
                'singular' => 'Industry Report',
                'icon' => '📊',
                'badge' => 'Report',
                'placeholder_icon' => 'bi-bar-chart-line',
                'empty_icon' => 'bi-bar-chart-line',
                'team_label' => 'Vedrix Research Team',
                'admin' => 'admin.industry-reports',
                'public_index' => 'insights.industry-reports',
                'public_show' => 'insights.industry-reports.show',
                'public_download' => 'insights.industry-reports.download',
                'gated' => true,
                'storage' => 'industry-reports',
                'table_id' => 'industry-reports-table',
                'leads_table_id' => 'industry-report-downloads-table',
                'page_title' => 'Industry Reports — Insights',
                'meta_description' => 'Data-backed hiring trends, salary benchmarks, and market reports from Vedrix. Fill a short form to download each report.',
                'eyebrow' => 'Market Intelligence',
                'eyebrow_icon' => 'bi-bar-chart-line-fill',
                'banner_title' => 'Data-Backed Industry Reports & Trends',
                'banner_sub' => 'In-depth research on tech hiring demands, salary benchmarks, emerging skill gaps, and market dynamics across global engineering and product ecosystems.',
                'section_eyebrow' => 'RESEARCH & REPORTS',
                'section_title' => 'Reports You Can Download and Share',
                'section_sub' => 'Open a report, then download the PDF after a short form. Your details help us send better resources.',
                'empty_title' => 'No reports currently published',
                'empty_body' => 'Check back soon — quarterly hiring and salary benchmarks will appear here.',
                'download_label' => 'Download Report',
                'form_title' => 'Download this report',
                'more_title' => 'More Reports',
                'pdf_eyebrow' => 'Industry Reports',
                'cta_title' => 'Need a mentor?',
                'cta_body' => 'Use these insights with a verified Vedrix mentor.',
            ],
            default => throw new \InvalidArgumentException('Unknown gated insight type.'),
        };
    }

    public static function fromRouteName(?string $name): string
    {
        $name = (string) $name;
        if (str_contains($name, 'industry-reports')) {
            return self::INDUSTRY_REPORT;
        }

        return self::CAREER_GUIDE;
    }
}
