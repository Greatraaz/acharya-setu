<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Dashboard') | Vedrix</title>
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=202608041559">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=202608041559">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=202608041559">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="{{ asset('admin/js/jquery4.0.js') }}"></script>
<style>
::-webkit-scrollbar{ width:6px; height:6px; }
::-webkit-scrollbar-track{ background:transparent; }
::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:9999px; }
::-webkit-scrollbar-thumb:hover{ background:#94a3b8; }
*{ scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }

/* All admin buttons: 8px radius (below 12px) */
button,
input[type="submit"],
input[type="button"],
input[type="reset"],
.btn,
a.btn,
.dt-button,
a[class*="bg-"][class*="rounded"],
button[class*="rounded"],
a[class*="px-"][class*="py-"][class*="rounded"] {
    border-radius: 8px !important;
}
/* Keep true pills only for status badges / chips that are not actions */
span[class*="rounded-full"] {
    border-radius: 9999px !important;
}

#admin-sidebar-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 40;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
}
#admin-sidebar-backdrop.is-open {
    opacity: 1;
    visibility: visible;
}
@media (max-width: 1023px) {
    #admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.25s ease;
    }
    #admin-sidebar.is-open {
        transform: translateX(0);
    }
}
</style>
<script>
 tailwind.config = {
   theme: {
     extend: {
       colors: {
         primary:'#F59E0B',
         dark:'#1E293B',
         accent:'#FFF7ED'
       },
       boxShadow:{
         soft:'0 10px 35px rgba(0,0,0,.06)'
       }
     }
   }
 }
</script>
</head>

<body class="bg-slate-50 text-slate-800 antialiased overflow-x-hidden">

<div id="admin-sidebar-backdrop" aria-hidden="true"></div>

<div class="min-h-screen flex">

    @include('admin.layouts.sidebar')

    <div class="flex-1 lg:ml-72 flex flex-col min-h-screen min-w-0 w-full">

        @include('admin.layouts.header')

        <main class="p-4 sm:p-6 lg:p-8 flex-1 min-w-0 max-w-full">
            @yield('content')
        </main>

        @include('admin.layouts.footer')

    </div>
</div>

    <script src="{{ asset('admin/js/admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')

</body>
</html>
