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

/* Admin table toolbar — export utilities + filter fields */
.admin-table-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.admin-table-toolbar__exports,
[id$="-export-buttons"] {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
.dt-buttons {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
.admin-table-toolbar__exports .dt-button,
[id$="-export-buttons"] .dt-button {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 0.4rem 0.85rem !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    color: #334155 !important;
    margin: 0 !important;
    box-shadow: none !important;
}
.admin-table-toolbar__exports .dt-button:hover,
[id$="-export-buttons"] .dt-button:hover {
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    border-color: #bfdbfe !important;
}
.admin-table-toolbar__exports .dt-button span.dt-down-arrow,
[id$="-export-buttons"] .dt-button span.dt-down-arrow,
.dt-button.buttons-colvis::after {
    display: none !important;
}
.admin-table-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}
.admin-table-filters input[type="text"],
.admin-table-filters input[type="search"] {
    min-width: 220px;
}
.admin-table-filters select {
    min-width: 140px;
}

/* Universal admin filter/search forms */
.admin-filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.75rem;
    min-width: 0;
}
.admin-filter-form > div,
.admin-filter-form > label {
    min-width: 0;
}
.admin-filter-bar {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1rem;
}
.admin-filter-form input:not([type="hidden"]),
.admin-filter-form select,
.admin-filter-form .form-input {
    box-sizing: border-box;
}
.admin-filter-form .admin-filter-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}

/* Admin page header actions — keep add/create labels on one line */
main .flex.items-center.justify-between > a.inline-flex,
main .flex.items-center.justify-between > button.inline-flex,
main .flex.items-center.justify-between > div > a.inline-flex,
main .flex.items-center.justify-between > div > button.inline-flex,
main .flex.items-center.justify-between.flex-wrap > a.inline-flex,
main a.inline-flex.items-center[class*="bg-blue"],
main a.inline-flex.items-center[class*="bg-indigo"],
main a.inline-flex.items-center[class*="bg-violet"],
main a.inline-flex.items-center[class*="bg-emerald"],
main a.inline-flex.items-center[class*="bg-green"],
main a.inline-flex.items-center[class*="bg-orange"],
main button.inline-flex.items-center[class*="bg-blue"],
main button.inline-flex.items-center[class*="bg-indigo"],
main button.inline-flex.items-center[class*="bg-violet"],
main button.inline-flex.items-center[class*="bg-emerald"],
main button.inline-flex.items-center[class*="bg-green"],
main button.inline-flex.items-center[class*="bg-orange"] {
    white-space: nowrap;
    flex-shrink: 0;
}

/* Admin table toolbar & filters — mobile */
@media (max-width: 767px) {
    .admin-table-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .admin-table-toolbar__exports,
    [id$="-export-buttons"],
    .dt-buttons {
        width: 100%;
    }
    .admin-table-toolbar__exports .dt-button,
    [id$="-export-buttons"] .dt-button {
        flex: 1 1 calc(50% - 0.25rem);
        min-width: 0;
        justify-content: center;
    }
    .admin-table-filters,
    .admin-filter-form,
    .filter-bar form[method="GET"],
    main form[method="GET"].admin-filter-form {
        display: grid !important;
        grid-template-columns: 1fr !important;
        align-items: stretch !important;
        width: 100%;
        gap: 0.75rem;
    }
    .admin-filter-form.grid,
    form.admin-filter-form[class*="grid-cols"] {
        grid-template-columns: 1fr !important;
    }
    .admin-filter-form.grid > [class*="col-span"],
    form.admin-filter-form[class*="grid-cols"] > [class*="col-span"] {
        grid-column: 1 / -1 !important;
    }
    .admin-table-filters input[type="text"],
    .admin-table-filters input[type="search"],
    .admin-table-filters input[type="date"],
    .admin-table-filters select,
    .admin-filter-form > div,
    .admin-filter-form > .flex-1,
    .admin-filter-form > [class*="min-w-"],
    .admin-filter-form > [class*="w-"],
    .filter-bar form[method="GET"] > div {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
    }
    .admin-filter-form input:not([type="hidden"]),
    .admin-filter-form select,
    .admin-filter-form .form-input,
    .admin-table-filters input[type="text"],
    .admin-table-filters input[type="search"],
    .admin-table-filters input[type="date"],
    .admin-table-filters select,
    .admin-table-filters input[class*="min-w"],
    .filter-bar form[method="GET"] input,
    .filter-bar form[method="GET"] select {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
    }
    .admin-filter-form .admin-filter-actions,
    .admin-filter-form > div.flex.items-end,
    .admin-filter-form > div:has(button[type="submit"]) {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        grid-column: 1 / -1;
        width: 100%;
    }
    .admin-filter-form > button,
    .admin-filter-form > a,
    .admin-table-filters button,
    .admin-table-filters a[class*="border"],
    .admin-table-filters a[class*="bg-"],
    .filter-bar form[method="GET"] button,
    .filter-bar form[method="GET"] a.btn,
    .filter-bar form[method="GET"] .btn {
        width: 100% !important;
        text-align: center;
        justify-content: center;
    }
    .admin-filter-bar,
    .filter-bar {
        padding: 1rem !important;
    }
}

/* Admin header — notification & user menus on mobile */
@media (max-width: 639px) {
    #admin-notif-dropdown,
    #admin-user-menu-dropdown {
        position: fixed !important;
        left: 1rem !important;
        right: 1rem !important;
        top: 4.5rem !important;
        width: auto !important;
        max-width: none !important;
        margin-top: 0 !important;
        z-index: 60;
    }
    #admin-notif-btn {
        padding: 0.625rem !important;
    }
    #admin-user-menu-btn {
        padding: 0.5rem !important;
    }
    #admin-user-menu-btn .w-10 {
        width: 2.25rem;
        height: 2.25rem;
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

    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen min-w-0 w-full">

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
