<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blogs Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #1e3a5f; color: #fff; }
    </style>
</head>
<body>
    <h1>Blogs</h1>
    <table>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Title</th>
                <th>Category</th>
                <th>Author</th>
                <th>Blog Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($blogs as $i => $blog)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $blog->title }}</td>
                <td>{{ $blog->category ?: '—' }}</td>
                <td>{{ $blog->author ?: '—' }}</td>
                <td>{{ optional($blog->blog_date)->format('Y-m-d') ?: '—' }}</td>
                <td>{{ ucfirst($blog->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
