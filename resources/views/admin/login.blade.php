<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Login — AcharyaSetu</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <script src="{{ asset('admin/js/jquery4.0.js') }}"></script>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:linear-gradient(135deg,#1B3A5C 0%,#2d5a8e 100%)">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-10">
    <div class="text-center mb-8">
      <img src="{{ asset('admin/images/logo.jpg') }}" alt="AcharyaSetu" class="h-16 mx-auto object-contain mb-4">
      <p class="text-gray-500 text-sm mt-1">Sign in to continue</p>
    </div>

    {{-- CSRF is added automatically by @csrf inside the form --}}
    <form class="formsubmit" action="{{ route('admin.login.post') }}" method="POST" data-redirect="{{ route('admin.dashboard') }}">
      @csrf

      @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="mb-6">
        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Email</label>
        <input type="email" name="email"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
               placeholder="Enter email"
               value="{{ old('email') }}">
      </div>

      <div class="mb-6">
        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Password</label>
        <input type="password" name="password"
               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
               placeholder="Enter password">
      </div>

      <p class="form-error hidden"></p>

      <button type="submit"
              class="w-full py-3 rounded-xl text-white font-semibold text-sm transition-all"
              style="background:#F5A623">
        Sign In →
      </button>
    </form>
  </div>

  <script src="{{ asset('admin/js/admin.js') }}"></script>
  <!-- Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
