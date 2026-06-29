<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Dashboard') | AcharyaSetu</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="{{ asset('admin/js/jquery4.0.js') }}"></script>
<style>
/* Chrome, Edge, Safari */
::-webkit-scrollbar{
  width:6px;
  height:6px;
}

::-webkit-scrollbar-track{
  background:transparent;
}

::-webkit-scrollbar-thumb{
  background:#cbd5e1;
  border-radius:9999px;
}

::-webkit-scrollbar-thumb:hover{
  background:#94a3b8;
}


/* Firefox */
*{
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
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

<body class="bg-slate-50 text-slate-800 antialiased">

<div class="min-h-screen flex">

    @include('admin.layouts.sidebar')

    <div class="flex-1 ml-72 flex flex-col min-h-screen">

        @include('admin.layouts.header')

        <main class="p-8 flex-1">
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