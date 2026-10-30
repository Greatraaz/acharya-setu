@extends('admin.layouts.app')
@section('title', 'Roles & Access')
@section('heading', 'Roles & Access')

@php
    $tab = session('tab', $tab ?? 'roles');
    $isSuper = auth()->user()->isSuperAdmin();
@endphp

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <p class="text-sm text-gray-500 max-w-2xl">
            Create roles, choose which admin modules they can open, then add team members and assign a role. Sidebar and pages follow that role.
        </p>
        <div class="flex gap-2">
            @if($tab === 'team')
            <button type="button" data-open-modal="staff-modal"
                    class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl">
                + Add team member
            </button>
            @else
            <button type="button" data-open-modal="role-modal"
                    class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl">
                + New role
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">{{ $errors->first() }}</div>
    @endif

    <div class="flex gap-2 border-b border-gray-200">
        <a href="{{ route('admin.roles.index') }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px {{ $tab === 'roles' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
            Roles
        </a>
        <a href="{{ route('admin.roles.index', ['tab' => 'team']) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px {{ $tab === 'team' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
            Team
        </a>
    </div>

    @if($tab === 'team')
    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
        @if($staff->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <p class="font-medium text-gray-600">No team members yet</p>
            <p class="text-sm mt-1">Add an admin user and assign a role.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Name</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Role</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($staff as $member)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 font-semibold text-gray-900">{{ $member->name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $member->email }}</td>
                        <td class="px-5 py-4">
                            @if($member->isSuperAdmin())
                                <span class="text-xs font-medium px-2 py-1 rounded-lg bg-violet-50 text-violet-700 border border-violet-100">Super Admin</span>
                            @elseif($member->adminRole)
                                <span class="text-xs font-medium px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $member->adminRole->name }}</span>
                            @else
                                <span class="text-xs text-gray-400">Full access</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-medium px-2 py-1 rounded-lg {{ $member->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $member->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <button type="button"
                                    class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold"
                                    data-open-modal="staff-modal"
                                    data-staff="{{ json_encode(['id' => $member->id, 'name' => $member->name, 'email' => $member->email, 'admin_role_id' => $member->admin_role_id, 'is_active' => (bool) $member->is_active]) }}">
                                Edit
                            </button>
                            @if((int) $member->id !== (int) auth()->id())
                            <form action="{{ route('admin.roles.users.destroy', $member) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Remove this team member from admin?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold ml-2">Remove</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($roles as $role)
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm flex flex-col">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $role->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('member', $role->users_count) }}</p>
                </div>
                @if($role->is_super)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-lg bg-violet-50 text-violet-700 border border-violet-100">Full access</span>
                @endif
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5 min-h-[2.5rem]">
                @foreach($role->moduleLabels() as $label)
                    <span class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-50 text-gray-600 border border-gray-100">{{ $label }}</span>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-end gap-3">
                <button type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                        data-open-modal="role-modal"
                        data-role="{{ json_encode(['id' => $role->id, 'name' => $role->name, 'is_super' => (bool) $role->is_super, 'modules' => $role->modules ?? []]) }}">
                    Edit
                </button>
                @if($role->users_count === 0)
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                      onsubmit="return confirm('Delete this role?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700">Delete</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="md:col-span-2 xl:col-span-3 bg-white border border-gray-100 rounded-2xl py-16 text-center text-gray-400">
            <p class="font-medium text-gray-600">No roles yet</p>
            <p class="text-sm mt-1">Create a role and tick the modules it can access.</p>
        </div>
        @endforelse
    </div>
    @endif
</div>

{{-- Role modal --}}
<div id="role-modal" class="hidden fixed inset-0 z-[80] bg-black/40 items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
        <form id="role-form" method="POST" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="_method" id="role-method" value="POST">
            <div class="flex items-center justify-between">
                <h3 id="role-modal-title" class="text-lg font-semibold text-gray-900">New role</h3>
                <button type="button" data-close-modal class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Role name *</label>
                <input id="role-name" name="name" required maxlength="80"
                       class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="e.g. Finance manager">
            </div>
            @if($isSuper)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_super" id="role-super" value="1" class="rounded border-gray-300 text-indigo-600">
                Full access to every module
            </label>
            @endif
            <div id="role-modules">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-600">Modules this role can open</p>
                    <button type="button" id="role-modules-all" class="text-[11px] font-semibold text-indigo-600">Select all</button>
                </div>
                <div class="space-y-4">
                    @foreach($groups as $group => $modules)
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-2">{{ $group }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($modules as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" class="role-module rounded border-gray-300 text-indigo-600">
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl">Save role</button>
            </div>
        </form>
    </div>
</div>

{{-- Staff modal --}}
<div id="staff-modal" class="hidden fixed inset-0 z-[80] bg-black/40 items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <form id="staff-form" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="staff-method" value="POST">
            <div class="flex items-center justify-between">
                <h3 id="staff-modal-title" class="text-lg font-semibold text-gray-900">Add team member</h3>
                <button type="button" data-close-modal class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Name *</label>
                <input id="staff-name" name="name" required maxlength="120"
                       class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email *</label>
                <input id="staff-email" name="email" type="email" required
                       class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Password <span id="staff-password-hint" class="text-gray-400 font-normal">*</span></label>
                <input id="staff-password" name="password" type="password" minlength="8"
                       class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Min. 8 characters">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Role *</label>
                <select id="staff-role" name="admin_role_id" required
                        class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select a role</option>
                    @foreach($roles as $role)
                        @if(! $role->is_super || $isSuper)
                        <option value="{{ $role->id }}">{{ $role->name }}{{ $role->is_super ? ' (full access)' : '' }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" id="staff-active" value="1" checked class="rounded border-gray-300 text-indigo-600">
                Can log in
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const roleStore = @json(route('admin.roles.store'));
    const staffStore = @json(route('admin.roles.users.store'));
    const roleUpdate = @json(url('/admin/roles'));
    const staffUpdate = @json(url('/admin/roles/users'));

    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function closeModal(el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => closeModal(btn.closest('[id$="-modal"]')));
    });
    document.querySelectorAll('[id$="-modal"]').forEach((modal) => {
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
    });

    document.getElementById('role-super')?.addEventListener('change', function () {
        document.getElementById('role-modules').classList.toggle('hidden', this.checked);
    });
    document.getElementById('role-modules-all')?.addEventListener('click', function () {
        const boxes = document.querySelectorAll('.role-module');
        const allOn = Array.from(boxes).every((b) => b.checked);
        boxes.forEach((b) => { b.checked = !allOn; });
    });

    document.querySelectorAll('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-open-modal');
            if (id === 'role-modal') fillRole(this.dataset.role ? JSON.parse(this.dataset.role) : null);
            if (id === 'staff-modal') fillStaff(this.dataset.staff ? JSON.parse(this.dataset.staff) : null);
            openModal(id);
        });
    });

    function fillRole(data) {
        const form = document.getElementById('role-form');
        const method = document.getElementById('role-method');
        document.getElementById('role-modal-title').textContent = data ? 'Edit role' : 'New role';
        document.getElementById('role-name').value = data ? data.name : '';
        const superEl = document.getElementById('role-super');
        if (superEl) superEl.checked = !!(data && data.is_super);
        document.querySelectorAll('.role-module').forEach((box) => {
            box.checked = !!(data && (data.modules || []).includes(box.value));
        });
        document.getElementById('role-modules').classList.toggle('hidden', !!(superEl && superEl.checked));
        if (data) {
            form.action = roleUpdate + '/' + data.id;
            method.value = 'PUT';
        } else {
            form.action = roleStore;
            method.value = 'POST';
        }
    }

    function fillStaff(data) {
        const form = document.getElementById('staff-form');
        const method = document.getElementById('staff-method');
        const password = document.getElementById('staff-password');
        document.getElementById('staff-modal-title').textContent = data ? 'Edit team member' : 'Add team member';
        document.getElementById('staff-name').value = data ? data.name : '';
        document.getElementById('staff-email').value = data ? data.email : '';
        document.getElementById('staff-role').value = data && data.admin_role_id ? data.admin_role_id : '';
        document.getElementById('staff-active').checked = data ? !!data.is_active : true;
        password.value = '';
        document.getElementById('staff-password-hint').textContent = data ? '(leave blank to keep)' : '*';
        password.required = !data;
        if (data) {
            form.action = staffUpdate + '/' + data.id;
            method.value = 'PUT';
        } else {
            form.action = staffStore;
            method.value = 'POST';
        }
    }
})();
</script>
@endpush
