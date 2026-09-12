<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $paper->title }}</title>
    <style>
        @page { margin: 36px 40px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.65;
        }
        .header {
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .brand {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #f59e0b;
            margin-bottom: 8px;
        }
        .eyebrow {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }
        h1 {
            font-size: 22px;
            line-height: 1.3;
            color: #0f172a;
            margin: 0 0 10px 0;
        }
        .meta {
            font-size: 10px;
            color: #64748b;
        }
        .meta span { margin-right: 14px; }
        .cover {
            width: 100%;
            max-height: 220px;
            border-radius: 8px;
            margin: 18px 0 22px 0;
        }
        .content h1,
        .content h2,
        .content h3,
        .content h4 {
            color: #0f172a;
            margin: 18px 0 8px 0;
            line-height: 1.35;
        }
        .content h2 { font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .content h3 { font-size: 14px; }
        .content p { margin: 0 0 12px 0; }
        .content ul, .content ol { margin: 0 0 12px 18px; padding: 0; }
        .content li { margin-bottom: 4px; }
        .content a { color: #b45309; text-decoration: none; }
        .content img { max-width: 100%; height: auto; }
        .content blockquote {
            margin: 12px 0;
            padding: 8px 14px;
            border-left: 3px solid #f59e0b;
            background: #fffbeb;
            color: #78350f;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        .content th, .content td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        .content th { background: #f1f5f9; }
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Vedrix · Insights</div>
        <div class="eyebrow">Research &amp; Reports · White Paper</div>
        <h1>{{ $paper->title }}</h1>
        <div class="meta">
            <span>Published: {{ optional($paper->created_at)->format('F j, Y') ?: '—' }}</span>
            <span>Status: {{ ucfirst($paper->status) }}</span>
        </div>
    </div>

    @if(!empty($coverDataUri))
        <img class="cover" src="{{ $coverDataUri }}" alt="">
    @endif

    <div class="content">
        {!! $descriptionHtml !!}
    </div>

    <div class="footer">
        © {{ date('Y') }} Vedrix · Generated from Insights White Papers · {{ $paper->slug }}
    </div>
</body>
</html>
