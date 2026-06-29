<form id="editUserForm" class="space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
            <input type="text" name="name" value="{{ $user->name }}" required
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ $user->email }}" required
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ $user->phone }}"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
            <select name="gender"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                <option value="">Select</option>
                @foreach(['male','female','other'] as $g)
                    <option value="{{ $g }}" {{ $user->gender === $g ? 'selected' : '' }}>{{ ucfirst($g) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select name="is_active"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bio</label>
        <textarea name="bio" rows="2"
            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">{{ $user->bio }}</textarea>
    </div>

    @if($user->isMentor())
    <div class="border-t pt-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentor Details</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Expertise</label>
                <input type="text" name="expertise" value="{{ $user->expertise }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                <input type="text" name="company" value="{{ $user->company }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Designation</label>
                <input type="text" name="designation" value="{{ $user->designation }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Experience (years)</label>
                <input type="number" name="experience_years" value="{{ $user->experience_years }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rate / Minute (₹)</label>
                <input type="number" step="0.01" name="rate_per_minute" value="{{ $user->rate_per_minute }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Mentor Status</label>
                <select name="mentor_status"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    @foreach(['pending','approved','rejected'] as $st)
                        <option value="{{ $st }}" {{ $user->mentor_status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">LinkedIn URL</label>
                <input type="url" name="linkedin" value="{{ $user->linkedin }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
        </div>
    </div>
    @endif

    @if($user->isMentee())
    <div class="border-t pt-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentee Details</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">College</label>
                <input type="text" name="college" value="{{ $user->college }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Field of Study</label>
                <input type="text" name="field" value="{{ $user->field }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
                <select name="year"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <option value="">Select</option>
                    @foreach(['1st Year','2nd Year','3rd Year','4th Year','Graduate'] as $y)
                        <option value="{{ $y }}" {{ $user->year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subscription Plan</label>
                <input type="text" name="subscription_plan" value="{{ $user->subscription_plan }}"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Assign Mentor</label>
                <select name="assigned_mentor_id"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <option value="">— No Mentor —</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" {{ $user->assigned_mentor_id == $mentor->id ? 'selected' : '' }}>
                            {{ $mentor->name }} ({{ $mentor->designation ?? 'Mentor' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Career Goals</label>
                <textarea name="career_goals" rows="2"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">{{ $user->career_goals }}</textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Strengths</label>
                <textarea name="strengths" rows="2"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">{{ $user->strengths }}</textarea>
            </div>
        </div>
    </div>
    @endif

    <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeGlobalModal()"
            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
            Save Changes
        </button>
    </div>
</form>

<script>
$('#editUserForm').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url:  '{{ route('admin.users.update', $user->id) }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function () { closeGlobalModal(); location.reload(); },
        error: function (xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) alert(Object.values(errors).flat().join('\n'));
        }
    });
});
</script>