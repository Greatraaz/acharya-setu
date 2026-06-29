@extends('admin.layouts.app')
@section('title','Settings')
@section('heading','Settings')
@section('content')

<style>
    .settings-sidebar { width: 230px; min-width: 230px; transition: width 0.22s cubic-bezier(.4,0,.2,1); }
    .settings-sidebar.collapsed { width: 62px; min-width: 62px; }
    .settings-sidebar.collapsed .nav-label,
    .settings-sidebar.collapsed .sidebar-title,
    .settings-sidebar.collapsed .nav-group-label { display: none; }
    .settings-sidebar.collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
    .settings-sidebar.collapsed .nav-icon { margin-right: 0; }
    .nav-item { transition: background .15s, color .15s; }
    .nav-item.active { background-color: #eff6ff; color: #1d4ed8; border-right: 3px solid #1d4ed8; }
    .nav-item.active .nav-icon { color: #1d4ed8; }
    .tab-section { display: none; }
    .tab-section.active { display: block; animation: fadeIn .18s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .toggle-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
    .toggle-switch input { display: none; }
    .toggle-track { width: 44px; height: 24px; background: #d1d5db; border-radius: 9999px; transition: background .2s; position: relative; flex-shrink: 0; }
    .toggle-switch input:checked + .toggle-track { background: #2563eb; }
    .toggle-thumb { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform: translateX(20px); }
    .provider-card { border: 2px solid #e5e7eb; border-radius: 12px; transition: border-color .2s, box-shadow .2s; cursor: pointer; }
    .provider-card.selected { border-color: #2563eb; box-shadow: 0 0 0 3px #dbeafe; }
    .provider-card:hover:not(.selected) { border-color: #93c5fd; }
    .badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-green { background: #dcfce7; color: #166534; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    .badge-purple { background: #ede9fe; color: #5b21b6; }
    .form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 12px; font-size: 14px; color: #111827; background: white; transition: border-color .2s, box-shadow .2s; outline: none; }
    .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px #dbeafe; }
    .form-input[readonly] { background: #f9fafb; color: #6b7280; }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
    .save-btn { background: #2563eb; color: white; padding: 9px 22px; border-radius: 8px; font-size: 14px; font-weight: 500; border: none; cursor: pointer; transition: background .2s; }
    .save-btn:hover { background: #1d4ed8; }
    .save-btn.secondary { background: #6b7280; }
    .save-btn.secondary:hover { background: #4b5563; }
    .save-btn.success { background: #059669; }
    .save-btn.success:hover { background: #047857; }
    .save-btn.danger { background: #dc2626; }
    .save-btn.danger:hover { background: #b91c1c; }
    .section-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
    .section-card h3 { font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 4px; }
    .section-card .section-desc { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
    .divider { height: 1px; background: #f3f4f6; margin: 12px 0; }
    .setting-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; }
    .setting-row-label { font-size: 14px; color: #374151; font-weight: 500; }
    .setting-row-desc { font-size: 12px; color: #9ca3af; margin-top: 2px; }
    .provider-logo { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
    .key-badge { font-size: 11px; font-weight: 500; background: #f3f4f6; color: #6b7280; padding: 2px 8px; border-radius: 4px; font-family: monospace; }
    .maintenance-warning { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 10px; margin-top: 12px; }
    .gateway-tab-btn { padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; border: 1px solid #e5e7eb; background: white; color: #6b7280; cursor: pointer; transition: all .15s; }
    .gateway-tab-btn.active { background: #2563eb; color: white; border-color: #2563eb; }
</style>

<div class="flex gap-0" style="min-height: calc(100vh - 120px); background: #f9fafb; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">

    {{-- ============ MINI SIDEBAR ============ --}}
    <aside class="settings-sidebar bg-white border-r border-gray-100 flex flex-col py-4 overflow-y-auto" id="settingsSidebar">
        <div class="flex items-center justify-between px-4 mb-5">
            <span class="sidebar-title text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</span>
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3z"/></svg>
            </button>
        </div>

        @php
        $navGroups = [
            'General' => [
                ['app','App Settings','M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319z'],
                ['appearance','Appearance','M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z'],
                ['notifications','Notifications','M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917z M14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z'],
                ['email','Email / SMTP','M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z'],
                ['storage','Storage','M1 2a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2zm0 4.5a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1V7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.5zm0 4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1V11a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-.5z'],
            ],
            'Payments' => [
                ['payment','Payment Gateways','M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3h16v5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V7zm3 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1H3zm2.5 0a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z'],
            ],
            'Messaging' => [
                ['sms','SMS Gateways','M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2.414a1 1 0 0 0-.707.293L.854 13.146A.5.5 0 0 1 0 12.793V2z'],
            ],
            'Video Calls' => [
                ['videocall','Video Call','M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z'],
                ['agora','Agora','M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z'],
                ['zoom','Zoom','M1.5 1h13A1.5 1.5 0 0 1 16 2.5v11a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-11A1.5 1.5 0 0 1 1.5 1zM1 2.5v11a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-11a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5z'],
                ['google','Google Meet','M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z'],
            ],
        ];
        @endphp

        @foreach($navGroups as $group => $items)
        <div class="px-3 mb-1 mt-3">
            <span class="nav-group-label text-xs font-semibold text-gray-400 uppercase tracking-wider px-2">{{ $group }}</span>
        </div>
        <nav class="flex flex-col gap-0.5 px-2 mb-1">
            @foreach($items as [$id, $label, $icon])
            <button onclick="showTab('{{ $id }}')" data-tab="{{ $id }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-left text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-700 w-full {{ $id === 'app' ? 'active' : '' }}">
                <svg class="nav-icon text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="{{ $icon }}"/></svg>
                <span class="nav-label">{{ $label }}</span>
            </button>
            @endforeach
        </nav>
        @endforeach
    </aside>

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">

        {{-- ===== APP SETTINGS ===== --}}
        <div id="tab-app" class="tab-section active">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="section" value="app">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Application Settings</h2>
                        <p class="text-sm text-gray-500">Configure your application's core settings and preferences.</p>
                    </div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>

                <div class="section-card">
                    <h3>Site Information</h3>
                    <p class="section-desc">Basic details displayed across your application.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                            <input type="text" name="app_name" class="form-input" value="{{ config_val('app_name') }}" placeholder="MyApp">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                            <input type="text" name="app_tagline" class="form-input" value="{{ config_val('app_tagline') }}" placeholder="Short description">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" class="form-input" value="{{ config_val('contact_email') }}" placeholder="admin@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                            <input type="text" name="support_phone" class="form-input" value="{{ config_val('support_phone') }}" placeholder="+91 00000 00000">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
                            <input type="url" name="app_url" class="form-input" value="{{ config_val('app_url', config('app.url')) }}" placeholder="https://yourapp.com">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <h3>Legal & Social Links</h3>
                    <p class="section-desc">URLs for your legal pages and social media profiles.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="#6b7280" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/></svg>
                                    Privacy Policy URL
                                </span>
                            </label>
                            <input type="url" name="privacy_policy_url" class="form-input" value="{{ config_val('privacy_policy_url') }}" placeholder="https://yourapp.com/privacy">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="#6b7280" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.1.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/></svg>
                                    Terms of Service URL
                                </span>
                            </label>
                            <input type="url" name="terms_url" class="form-input" value="{{ config_val('terms_url') }}" placeholder="https://yourapp.com/terms">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <span class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="#0a66c2" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>
                                    LinkedIn URL
                                </span>
                            </label>
                            <input type="url" name="linkedin_url" class="form-input" value="{{ config_val('linkedin_url') }}" placeholder="https://linkedin.com/company/yourapp">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter / X URL</label>
                            <input type="url" name="twitter_url" class="form-input" value="{{ config_val('twitter_url') }}" placeholder="https://twitter.com/yourapp">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-input" value="{{ config_val('facebook_url') }}" placeholder="https://facebook.com/yourapp">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-input" value="{{ config_val('instagram_url') }}" placeholder="https://instagram.com/yourapp">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <h3>Localization</h3>
                    <p class="section-desc">Set your timezone, language, and date preferences.</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Language</label>
                            <select name="default_language" class="form-input form-select">
                                <option value="en" {{ config_val('default_language') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="hi" {{ config_val('default_language') === 'hi' ? 'selected' : '' }}>Hindi</option>
                                <option value="es" {{ config_val('default_language') === 'es' ? 'selected' : '' }}>Spanish</option>
                                <option value="fr" {{ config_val('default_language') === 'fr' ? 'selected' : '' }}>French</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select name="timezone" class="form-input form-select">
                                <option value="Asia/Kolkata" {{ config_val('timezone') === 'Asia/Kolkata' ? 'selected' : '' }}>UTC+05:30 (IST)</option>
                                <option value="UTC" {{ config_val('timezone') === 'UTC' ? 'selected' : '' }}>UTC+00:00 (GMT)</option>
                                <option value="America/New_York" {{ config_val('timezone') === 'America/New_York' ? 'selected' : '' }}>UTC-05:00 (EST)</option>
                                <option value="Europe/London" {{ config_val('timezone') === 'Europe/London' ? 'selected' : '' }}>UTC+00:00 (London)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                            <select name="date_format" class="form-input form-select">
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                                <option value="Y-m-d">YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <h3>Features & Access</h3>
                    <p class="section-desc">Enable or disable core application features.</p>
                    @foreach([
                        ['user_registration','User Registration','Allow new users to register publicly'],
                        ['email_verification','Email Verification','Require email confirmation on signup'],
                        ['two_factor_auth','Two-Factor Authentication','Enforce 2FA for all admin accounts'],
                    ] as [$key, $label, $desc])
                    <div class="setting-row">
                        <div>
                            <div class="setting-row-label">{{ $label }}</div>
                            <div class="setting-row-desc">{{ $desc }}</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ config_val($key) ? 'checked' : '' }}>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    <div class="divider"></div>
                    @endforeach

                    {{-- Maintenance Mode --}}
                    <div class="setting-row">
                        <div>
                            <div class="setting-row-label flex items-center gap-2">
                                Maintenance Mode
                                <span id="maintenance-badge" class="badge {{ config_val('maintenance_mode') ? 'badge-red' : 'badge-green' }}">
                                    {{ config_val('maintenance_mode') ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                            <div class="setting-row-desc">Temporarily disable the app for users</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="hidden" name="maintenance_mode" value="0">
                            <input type="checkbox" id="maintenance-toggle" name="maintenance_mode" value="1" {{ config_val('maintenance_mode') ? 'checked' : '' }} onchange="updateMaintenanceBadge(this)">
                            <div class="toggle-track" style="{{ config_val('maintenance_mode') ? 'background:#dc2626;' : '' }}"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    <div id="maintenance-warning" class="maintenance-warning" style="{{ config_val('maintenance_mode') ? '' : 'display:none' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#d97706" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>
                        <div>
                            <div class="text-sm font-semibold text-yellow-800">Maintenance mode is ON</div>
                            <div class="text-xs text-yellow-700 mt-0.5">All users (except admins) will see the maintenance page. Remember to turn this off when done.</div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <h3>Logo & Favicon</h3>
                    <p class="section-desc">Upload your brand assets.</p>
                    <div class="grid grid-cols-2 gap-6">
                        @foreach([['app_logo','App Logo','PNG, JPG up to 2MB'],['app_favicon','Favicon','ICO, PNG 32×32']] as [$name, $label, $hint])
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
                            <label class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer bg-gray-50 flex flex-col items-center">
                                <svg class="mb-2 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/></svg>
                                <p class="text-sm text-gray-500">Click to upload {{ strtolower($label) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
                                <input type="file" name="{{ $name }}" class="hidden" accept="image/*">
                            </label>
                            @if(config_val($name))
                            <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                                Current file uploaded
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== APPEARANCE ===== --}}
        <div id="tab-appearance" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="appearance">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Appearance</h2><p class="text-sm text-gray-500">Customize how your application looks.</p></div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
                <div class="section-card">
                    <h3>Theme & Colors</h3>
                    <p class="section-desc">Set your brand colors and default theme mode.</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="primary_color_picker" value="{{ config_val('primary_color', '#2563eb') }}" class="h-9 w-16 rounded border border-gray-200 cursor-pointer" oninput="document.getElementById('primary_color_text').value=this.value">
                                <input type="text" name="primary_color" id="primary_color_text" value="{{ config_val('primary_color', '#2563eb') }}" class="form-input flex-1">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accent_color_picker" value="{{ config_val('accent_color', '#7c3aed') }}" class="h-9 w-16 rounded border border-gray-200 cursor-pointer" oninput="document.getElementById('accent_color_text').value=this.value">
                                <input type="text" name="accent_color" id="accent_color_text" value="{{ config_val('accent_color', '#7c3aed') }}" class="form-input flex-1">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Theme</label>
                            <select name="default_theme" class="form-input form-select">
                                <option value="light" {{ config_val('default_theme') === 'light' ? 'selected' : '' }}>Light</option>
                                <option value="dark" {{ config_val('default_theme') === 'dark' ? 'selected' : '' }}>Dark</option>
                                <option value="system" {{ config_val('default_theme') === 'system' ? 'selected' : '' }}>System Default</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== NOTIFICATIONS ===== --}}
        <div id="tab-notifications" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="notifications">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Notification Settings</h2><p class="text-sm text-gray-500">Control how and when notifications are sent.</p></div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
                <div class="section-card">
                    <h3>System Notifications</h3>
                    <p class="section-desc">Configure automatic notification triggers.</p>
                    @foreach([
                        ['notify_new_user','New user registered','Alert admin when a new user signs up',true],
                        ['notify_booking','Session booking','Notify on new session/appointment booking',true],
                        ['notify_payment','Payment received','Alert on successful payment',true],
                        ['notify_failed_login','Failed login attempts','Alert on repeated failed logins',false],
                        ['notify_cancellation','Session cancellation','Notify on session cancellation',true],
                    ] as [$key, $label, $desc, $default])
                    <div class="setting-row">
                        <div><div class="setting-row-label">{{ $label }}</div><div class="setting-row-desc">{{ $desc }}</div></div>
                        <label class="toggle-switch">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ config_val($key, $default) ? 'checked' : '' }}>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    <div class="divider"></div>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- ===== EMAIL ===== --}}
        <div id="tab-email" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="email">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Email / SMTP Settings</h2><p class="text-sm text-gray-500">Configure outgoing email server settings.</p></div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.settings.test-email') }}" class="save-btn secondary">Test Connection</a>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </div>
                <div class="section-card">
                    <h3>SMTP Configuration</h3>
                    <p class="section-desc">Set up your mail server for sending transactional emails.</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach([
                            ['mail_driver','Mail Driver','select',['smtp'=>'SMTP','mailgun'=>'Mailgun','sendgrid'=>'SendGrid','ses'=>'Amazon SES']],
                            ['mail_encryption','Encryption','select',['tls'=>'TLS','ssl'=>'SSL','none'=>'None']],
                            ['mail_host','SMTP Host','text','smtp.mailtrap.io'],
                            ['mail_port','SMTP Port','number','587'],
                            ['mail_username','Username','text','SMTP username'],
                            ['mail_password','Password','password','SMTP password'],
                            ['mail_from_name','From Name','text','MyApp Support'],
                            ['mail_from_address','From Address','email','no-reply@yourapp.com'],
                        ] as $field)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field[1] }}</label>
                            @if($field[2] === 'select')
                            <select name="{{ $field[0] }}" class="form-input form-select">
                                @foreach($field[3] as $val => $text)
                                <option value="{{ $val }}" {{ config_val($field[0]) === $val ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="{{ $field[2] }}" name="{{ $field[0] }}" class="form-input {{ in_array($field[2],['text','password']) && str_contains($field[0],'password') ? 'font-mono' : '' }}" value="{{ $field[2] !== 'password' ? config_val($field[0]) : '' }}" placeholder="{{ $field[3] }}">
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== STORAGE ===== --}}
        <div id="tab-storage" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="storage">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Storage Settings</h2><p class="text-sm text-gray-500">Configure where files and media are stored.</p></div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
                <div class="section-card">
                    <h3>Storage Driver</h3>
                    <p class="section-desc">Choose where uploaded files are saved.</p>
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        @foreach([['local','Local','Server filesystem','badge-green'],['s3','AWS S3','Amazon Simple Storage','badge-yellow'],['cloudinary','Cloudinary','Image & video CDN','badge-blue']] as [$val,$name,$desc,$badge])
                        <label class="provider-card p-4 cursor-pointer {{ config_val('storage_driver','local') === $val ? 'selected' : '' }}">
                            <input type="radio" name="storage_driver" value="{{ $val }}" class="hidden" {{ config_val('storage_driver','local') === $val ? 'checked' : '' }}>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-800 text-sm">{{ $name }}</span>
                                <span class="badge {{ $badge }}">{{ config_val('storage_driver','local') === $val ? 'Active' : 'Select' }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ $desc }}</p>
                        </label>
                        @endforeach
                    </div>
                    <div id="s3-fields" class="{{ config_val('storage_driver') === 's3' ? '' : 'hidden' }}">
                        <div class="divider mb-4"></div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">AWS S3 Credentials</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['aws_key'=>'Access Key','aws_secret'=>'Secret Key','aws_region'=>'Region','aws_bucket'=>'Bucket Name'] as $k=>$l)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $l }}</label>
                                <input type="{{ str_contains($k,'secret') ? 'password' : 'text' }}" name="{{ $k }}" class="form-input font-mono text-sm" value="{{ str_contains($k,'secret') ? '' : config_val($k) }}" placeholder="{{ $l }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== PAYMENT GATEWAYS ===== --}}
        <div id="tab-payment" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="payment">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Payment Gateways</h2>
                        <p class="text-sm text-gray-500">Configure payment providers and API credentials.</p>
                    </div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>

                {{-- Gateway Tabs --}}
                <div class="flex gap-2 mb-5 flex-wrap">
                    @foreach([
                        ['razorpay','Razorpay','#2563eb'],
                        ['stripe','Stripe','#635bff'],
                        ['paypal','PayPal','#003087'],
                        ['paytm','Paytm','#00b9f1'],
                        ['phonepe','PhonePe','#5f259f'],
                        ['cashfree','Cashfree','#19a363'],
                    ] as [$gw, $label, $color])
                    <button type="button" onclick="showGateway('{{ $gw }}')" id="gwtab-{{ $gw }}" class="gateway-tab-btn {{ $gw === 'razorpay' ? 'active' : '' }}">
                        {{ $label }}
                        @if(config_val($gw.'_enabled'))
                        <span class="badge badge-green ml-1">ON</span>
                        @endif
                    </button>
                    @endforeach
                </div>

                {{-- Razorpay --}}
                <div id="gw-razorpay" class="gateway-section">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#fff3e0;"><span style="color:#e67e22;font-size:11px;font-weight:800;">Rz</span></div>
                            <div>
                                <h3 class="mb-0">Razorpay</h3>
                                <p class="text-xs text-gray-500">India's leading payment gateway — UPI, Cards, Net Banking</p>
                            </div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="razorpay_enabled" value="0">
                                    <input type="checkbox" name="razorpay_enabled" value="1" {{ config_val('razorpay_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Key ID <span class="key-badge">razorpay_key_id</span></label>
                                <input type="text" name="razorpay_key_id" class="form-input font-mono text-sm" value="{{ config_val('razorpay_key_id') }}" placeholder="rzp_live_xxxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Key Secret <span class="key-badge">razorpay_key_secret</span></label>
                                <input type="password" name="razorpay_key_secret" class="form-input font-mono text-sm" placeholder="Your secret key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                                <input type="password" name="razorpay_webhook_secret" class="form-input font-mono text-sm" placeholder="Webhook verification secret">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="razorpay_mode" class="form-input form-select">
                                    <option value="live" {{ config_val('razorpay_mode') === 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="test" {{ config_val('razorpay_mode','test') === 'test' ? 'selected' : '' }}>Test / Sandbox</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stripe --}}
                <div id="gw-stripe" class="gateway-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#f0f0ff;"><span style="color:#635bff;font-size:11px;font-weight:800;">St</span></div>
                            <div><h3 class="mb-0">Stripe</h3><p class="text-xs text-gray-500">Global payments — Cards, Wallets, SEPA, Bank transfers</p></div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="stripe_enabled" value="0">
                                    <input type="checkbox" name="stripe_enabled" value="1" {{ config_val('stripe_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Publishable Key <span class="key-badge">pk_</span></label>
                                <input type="text" name="stripe_publishable_key" class="form-input font-mono text-sm" value="{{ config_val('stripe_publishable_key') }}" placeholder="pk_live_xxxxxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key <span class="key-badge">sk_</span></label>
                                <input type="password" name="stripe_secret_key" class="form-input font-mono text-sm" placeholder="sk_live_xxxxxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Signing Secret <span class="key-badge">whsec_</span></label>
                                <input type="password" name="stripe_webhook_secret" class="form-input font-mono text-sm" placeholder="whsec_xxxxxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="stripe_mode" class="form-input form-select">
                                    <option value="live" {{ config_val('stripe_mode') === 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="test" {{ config_val('stripe_mode','test') === 'test' ? 'selected' : '' }}>Test</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL <span class="text-xs text-gray-400">(add this to your Stripe Dashboard)</span></label>
                                <div class="flex gap-2">
                                    <input type="url" class="form-input font-mono text-sm" value="{{ url('/webhooks/stripe') }}" readonly>
                                    <button type="button" class="save-btn secondary" onclick="navigator.clipboard.writeText('{{ url('/webhooks/stripe') }}')">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PayPal --}}
                <div id="gw-paypal" class="gateway-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#e8f0fe;"><span style="color:#003087;font-size:11px;font-weight:800;">PP</span></div>
                            <div><h3 class="mb-0">PayPal</h3><p class="text-xs text-gray-500">Global wallet payments & PayPal Checkout</p></div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="paypal_enabled" value="0">
                                    <input type="checkbox" name="paypal_enabled" value="1" {{ config_val('paypal_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                                <input type="text" name="paypal_client_id" class="form-input font-mono text-sm" value="{{ config_val('paypal_client_id') }}" placeholder="PayPal Client ID">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                                <input type="password" name="paypal_client_secret" class="form-input font-mono text-sm" placeholder="PayPal Client Secret">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="paypal_mode" class="form-input form-select">
                                    <option value="live" {{ config_val('paypal_mode') === 'live' ? 'selected' : '' }}>Live</option>
                                    <option value="sandbox" {{ config_val('paypal_mode','sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select name="paypal_currency" class="form-input form-select">
                                    <option value="USD">USD</option><option value="EUR">EUR</option><option value="GBP">GBP</option><option value="INR">INR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Paytm --}}
                <div id="gw-paytm" class="gateway-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#e0f7fe;"><span style="color:#00b9f1;font-size:10px;font-weight:800;">Ptm</span></div>
                            <div><h3 class="mb-0">Paytm</h3><p class="text-xs text-gray-500">India payments — Paytm Wallet, UPI, Cards</p></div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="paytm_enabled" value="0">
                                    <input type="checkbox" name="paytm_enabled" value="1" {{ config_val('paytm_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID</label>
                                <input type="text" name="paytm_merchant_id" class="form-input font-mono text-sm" value="{{ config_val('paytm_merchant_id') }}" placeholder="Paytm Merchant ID">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant Key</label>
                                <input type="password" name="paytm_merchant_key" class="form-input font-mono text-sm" placeholder="Paytm Merchant Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                <input type="text" name="paytm_website" class="form-input" value="{{ config_val('paytm_website','WEBPROD') }}" placeholder="WEBPROD">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="paytm_mode" class="form-input form-select">
                                    <option value="production">Production</option>
                                    <option value="staging">Staging</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PhonePe --}}
                <div id="gw-phonepe" class="gateway-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#f3eeff;"><span style="color:#5f259f;font-size:10px;font-weight:800;">Ph</span></div>
                            <div><h3 class="mb-0">PhonePe</h3><p class="text-xs text-gray-500">UPI & digital payments via PhonePe gateway</p></div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="phonepe_enabled" value="0">
                                    <input type="checkbox" name="phonepe_enabled" value="1" {{ config_val('phonepe_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID</label>
                                <input type="text" name="phonepe_merchant_id" class="form-input font-mono text-sm" value="{{ config_val('phonepe_merchant_id') }}" placeholder="PhonePe Merchant ID">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salt Key</label>
                                <input type="password" name="phonepe_salt_key" class="form-input font-mono text-sm" placeholder="Salt Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salt Index</label>
                                <input type="text" name="phonepe_salt_index" class="form-input" value="{{ config_val('phonepe_salt_index','1') }}" placeholder="1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="phonepe_mode" class="form-input form-select">
                                    <option value="production">Production</option>
                                    <option value="uat">UAT/Sandbox</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cashfree --}}
                <div id="gw-cashfree" class="gateway-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#e8fef2;"><span style="color:#19a363;font-size:10px;font-weight:800;">CF</span></div>
                            <div><h3 class="mb-0">Cashfree</h3><p class="text-xs text-gray-500">Payouts, payment links & subscriptions</p></div>
                            <div class="ml-auto flex items-center gap-2">
                                <span class="text-sm text-gray-600">Enable</span>
                                <label class="toggle-switch">
                                    <input type="hidden" name="cashfree_enabled" value="0">
                                    <input type="checkbox" name="cashfree_enabled" value="1" {{ config_val('cashfree_enabled') ? 'checked' : '' }}>
                                    <div class="toggle-track"><div class="toggle-thumb"></div></div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">App ID</label>
                                <input type="text" name="cashfree_app_id" class="form-input font-mono text-sm" value="{{ config_val('cashfree_app_id') }}" placeholder="Cashfree App ID">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                                <input type="password" name="cashfree_secret_key" class="form-input font-mono text-sm" placeholder="Secret Key">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select name="cashfree_mode" class="form-input form-select" style="max-width:200px;">
                                    <option value="production">Production</option>
                                    <option value="test">Test</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Currency Settings --}}
                <div class="section-card">
                    <h3>Currency Settings</h3>
                    <p class="section-desc">Global currency configuration for all active gateways.</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency</label>
                            <select name="default_currency" class="form-input form-select">
                                @foreach(['INR'=>'INR — Indian Rupee','USD'=>'USD — US Dollar','EUR'=>'EUR — Euro','GBP'=>'GBP — British Pound','AED'=>'AED — UAE Dirham'] as $code=>$label)
                                <option value="{{ $code }}" {{ config_val('default_currency','INR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-input" value="{{ config_val('currency_symbol','₹') }}" placeholder="₹">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Symbol Position</label>
                            <select name="currency_position" class="form-input form-select">
                                <option value="before" {{ config_val('currency_position','before') === 'before' ? 'selected' : '' }}>Before amount (₹100)</option>
                                <option value="after" {{ config_val('currency_position') === 'after' ? 'selected' : '' }}>After amount (100₹)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== SMS GATEWAYS ===== --}}
        <div id="tab-sms" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="sms">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">SMS Gateways</h2>
                        <p class="text-sm text-gray-500">Configure SMS providers for OTP and notifications.</p>
                    </div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>

                {{-- SMS provider tabs --}}
                <div class="flex gap-2 mb-5 flex-wrap">
                    @foreach(['msg91'=>'MSG91','twilio'=>'Twilio','fast2sms'=>'Fast2SMS','sns'=>'AWS SNS','vonage'=>'Vonage'] as $gw=>$label)
                    <button type="button" onclick="showSms('{{ $gw }}')" id="smstab-{{ $gw }}" class="gateway-tab-btn {{ $gw === 'msg91' ? 'active' : '' }}">
                        {{ $label }}
                        @if(config_val('sms_active_provider') === $gw)
                        <span class="badge badge-green ml-1">Active</span>
                        @endif
                    </button>
                    @endforeach
                </div>

                <div class="section-card mb-4">
                    <h3>Active SMS Provider</h3>
                    <p class="section-desc">Select which provider will be used to send SMS messages.</p>
                    <select name="sms_active_provider" class="form-input form-select" style="max-width:260px;">
                        @foreach(['msg91'=>'MSG91','twilio'=>'Twilio','fast2sms'=>'Fast2SMS','sns'=>'AWS SNS','vonage'=>'Vonage (Nexmo)'] as $gw=>$label)
                        <option value="{{ $gw }}" {{ config_val('sms_active_provider','msg91') === $gw ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MSG91 --}}
                <div id="sms-msg91" class="sms-section">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#fce7f3;"><span style="color:#db2777;font-size:10px;font-weight:800;">M91</span></div>
                            <div><h3 class="mb-0">MSG91</h3><p class="text-xs text-gray-500">India's leading OTP & transactional SMS platform</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Auth Key</label>
                                <input type="password" name="msg91_auth_key" class="form-input font-mono text-sm" placeholder="MSG91 Auth Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sender ID</label>
                                <input type="text" name="msg91_sender_id" class="form-input" value="{{ config_val('msg91_sender_id') }}" placeholder="MYAPP (6 chars)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">OTP Template ID</label>
                                <input type="text" name="msg91_otp_template_id" class="form-input font-mono text-sm" value="{{ config_val('msg91_otp_template_id') }}" placeholder="Template ID from MSG91">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                                <select name="msg91_route" class="form-input form-select">
                                    <option value="4" {{ config_val('msg91_route','4') === '4' ? 'selected' : '' }}>Transactional (4)</option>
                                    <option value="1" {{ config_val('msg91_route') === '1' ? 'selected' : '' }}>Promotional (1)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Twilio --}}
                <div id="sms-twilio" class="sms-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#fef3c7;"><span style="color:#d97706;font-size:10px;font-weight:800;">Tw</span></div>
                            <div><h3 class="mb-0">Twilio</h3><p class="text-xs text-gray-500">Global SMS, WhatsApp & voice platform</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account SID</label>
                                <input type="text" name="twilio_account_sid" class="form-input font-mono text-sm" value="{{ config_val('twilio_account_sid') }}" placeholder="ACxxxxxxxxxxxxxxxx">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Auth Token</label>
                                <input type="password" name="twilio_auth_token" class="form-input font-mono text-sm" placeholder="Twilio Auth Token">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">From Number</label>
                                <input type="text" name="twilio_from_number" class="form-input" value="{{ config_val('twilio_from_number') }}" placeholder="+1XXXXXXXXXX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Messaging Service SID <span class="text-xs text-gray-400">(optional)</span></label>
                                <input type="text" name="twilio_messaging_service_sid" class="form-input font-mono text-sm" value="{{ config_val('twilio_messaging_service_sid') }}" placeholder="MGxxxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fast2SMS --}}
                <div id="sms-fast2sms" class="sms-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#ecfdf5;"><span style="color:#059669;font-size:9px;font-weight:800;">F2S</span></div>
                            <div><h3 class="mb-0">Fast2SMS</h3><p class="text-xs text-gray-500">Affordable bulk & OTP SMS for India</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <input type="password" name="fast2sms_api_key" class="form-input font-mono text-sm" placeholder="Fast2SMS API Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sender ID</label>
                                <input type="text" name="fast2sms_sender_id" class="form-input" value="{{ config_val('fast2sms_sender_id') }}" placeholder="FSTSMS">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                                <select name="fast2sms_route" class="form-input form-select">
                                    <option value="dlt_manual" {{ config_val('fast2sms_route','dlt_manual') === 'dlt_manual' ? 'selected' : '' }}>DLT Manual</option>
                                    <option value="q" {{ config_val('fast2sms_route') === 'q' ? 'selected' : '' }}>Quick (non-DLT)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">DLT Template ID</label>
                                <input type="text" name="fast2sms_template_id" class="form-input font-mono text-sm" value="{{ config_val('fast2sms_template_id') }}" placeholder="DLT Template ID">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AWS SNS --}}
                <div id="sms-sns" class="sms-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#fff7ed;"><span style="color:#ea580c;font-size:9px;font-weight:800;">SNS</span></div>
                            <div><h3 class="mb-0">AWS SNS</h3><p class="text-xs text-gray-500">Amazon Simple Notification Service for global SMS</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Access Key ID</label>
                                <input type="text" name="sns_access_key" class="form-input font-mono text-sm" value="{{ config_val('sns_access_key') }}" placeholder="AKIAIOSFODNN7EXAMPLE">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Secret Access Key</label>
                                <input type="password" name="sns_secret_key" class="form-input font-mono text-sm" placeholder="AWS Secret Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select name="sns_region" class="form-input form-select">
                                    @foreach(['ap-south-1'=>'ap-south-1 (Mumbai)','us-east-1'=>'us-east-1 (Virginia)','eu-west-1'=>'eu-west-1 (Ireland)'] as $r=>$l)
                                    <option value="{{ $r }}" {{ config_val('sns_region','ap-south-1') === $r ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SMS Type</label>
                                <select name="sns_sms_type" class="form-input form-select">
                                    <option value="Transactional">Transactional</option>
                                    <option value="Promotional">Promotional</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vonage --}}
                <div id="sms-vonage" class="sms-section hidden">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="provider-logo" style="background:#eff6ff;"><span style="color:#1d4ed8;font-size:9px;font-weight:800;">Vng</span></div>
                            <div><h3 class="mb-0">Vonage (Nexmo)</h3><p class="text-xs text-gray-500">Global SMS & voice API platform</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <input type="text" name="vonage_api_key" class="form-input font-mono text-sm" value="{{ config_val('vonage_api_key') }}" placeholder="Vonage API Key">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                                <input type="password" name="vonage_api_secret" class="form-input font-mono text-sm" placeholder="Vonage API Secret">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">From Name / Number</label>
                                <input type="text" name="vonage_from" class="form-input" value="{{ config_val('vonage_from','MYAPP') }}" placeholder="MYAPP or +1XXXXXXXXXX">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        {{-- ===== VIDEO CALL GENERAL ===== --}}
        <div id="tab-videocall" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="videocall">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Video Call Settings</h2><p class="text-sm text-gray-500">Configure your preferred video call provider and behavior.</p></div>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
                <div class="section-card">
                    <h3>Active Provider</h3>
                    <p class="section-desc">Select which video call platform your application will use.</p>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach([
                            ['agora','Agora','Real-time video & voice SDK','#099DFD20','#099DFD'],
                            ['zoom','Zoom','Enterprise video meetings','#2D8CFF20','#2D8CFF'],
                            ['google','Google Meet','Google Workspace meetings','#1A73E820','#1A73E8'],
                        ] as [$id, $name, $desc, $bg, $color])
                        <label class="provider-card p-5 {{ config_val('video_provider','agora') === $id ? 'selected' : '' }}">
                            <input type="radio" name="video_provider" value="{{ $id }}" class="hidden" {{ config_val('video_provider','agora') === $id ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:{{ $bg }};">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="{{ $color }}" viewBox="0 0 16 16"><path d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/></svg>
                            </div>
                            <div class="font-semibold text-gray-800 text-sm mb-1">{{ $name }}</div>
                            <div class="text-xs text-gray-500 mb-3">{{ $desc }}</div>
                            <span class="badge {{ config_val('video_provider','agora') === $id ? 'badge-green' : 'badge-yellow' }}">{{ config_val('video_provider','agora') === $id ? 'Active' : 'Inactive' }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="section-card">
                    <h3>General Call Options</h3>
                    <p class="section-desc">Default behavior for all video call sessions.</p>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Duration (min)</label>
                            <input type="number" name="video_default_duration" class="form-input" value="{{ config_val('video_default_duration',60) }}" min="5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Participants</label>
                            <input type="number" name="video_max_participants" class="form-input" value="{{ config_val('video_max_participants',10) }}" min="2">
                        </div>
                    </div>
                    @foreach([
                        ['video_recording_enabled','Enable Recording','Allow sessions to be recorded',true],
                        ['video_waiting_room','Waiting Room','Participants wait until host admits them',true],
                        ['video_screen_sharing','Screen Sharing','Allow participants to share their screen',true],
                    ] as [$key,$label,$desc,$default])
                    <div class="setting-row">
                        <div><div class="setting-row-label">{{ $label }}</div><div class="setting-row-desc">{{ $desc }}</div></div>
                        <label class="toggle-switch">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ config_val($key,$default) ? 'checked' : '' }}>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    <div class="divider"></div>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- ===== AGORA ===== --}}
        <div id="tab-agora" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="agora">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Agora Configuration</h2><p class="text-sm text-gray-500">Real-time Video & Voice SDK</p></div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.settings.test-agora') }}" class="save-btn success">Test Connection</a>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </div>
                <div class="section-card">
                    <h3>API Credentials</h3>
                    <p class="section-desc">Enter your Agora credentials from the <a href="https://console.agora.io" target="_blank" class="text-blue-600 hover:underline">Agora Console</a>.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">App ID</label><input type="text" name="agora_app_id" class="form-input font-mono text-sm" value="{{ config_val('agora_app_id') }}" placeholder="Agora App ID"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">App Certificate</label><input type="password" name="agora_app_certificate" class="form-input font-mono text-sm" placeholder="App Certificate"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer ID</label><input type="text" name="agora_customer_id" class="form-input font-mono text-sm" value="{{ config_val('agora_customer_id') }}" placeholder="Customer ID"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Secret</label><input type="password" name="agora_customer_secret" class="form-input font-mono text-sm" placeholder="Customer Secret"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Token Expiry (seconds)</label><input type="number" name="agora_token_expiry" class="form-input" value="{{ config_val('agora_token_expiry',3600) }}"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Region</label><select name="agora_region" class="form-input form-select"><option value="global">Global</option><option value="ap">Asia Pacific</option><option value="na">North America</option><option value="eu">Europe</option></select></div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== ZOOM ===== --}}
        <div id="tab-zoom" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="zoom">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Zoom Configuration</h2><p class="text-sm text-gray-500">Enterprise Video Meetings — OAuth 2.0</p></div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.settings.zoom-connect') }}" class="save-btn success">Connect Zoom</a>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </div>
                <div class="section-card">
                    <h3>Zoom App Credentials</h3>
                    <p class="section-desc">Get these from your <a href="https://marketplace.zoom.us" target="_blank" class="text-blue-600 hover:underline">Zoom Marketplace</a> app.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label><input type="text" name="zoom_client_id" class="form-input font-mono text-sm" value="{{ config_val('zoom_client_id') }}" placeholder="Zoom Client ID"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label><input type="password" name="zoom_client_secret" class="form-input font-mono text-sm" placeholder="Client Secret"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Account ID</label><input type="text" name="zoom_account_id" class="form-input font-mono text-sm" value="{{ config_val('zoom_account_id') }}" placeholder="Zoom Account ID"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret Token</label><input type="password" name="zoom_webhook_secret" class="form-input font-mono text-sm" placeholder="Verification token"></div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">OAuth Redirect URL</label>
                            <div class="flex gap-2"><input type="url" class="form-input font-mono text-sm" value="{{ url('/admin/zoom/callback') }}" readonly><button type="button" class="save-btn secondary" onclick="copyToClipboard('{{ url('/admin/zoom/callback') }}')">Copy</button></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== GOOGLE MEET ===== --}}
        <div id="tab-google" class="tab-section">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="section" value="google">
                <div class="flex items-center justify-between mb-6">
                    <div><h2 class="text-lg font-semibold text-gray-800">Google Meet Configuration</h2><p class="text-sm text-gray-500">Google Workspace Video Calls</p></div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.settings.google-connect') }}" class="save-btn danger">Connect Google</a>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </div>
                <div class="section-card">
                    <h3>Google OAuth Credentials</h3>
                    <p class="section-desc">Create at <a href="https://console.cloud.google.com" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a> with Calendar API enabled.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label><input type="text" name="google_client_id" class="form-input font-mono text-sm" value="{{ config_val('google_client_id') }}" placeholder="*.apps.googleusercontent.com"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label><input type="password" name="google_client_secret" class="form-input font-mono text-sm" placeholder="Google Client Secret"></div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Redirect URI</label>
                            <div class="flex gap-2"><input type="url" class="form-input font-mono text-sm" value="{{ url('/admin/google/callback') }}" readonly><button type="button" class="save-btn secondary" onclick="copyToClipboard('{{ url('/admin/google/callback') }}')">Copy</button></div>
                        </div>
                    </div>
                </div>
                <div class="section-card">
                    <h3>Calendar Sync</h3>
                    <p class="section-desc">Configure Google Calendar integration options.</p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Calendar ID</label>
                        <input type="text" name="google_calendar_id" class="form-input" value="{{ config_val('google_calendar_id','primary') }}" placeholder="primary" style="max-width:380px;">
                    </div>
                    @foreach([
                        ['google_auto_calendar','Auto-create Calendar Events','Add sessions to Google Calendar automatically',true],
                        ['google_send_invites','Send Google Invites','Email Calendar invites to participants',true],
                        ['google_sync_cancellations','Sync Cancellations','Remove events when sessions are cancelled',true],
                    ] as [$key,$label,$desc,$default])
                    <div class="setting-row">
                        <div><div class="setting-row-label">{{ $label }}</div><div class="setting-row-desc">{{ $desc }}</div></div>
                        <label class="toggle-switch">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ config_val($key,$default) ? 'checked' : '' }}>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    <div class="divider"></div>
                    @endforeach
                </div>
            </form>
        </div>

    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('settingsSidebar').classList.toggle('collapsed');
}

function showTab(tab) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const el = document.getElementById('tab-' + tab);
    if (el) el.classList.add('active');
    const btn = document.querySelector(`[data-tab="${tab}"]`);
    if (btn) btn.classList.add('active');
}

function showGateway(gw) {
    document.querySelectorAll('.gateway-section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('[id^="gwtab-"]').forEach(b => b.classList.remove('active'));
    const el = document.getElementById('gw-' + gw);
    if (el) el.classList.remove('hidden');
    const btn = document.getElementById('gwtab-' + gw);
    if (btn) btn.classList.add('active');
}

function showSms(gw) {
    document.querySelectorAll('.sms-section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('[id^="smstab-"]').forEach(b => b.classList.remove('active'));
    const el = document.getElementById('sms-' + gw);
    if (el) el.classList.remove('hidden');
    const btn = document.getElementById('smstab-' + gw);
    if (btn) btn.classList.add('active');
}

function updateMaintenanceBadge(checkbox) {
    const badge = document.getElementById('maintenance-badge');
    const warning = document.getElementById('maintenance-warning');
    const track = checkbox.closest('.toggle-switch').querySelector('.toggle-track');
    if (checkbox.checked) {
        badge.textContent = 'ON'; badge.className = 'badge badge-red';
        warning.style.display = 'flex';
        track.style.background = '#dc2626';
    } else {
        badge.textContent = 'OFF'; badge.className = 'badge badge-green';
        warning.style.display = 'none';
        track.style.background = '';
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}

// Storage driver selection
document.querySelectorAll('input[name="storage_driver"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.provider-card').forEach(c => {
            c.classList.remove('selected');
            const badge = c.querySelector('.badge');
            if (badge) badge.textContent = 'Select';
        });
        const card = this.closest('.provider-card');
        card.classList.add('selected');
        const badge = card.querySelector('.badge');
        if (badge) badge.textContent = 'Active';
        document.getElementById('s3-fields').classList.toggle('hidden', this.value !== 's3');
    });
});
</script>

@endsection