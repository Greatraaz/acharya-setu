@if(!empty($session->plan_lapse))
<p class="plan-lapse-note">{{ ($audience ?? 'mentee') === 'mentor' ? $session->plan_lapse['mentor_message'] : $session->plan_lapse['mentee_message'] }}</p>
@endif
