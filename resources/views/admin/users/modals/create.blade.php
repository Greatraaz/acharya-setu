<form id="createUserForm" class="space-y-4">
    @csrf
    <input type="hidden" name="role" value="{{ $role }}">

    {{-- Common Fields --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
            <input type="text" name="name" required placeholder="Enter full name"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
            <input type="email" name="email" required placeholder="Enter email address"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
            <input type="password" name="password" required placeholder="Set a password"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" placeholder="Enter phone number"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
            <select name="gender"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Bio</label>
        <textarea name="bio" rows="2" placeholder="Write a short bio"
            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"></textarea>
    </div>

    {{-- Mentor-specific --}}
    @if($role === 'mentor')
    <div class="border-t pt-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentor Details</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Expertise</label>
                <input type="text" name="expertise" placeholder="E.g., Data Science, Marketing"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                <input type="text" name="company" placeholder="Company name"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Designation</label>
                <input type="text" name="designation" placeholder="Your designation"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Experience (years)</label>
                <input type="number" name="experience_years" placeholder="Number of years"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rate / Minute (₹)</label>
                <input type="number" step="0.01" name="rate_per_minute" placeholder="e.g. 150"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">LinkedIn URL</label>
                <input type="url" name="linkedin" placeholder="https://linkedin.com/in/your-profile"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
        </div>
    </div>
    @endif

    {{-- Mentee-specific --}}
    @if($role === 'mentee')
    <div class="border-t pt-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Mentee Details</p>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">College</label>
                <input type="text" name="college" placeholder="College name"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Field of Study</label>
                <input type="text" name="field" placeholder="E.g., Computer Science"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Year</label>
                <select name="year"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <option value="">Select Year</option>
                    <option>1st Year</option>
                    <option>2nd Year</option>
                    <option>3rd Year</option>
                    <option>4th Year</option>
                    <option>Graduate</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subscription Plan</label>
                <input type="text" name="subscription_plan" placeholder="E.g., Premium"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Assign Mentor</label>
                <select name="assigned_mentor_id"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    <option value="">— No Mentor —</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}">{{ $mentor->name }} ({{ $mentor->designation ?? 'Mentor' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Career Goals</label>
                <textarea name="career_goals" rows="2" placeholder="Describe your career goals"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"></textarea>
            </div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeGlobalModal()"
            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
            Create {{ ucfirst($role) }}
        </button>
    </div>
</form>
