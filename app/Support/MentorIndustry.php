<?php

namespace App\Support;

class MentorIndustry
{
    /**
     * Canonical industries shown as “Primary Domain / Field” on mentor onboarding
     * and profile. Curriculum templates are keyed to these values.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Engineering & Technology',
            'Product Management',
            'Design & UX / UI',
            'Finance & Investment Banking',
            'MBA & Management Consulting',
            'Marketing & Growth',
            'Data Science & AI / ML',
            'Law & Legal',
            'Medicine & Healthcare',
            'Arts & Humanities',
            'Government & Public Policy',
            'Education & Academia',
            'Entrepreneurship & Startups',
        ];
    }

    /**
     * Older admin / listing labels mapped onto the onboarding list.
     *
     * @return array<string, string>
     */
    public static function aliases(): array
    {
        return [
            'software engineering' => 'Engineering & Technology',
            'engineering' => 'Engineering & Technology',
            'computer science engineering (cse)' => 'Engineering & Technology',
            'data science' => 'Data Science & AI / ML',
            'design (ui/ux)' => 'Design & UX / UI',
            'design & ux / ui' => 'Design & UX / UI',
            'ui/ux' => 'Design & UX / UI',
            'marketing' => 'Marketing & Growth',
            'sales' => 'Marketing & Growth',
            'finance' => 'Finance & Investment Banking',
            'accounting' => 'Finance & Investment Banking',
            'legal' => 'Law & Legal',
            'law' => 'Law & Legal',
            'entrepreneurship' => 'Entrepreneurship & Startups',
            'operations' => 'MBA & Management Consulting',
            'hr' => 'MBA & Management Consulting',
            'research' => 'Education & Academia',
            'product management' => 'Product Management',
            'psychology' => 'Arts & Humanities',
            'business analytics' => 'MBA & Management Consulting',
            'tech transition' => 'Engineering & Technology',
            'technology' => 'Engineering & Technology',
            'consulting' => 'MBA & Management Consulting',
            'healthcare' => 'Medicine & Healthcare',
            'startup' => 'Entrepreneurship & Startups',
            'government' => 'Government & Public Policy',
            'education' => 'Education & Academia',
        ];
    }

    public static function canonicalize(?string $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $norm = strtolower($raw);
        foreach (self::all() as $name) {
            if (strtolower($name) === $norm) {
                return $name;
            }
        }

        return self::aliases()[$norm] ?? $raw;
    }

    public static function matches(?string $templateIndustry, ?string $mentorField): bool
    {
        $template = self::canonicalize($templateIndustry);
        $mentor = self::canonicalize($mentorField);

        return $template !== null && $mentor !== null && strtolower($template) === strtolower($mentor);
    }
}
