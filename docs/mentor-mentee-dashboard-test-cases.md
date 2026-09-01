# PART A — MENTOR DASHBOARD

---

## A1. Authentication, Access & Global UI

| TC ID | Title | Priority | Preconditions | Steps | Expected |
|-------|-------|----------|---------------|-------|----------|
| MNT-AUTH-01 | Mentor login — valid credentials | H | Approved mentor account | Login with mentor email/password | Redirect to `/mentor/dashboard` |
| MNT-AUTH-02 | Pending mentor blocked from dashboard | H | Mentor with `mentor_status=pending` | Visit `/mentor/dashboard` directly | Redirect to `/mentor/onboarding/pending` or blocked |
| MNT-AUTH-03 | Non-mentor blocked from mentor area | H | Logged in as mentee/admin | Visit `/mentor/dashboard` | 403 or redirect away |
| MNT-AUTH-04 | Mentor logout | H | Logged in as mentor | Click Sign Out in sidebar | Session cleared; redirected to login/home |
| MNT-AUTH-05 | Sidebar navigation | M | Approved mentor | Click each sidebar link | Correct page loads; active state highlights |
| MNT-AUTH-06 | Sidebar badges — sessions | M | Mentor with upcoming sessions | Open dashboard | My Sessions badge shows count |
| MNT-AUTH-07 | Sidebar badges — requests | M | Mentor with pending mentee requests | Open dashboard | Requests badge shows count |
| MNT-AUTH-08 | Sidebar wallet balance | L | Mentor with wallet balance | View sidebar Earnings row | Balance displayed correctly |
| MNT-AUTH-09 | Mobile bottom nav | M | Mobile viewport | Open mentor dashboard | Bottom nav visible; links work |
| MNT-AUTH-10 | Generic `/dashboard` redirect | M | Logged in as mentor | Visit `/dashboard` | Redirect to `/mentor/dashboard` |

---

## A2. Mentor Onboarding (pre-approval)

**URLs:** `/mentor/onboarding/1` … `/mentor/onboarding/5`, `/mentor/onboarding/pending`  
**Middleware:** auth + role:mentor (no approval required)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-ONB-01 | Step 1 loads | H | Open `/mentor/onboarding/1` | About-you form visible |
| MNT-ONB-02 | Step 1 validation | H | Submit empty step 1 | Errors on required fields (bio min 50 chars) |
| MNT-ONB-03 | Step 1 save + advance | H | Fill name, phone, bio (50+ chars), avatar; submit | Advances to step 2 |
| MNT-ONB-04 | Step 2 — professional info | H | Fill designation, company, experience, rate, field; submit | Advances to step 3 |
| MNT-ONB-05 | Step 3 — expertise | H | Select at least 1 expertise; submit | Advances to step 4 |
| MNT-ONB-06 | Step 4 — preferences | H | Fill preferred time, session length, strengths; submit | Advances to step 5 (review) |
| MNT-ONB-07 | Submit for approval | H | Review step → Submit | Status becomes pending; redirect to pending page |
| MNT-ONB-08 | Pending page | H | After submit | Pending message shown; dashboard inaccessible |
| MNT-ONB-09 | Resume incomplete onboarding | M | Leave at step 2, return later | Can continue from saved step |
| MNT-ONB-10 | Approved mentor skips onboarding | H | Approved mentor visits `/mentor/onboarding/1` | Redirect to dashboard |

---

## A3. Mentor Dashboard

**URL:** `/mentor/dashboard`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-DASH-01 | Dashboard loads | H | Open dashboard | Stats cards visible (sessions, earnings, mentees) |
| MNT-DASH-02 | Upcoming sessions widget | H | Mentor with upcoming sessions | Up to 5 upcoming sessions listed |
| MNT-DASH-03 | Pending requests widget | M | Mentor with pending requests | Pending mentee requests shown |
| MNT-DASH-04 | Empty dashboard | L | New mentor with no data | Page loads without errors; empty states shown |
| MNT-DASH-05 | Quick links work | M | Click session/request links from dashboard | Navigates to correct detail/list page |

---

## A4. Mentee Assignment Requests

**URL:** `/mentor/requests`  
**Filters:** `status` (pending / accepted / rejected / all), `search` or `q`  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-REQ-01 | Requests list loads | H | Open `/mentor/requests` | Pending requests listed by default |
| MNT-REQ-02 | Filter by status — accepted | M | Select accepted tab/filter | Only accepted requests shown |
| MNT-REQ-03 | Filter by status — rejected | M | Select rejected filter | Only rejected requests shown |
| MNT-REQ-04 | Search by mentee name | M | Enter mentee name in search, Apply | Matching requests only |
| MNT-REQ-05 | Pagination | M | 16+ requests → page 2 | Page 2 loads; filters preserved |
| MNT-REQ-06 | Accept request | H | Click Accept on pending request | Status accepted; mentee linked to mentor |
| MNT-REQ-07 | Reject request | H | Click Reject, optional note, confirm | Status rejected; note stored |
| MNT-REQ-08 | Accept already processed | M | Try accept on accepted request | Error or no-op; no duplicate link |

---

## A5. Sessions

**URLs:** `/mentor/sessions`, `/mentor/sessions/{id}`  
**Filters:** `filter` or `status` (all / upcoming / completed / cancelled), `q`, `date`  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-SES-01 | Sessions list loads | H | Open `/mentor/sessions` | Session rows visible |
| MNT-SES-02 | Filter upcoming | H | Select upcoming filter | Only upcoming sessions |
| MNT-SES-03 | Filter completed | M | Select completed filter | Only completed sessions |
| MNT-SES-04 | Filter cancelled | M | Select cancelled filter | Only cancelled sessions |
| MNT-SES-05 | Search by mentee/title | M | Enter search term, Apply | Filtered results |
| MNT-SES-06 | Filter by date | M | Pick a scheduled date | Sessions on that date only |
| MNT-SES-07 | Pagination | H | 16+ sessions → page 2 | Page 2 loads; filters preserved |
| MNT-SES-08 | Session detail page | H | Open a session | Mentee info, schedule, status, actions visible |
| MNT-SES-09 | Add meeting link | H | Upcoming session → add/update meeting link URL | Link saved; visible on detail |
| MNT-SES-10 | Complete session | H | Mark upcoming session complete | Status becomes completed |
| MNT-SES-11 | Cancel session | H | Cancel upcoming session (optional reason) | Status cancelled; mentee notified if applicable |
| MNT-SES-12 | Mark no-show | M | Mark upcoming as no-show | Session marked complete/no-show |
| MNT-SES-13 | Add session note | H | Post note on session (shared) | Note saved; visible on session |
| MNT-SES-14 | Add private note | M | Post note with is_shared=false | Note visible to mentor only |
| MNT-SES-15 | Join video call | H | Upcoming session → Join call | Video call page loads (`/sessions/{id}/call`) |

---

## A6. Session Notes (aggregate)

**URL:** `/mentor/notes`  
**Filters:** `search` or `q`, `visibility` (shared / private)  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-NOT-01 | Notes list loads | H | Open `/mentor/notes` | Notes from sessions listed |
| MNT-NOT-02 | Search notes | M | Search by mentee name or content | Filtered notes |
| MNT-NOT-03 | Filter shared vs private | M | Toggle visibility filter | Correct subset shown |
| MNT-NOT-04 | Pagination | M | 21+ notes → page 2 | Page 2 loads |
| MNT-NOT-05 | Sessions without notes hint | L | Completed sessions with no notes | Shown in sidebar/hint area (up to 8) |

---

## A7. Availability

**URL:** `/mentor/availability`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-AVL-01 | Availability page loads | H | Open `/mentor/availability` | Weekly schedule grid visible |
| MNT-AVL-02 | Enable day + time ranges | H | Enable Monday, add 09:00–17:00, save | Schedule saved; persists on reload |
| MNT-AVL-03 | Overlapping slots rejected | M | Add overlapping time ranges same day | Validation error |
| MNT-AVL-04 | Minimum slot duration | M | Add slot < 15 minutes | Validation error |
| MNT-AVL-05 | Update buffer/advance settings | M | Change buffer minutes, advance days, min notice; save | Settings saved |
| MNT-AVL-06 | Toggle live/bookable | H | Toggle live availability off/on | `is_active` flips; reflected in mentor listing |
| MNT-AVL-07 | Block a date | H | Block today or future date | Date blocked; not bookable |
| MNT-AVL-08 | Unblock a date | M | Remove blocked date | Date available again |
| MNT-AVL-09 | Public mentor profile shows slots | M | Mentee views mentor on `/mentors/{slug}` | Availability reflects saved schedule |

---

## A8. Wallet / Earnings

**URL:** `/mentor/wallet`  
**Pagination:** Transactions 20/page; Withdrawals 10/page (`withdrawals_page`)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-WAL-01 | Wallet page loads | H | Open `/mentor/wallet` | Balance, earnings stats, transaction list |
| MNT-WAL-02 | Transaction pagination | M | 21+ transactions → page 2 | Page 2 loads |
| MNT-WAL-03 | Withdrawal history pagination | M | 11+ withdrawals → withdrawals page 2 | Second withdrawals page loads |
| MNT-WAL-04 | Request withdrawal — valid | H | Enter amount ≥ ₹500 + bank details, submit | Withdrawal request created; pending status |
| MNT-WAL-05 | Withdrawal — below minimum | H | Enter amount < ₹500 | Validation error |
| MNT-WAL-06 | Withdrawal — exceeds available | H | Amount > available balance | Error message |
| MNT-WAL-07 | Earnings after completed session | H | Complete a paid session | Earnings reflected (after platform fee if applicable) |
| MNT-WAL-08 | Pending hold display | M | Session in hold period | Pending amount shown separately |

---

## A9. Profile Edit

**URLs:** `/mentor/profile/edit`, `PUT /mentor/profile`, `POST /mentor/profile/avatar`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-PRF-01 | Profile edit page loads | H | Open `/mentor/profile/edit` | Form pre-filled with current data |
| MNT-PRF-02 | Update bio and rate | H | Change bio, rate_per_minute, save | Success message; changes saved |
| MNT-PRF-03 | Update expertise/preferences | M | Add/remove expertise tags, save | Tags updated |
| MNT-PRF-04 | Upload avatar | M | Upload image (≤ 2 MB), save | Avatar updated on profile and public listing |
| MNT-PRF-05 | Invalid avatar rejected | M | Upload non-image or > 2 MB | Validation error |
| MNT-PRF-06 | Public profile reflects changes | M | Update designation; view `/mentors/{slug}` | Public profile shows new data |

---

## A10. My Mentees

**URLs:** `/mentor/mentees`, `/mentor/mentees/{id}`  
**Filters:** `search` or `q`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-MTE-01 | Mentees list loads | H | Open `/mentor/mentees` | Assigned/linked mentees listed |
| MNT-MTE-02 | Search mentee | M | Search by name/email/college | Filtered results |
| MNT-MTE-03 | Pagination | M | 21+ mentees → page 2 | Page 2 loads |
| MNT-MTE-04 | Mentee detail page | H | Open a mentee | Profile, sessions, enrollments, tracks visible |
| MNT-MTE-05 | Admin-assigned mentee appears | H | Admin assigns mentee to mentor | Mentee appears in list without manual accept |
| MNT-MTE-06 | Curriculum-linked mentee appears | M | Mentee has curriculum track for mentor | Mentee in list |

---

## A11. Progress Tracker (Journey)

**URLs:** `/mentor/journey`, `/mentor/journey/{mentee}`  
**Filters:** `search` or `q`, `status` (active / completed / paused)  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-JRN-01 | Journey list loads | H | Open `/mentor/journey` | Enrollments/tracks with progress listed |
| MNT-JRN-02 | Search by mentee name | M | Search mentee name | Filtered list |
| MNT-JRN-03 | Filter by status | M | Filter active/completed/paused | Correct subset |
| MNT-JRN-04 | Pagination | M | 16+ enrollments → page 2 | Page 2 loads |
| MNT-JRN-05 | Mentee journey detail | H | Open mentee progress page | Month/week/task progress visible |
| MNT-JRN-06 | Admin-created journey visible | H | Admin creates 6-month journey for assigned mentee | Track appears for mentor |
| MNT-JRN-07 | Progress updates after mentee completes task | M | Mentee completes a task | Progress % updates on mentor view |

---

## A12. Curriculum Builder

**URLs:** `/mentor/curriculum`, `/mentor/curriculum/tracks/{track}/months`, `/mentor/curriculum/months/{month}/weeks`, etc.  
**List filters:** `search` or `q`, `mentee_id`  
**Pagination:** 12 tracks per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-CUR-01 | Tracks list loads | H | Open `/mentor/curriculum` | Curriculum tracks listed |
| MNT-CUR-02 | Filter toolbar single row | M | View filter bar | Mentee dropdown, search, Apply on one row |
| MNT-CUR-03 | Filter by mentee | H | Select mentee from dropdown, Apply | Only that mentee's tracks |
| MNT-CUR-04 | Search track name | M | Search track name, Apply | Matching tracks only |
| MNT-CUR-05 | Pagination | M | 13+ tracks → page 2 | Page 2 loads; filters preserved |
| MNT-CUR-06 | Create track | H | + New Track → select mentee, name, description, submit | Track created; appears in list |
| MNT-CUR-07 | Create track validation | H | Submit without mentee or name | Field errors |
| MNT-CUR-08 | Edit track | H | Edit track name/status, save | Changes saved |
| MNT-CUR-09 | Manage months | H | Open track → Manage months | Months list page loads |
| MNT-CUR-10 | Add month | H | Add month (number, title, theme), save | Month created |
| MNT-CUR-11 | Edit/delete month | M | Update month; delete month | CRUD works; cascade handled |
| MNT-CUR-12 | Manage weeks | H | Open month → weeks page | Weeks list loads |
| MNT-CUR-13 | Add week with tasks | H | Add week, add task (title, type, plan_id) | Week and task saved |
| MNT-CUR-14 | Edit/delete task | M | Update task; delete task | CRUD works |
| MNT-CUR-15 | Add MCQ topic + questions | H | Add MCQ topic with questions (4 options, correct answer) | MCQs saved |
| MNT-CUR-16 | Add supporting material — PDF | M | Upload PDF material for week | File stored; visible in week |
| MNT-CUR-17 | Add supporting material — video link | M | Add videolink type with URL | Link saved |
| MNT-CUR-18 | Mentee sees track on journey | H | Mentee opens `/mentee/journey` | New track/months visible |
| MNT-CUR-19 | Cannot assign unrelated mentee | H | Try create track for mentee not in mentor's list | Validation/403 error |

---

## A13. Community (Mentor)

**URLs:** `/mentor/community`, `/mentor/community/create`, `/mentor/community/{slug}`  
**List filters:** `search` or `q`, `type`, `category`, `joined`  
**Pagination:** 18 channels/page; 30 messages/page in thread

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-COM-01 | Community index loads | H | Open `/mentor/community` | Channel cards with images |
| MNT-COM-02 | Filter by type — public/private | M | Apply type filter | Correct channels |
| MNT-COM-03 | Filter by category | M | Select category (career, tech, etc.) | Filtered list |
| MNT-COM-04 | Filter joined/not joined | M | Toggle joined filter | Correct subset |
| MNT-COM-05 | Search channel name | M | Search channel name | Matching channels |
| MNT-COM-06 | Pagination | M | 19+ channels → page 2 | Page 2 loads |
| MNT-COM-07 | Create public channel | H | Create channel: name, type public, image, submit | Channel created; creator is admin |
| MNT-COM-08 | Create private channel | M | Create private channel | Channel created; join restricted |
| MNT-COM-09 | Channel show — messages load | H | Open a channel | Messages load; scrolled to latest |
| MNT-COM-10 | Post text message | H | Type message, send | Message appears at bottom |
| MNT-COM-11 | Reply to message | H | Right-click message → Reply (or reply flow) | Reply with quote appears |
| MNT-COM-12 | Click reply quote scrolls to original | M | Click quoted parent in reply | Scrolls to and highlights original |
| MNT-COM-13 | Like message (context menu) | M | Right-click → Like | Like toggled |
| MNT-COM-14 | Report message | M | Right-click → Report, optional reason | Report recorded |
| MNT-COM-15 | Delete own message | M | Right-click → Delete on own message | Message removed |
| MNT-COM-16 | Message alignment — mine right | M | Post as logged-in mentor | Own bubble on right |
| MNT-COM-17 | Message alignment — others left | M | View another user's message | Bubble on left |
| MNT-COM-18 | Join public channel | H | Join a public channel not yet joined | Member added |
| MNT-COM-19 | Leave channel | M | Leave joined channel | Membership removed |
| MNT-COM-20 | Invite member | M | Channel admin invites mentee/mentor | User added to channel |
| MNT-COM-21 | Remove member | M | Admin removes member | Member removed |
| MNT-COM-22 | Delete channel (creator) | M | Creator deletes channel | Channel removed |
| MNT-COM-23 | Upload image in message | M | Attach image to message | Image displays in bubble |
| MNT-COM-24 | No video upload on channel create | M | Open create channel form | No video upload field (image only) |

---

## A14. Assessments (Mentor)

**URLs:** `/mentor/assessments`, create/edit/show/destroy  
**Filters:** `search` or `q`, `status` (active / inactive)  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-ASM-01 | Assessments list loads | H | Open `/mentor/assessments` | Assessment categories listed |
| MNT-ASM-02 | Search assessments | M | Search by title | Filtered results |
| MNT-ASM-03 | Filter active/inactive | M | Status filter | Correct subset |
| MNT-ASM-04 | Pagination | M | 21+ assessments → page 2 | Page 2 loads |
| MNT-ASM-05 | Create assessment | H | Fill title, description, 4 score bands, submit | Assessment created |
| MNT-ASM-06 | Edit assessment | M | Update title/status, save | Changes saved |
| MNT-ASM-07 | Delete assessment | M | Delete assessment | Removed from list |
| MNT-ASM-08 | Assessment show — completions | M | Open assessment with mentee submissions | Recent completions listed (up to 20) |

---

## A15. Assessment Questions (Mentor)

**URL:** `/mentor/assessment-questions`  
**Filters:** `assessment_id`, `search`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-ASQ-01 | Questions list loads | H | Open `/mentor/assessment-questions` | Questions listed |
| MNT-ASQ-02 | Filter by assessment | M | Select assessment from filter | Questions for that assessment only |
| MNT-ASQ-03 | Search question text | M | Search keyword | Matching questions |
| MNT-ASQ-04 | Create question | H | Select assessment, question text, 4 options, save | Question created |
| MNT-ASQ-05 | Edit question | M | Update question/options | Changes saved |
| MNT-ASQ-06 | Delete question | M | Delete question | Removed from list |
| MNT-ASQ-07 | Mentee can take assessment | H | Mentee opens assessment with questions | Questions visible; can submit |

---

# PART B — MENTEE DASHBOARD

---

## B1. Authentication, Access & Global UI

| TC ID | Title | Priority | Preconditions | Steps | Expected |
|-------|-------|----------|---------------|-------|----------|
| MTE-AUTH-01 | Mentee login — valid credentials | H | Onboarded mentee | Login with mentee email/password | Redirect to `/mentee/dashboard` |
| MTE-AUTH-02 | Incomplete onboarding blocked | H | Mentee with onboarding incomplete | Visit `/mentee/dashboard` | Redirect to `/mentee/onboarding/1` |
| MTE-AUTH-03 | Non-mentee blocked | H | Logged in as mentor/admin | Visit `/mentee/dashboard` | 403 or redirect away |
| MTE-AUTH-04 | Mentee logout | H | Logged in as mentee | Click Sign Out | Session cleared |
| MTE-AUTH-05 | Sidebar navigation | M | Onboarded mentee | Click each sidebar link | Correct page loads; active state |
| MTE-AUTH-06 | Sidebar session badge | M | Mentee with upcoming sessions | View sidebar | My Sessions badge shows count |
| MTE-AUTH-07 | Mobile bottom nav | M | Mobile viewport | Open mentee dashboard | Bottom nav works |
| MTE-AUTH-08 | Generic `/dashboard` redirect | M | Logged in as mentee | Visit `/dashboard` | Redirect to `/mentee/dashboard` |

---

## B2. Mentee Onboarding

**URLs:** `/mentee/onboarding/1` … `/mentee/onboarding/4`  
**Middleware:** auth + role:mentee

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-ONB-01 | Step 1 — About you | H | Fill name, gender, phone, address; submit | Advances to step 2 |
| MTE-ONB-02 | Step 1 validation | H | Submit empty step 1 | Field errors |
| MTE-ONB-03 | Step 2 — Education | H | Fill stream, field, college, year; submit | Advances to step 3 |
| MTE-ONB-04 | Step 3 — Tracks | H | Select at least 1 track; submit | Advances to step 4 |
| MTE-ONB-05 | Step 4 — Preferences | H | Fill time commitment, language, format; complete | Onboarding marked complete |
| MTE-ONB-06 | Dashboard accessible after onboarding | H | Complete onboarding | `/mentee/dashboard` loads |
| MTE-ONB-07 | Auto mentor assignment | M | Complete onboarding with auto-assign enabled | Mentor assigned if configured |
| MTE-ONB-08 | Resume incomplete onboarding | M | Stop at step 2, return | Can continue from saved data |

---

## B3. Mentee Dashboard

**URL:** `/mentee/dashboard`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-DASH-01 | Dashboard loads | H | Open dashboard | Stats, assigned mentor, upcoming sessions |
| MTE-DASH-02 | Upcoming sessions (max 3) | H | Mentee with 4+ upcoming sessions | Shows up to 3 upcoming |
| MTE-DASH-03 | Journey progress widget | M | Mentee with active enrollment | Progress shown (if plan allows) |
| MTE-DASH-04 | Recommended mentors | M | Mentee without assigned mentor | Top mentors suggested |
| MTE-DASH-05 | Pending mentor requests | M | Pending request exists | Shown on dashboard |
| MTE-DASH-06 | Empty dashboard | L | New mentee minimal data | No PHP/JS errors |

---

## B4. My Mentor / Change Mentor

**URL:** `/mentee/mentor/change`  
**Filters:** `search` or `q`, `field`  
**Pagination:** 12 mentors per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-MNT-01 | Change mentor page loads | H | Open `/mentee/mentor/change` | Current mentor + browse list |
| MTE-MNT-02 | Search mentors | M | Search by name/field | Filtered mentors |
| MTE-MNT-03 | Filter by field | M | Select field filter | Matching mentors |
| MTE-MNT-04 | Pagination | M | 13+ mentors → page 2 | Page 2 loads |
| MTE-MNT-05 | Send mentor request | H | Select mentor → Send request | Request pending; shown on dashboard |
| MTE-MNT-06 | Cancel pending request | H | Cancel pending request | Request removed |
| MTE-MNT-07 | Current mentor excluded from list | M | View mentor list | Assigned mentor not in browse list |
| MTE-MNT-08 | Duplicate request prevented | M | Request same mentor twice | Error or no duplicate |

---

## B5. Find Mentors (public) & Book Session

**URLs:** `/mentors`, `/mentors/{slug}`, `POST /mentee/sessions`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-FND-01 | Mentor search page loads | H | Open `/mentors` | Mentor cards listed |
| MTE-FND-02 | Mentor public profile | H | Open mentor profile | Bio, rate, availability visible |
| MTE-FND-03 | Book session — wallet payment | H | Select slot, duration, title; pay with wallet | Session booked; wallet debited |
| MTE-FND-04 | Book session — Razorpay | H | Book with Razorpay; complete payment | Session confirmed after verify |
| MTE-FND-05 | Book session — insufficient wallet | H | Book with wallet when balance too low | Error or redirect to top-up |
| MTE-FND-06 | Book outside availability | M | Try book when mentor unavailable | Slot rejected |
| MTE-FND-07 | Session appears in My Sessions | H | After booking | Session in `/mentee/sessions` upcoming |

---

## B6. Sessions (Mentee)

**URLs:** `/mentee/sessions`, `/mentee/sessions/{id}`  
**Filters:** `status`, `q`, `date`  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-SES-01 | Sessions list loads | H | Open `/mentee/sessions` | Sessions listed |
| MTE-SES-02 | Filter upcoming/completed/cancelled | H | Apply each status filter | Correct subset |
| MTE-SES-03 | Search sessions | M | Search by mentor/title | Filtered results |
| MTE-SES-04 | Filter by date | M | Pick scheduled date | Sessions on that date |
| MTE-SES-05 | Pagination | H | 16+ sessions → page 2 | Page 2 loads; filters preserved |
| MTE-SES-06 | Session detail | H | Open session | Mentor, schedule, meeting link, status |
| MTE-SES-07 | Cancel session (>2h before) | H | Cancel upcoming session > 2 hours away | Cancelled; wallet refunded if paid |
| MTE-SES-08 | Cancel blocked (<2h before) | H | Try cancel within 2 hours of start | Error; cannot cancel |
| MTE-SES-09 | Join video call | H | Upcoming session → Join | Video call page loads |
| MTE-SES-10 | Submit session review | H | After completed session → review form | Ratings saved |
| MTE-SES-11 | Review validation | M | Submit review without overall rating | Validation error |
| MTE-SES-12 | Download session invoice | M | Completed paid session with invoice | PDF downloads |

---

## B7. My Journey (Curriculum)

**URLs:** `/mentee/journey`, `/mentee/journey/month/{month}`, `/mentee/journey/week/{week}`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-JRN-01 | Journey index loads | H | Open `/mentee/journey` | Active curriculum track(s) visible |
| MTE-JRN-02 | No journey empty state | M | Mentee without enrollment | Empty/helpful message |
| MTE-JRN-03 | Open month view | H | Click a month | Weeks and overview shown |
| MTE-JRN-04 | Open week view | H | Click a week | Tasks, MCQs, materials listed |
| MTE-JRN-05 | Complete text task | H | Mark task complete with submission text | Task marked done; progress updates |
| MTE-JRN-06 | Complete task — file submission | M | Task requires file; upload file | Submission saved |
| MTE-JRN-07 | Answer MCQ correctly | H | Select correct option, submit | Score recorded; explanation if configured |
| MTE-JRN-08 | Answer MCQ incorrectly | M | Select wrong option | Incorrect feedback; can retry if allowed |
| MTE-JRN-09 | Weekly check-in | M | Submit mood, wins, challenges | Check-in saved |
| MTE-JRN-10 | Progress hidden without plan feature | M | Mentee on plan without progress report | Progress/scores gated or hidden |
| MTE-JRN-11 | Admin-assigned journey visible | H | Admin creates journey for mentee | Appears on mentee journey |

---

## B8. Wallet

**URL:** `/mentee/wallet`  
**Pagination:** 20 transactions per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-WAL-01 | Wallet page loads | H | Open `/mentee/wallet` | Balance and transaction history |
| MTE-WAL-02 | Transaction pagination | M | 21+ transactions → page 2 | Page 2 loads |
| MTE-WAL-03 | Initiate top-up | H | Enter amount (₹100–₹100,000), proceed | Razorpay checkout opens |
| MTE-WAL-04 | Verify top-up payment | H | Complete Razorpay payment | Balance increased |
| MTE-WAL-05 | Top-up below minimum | M | Amount < ₹100 | Validation error |
| MTE-WAL-06 | Top-up above maximum | M | Amount > ₹100,000 | Validation error |
| MTE-WAL-07 | Balance after session booking | M | Book session with wallet | Debit reflected in transactions |

---

## B9. Plans & Subscriptions

**URL:** `/mentee/plans`  
**Filters:** `status`, `search` or `q` on history  
**Pagination:** 10 subscriptions per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-PLN-01 | Plans page loads | H | Open `/mentee/plans` | Available plans + current subscription |
| MTE-PLN-02 | Subscribe free plan | H | Subscribe to free plan | Active immediately |
| MTE-PLN-03 | Subscribe paid plan — Razorpay | H | Subscribe paid plan; complete payment | Subscription active after verify |
| MTE-PLN-04 | Cancel subscription | H | Cancel active subscription | Status cancelled; access rules apply |
| MTE-PLN-05 | Subscription history filter | M | Filter by status | Correct subset |
| MTE-PLN-06 | Subscription history pagination | M | 11+ subs → page 2 | Page 2 loads |
| MTE-PLN-07 | Generate invoice | M | Generate invoice for paid subscription | Invoice created |
| MTE-PLN-08 | Download invoice PDF | M | Download invoice | PDF downloads |
| MTE-PLN-09 | Plan features unlock journey progress | M | Upgrade to plan with progress report | Journey progress becomes visible |

---

## B10. Quizzes

**URLs:** `/mentee/quizzes`, `/mentee/quizzes/{quiz}`, attempt/submit/result  
**Filters:** `status` (all / not_attempted / completed / passed / failed), `search` or `q`  
**Pagination:** 12 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-QUZ-01 | Quizzes list loads | H | Open `/mentee/quizzes` | Available quizzes listed |
| MTE-QUZ-02 | Filter by status | M | Filter completed/not attempted | Correct subset |
| MTE-QUZ-03 | Search quizzes | M | Search by title | Filtered results |
| MTE-QUZ-04 | Pagination | M | 13+ quizzes → page 2 | Page 2 loads |
| MTE-QUZ-05 | Start quiz attempt | H | Open quiz → Start | Questions displayed; timer if configured |
| MTE-QUZ-06 | Submit quiz answers | H | Answer all questions, submit | Attempt scored |
| MTE-QUZ-07 | View result | H | Open result page | Score, pass/fail, answers if enabled |
| MTE-QUZ-08 | Results hidden when disabled | M | Quiz with show_results=false | Result page blocked or limited |
| MTE-QUZ-09 | Retake rules | M | Attempt quiz again if allowed | Follows retake policy |

---

## B11. Assessments (Mentee)

**URLs:** `/mentee/assessments`, `/mentee/assessments/{id}`, submit  
**Filters:** `status` (all / pending / completed), `search` or `q`  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-ASM-01 | Assessments list loads | H | Open `/mentee/assessments` | Assessments listed |
| MTE-ASM-02 | Filter pending/completed | M | Apply status filter | Correct subset |
| MTE-ASM-03 | Search assessments | M | Search by title | Filtered results |
| MTE-ASM-04 | Pagination | M | 16+ assessments → page 2 | Page 2 loads |
| MTE-ASM-05 | Take assessment | H | Open assessment → answer all questions | Form submits |
| MTE-ASM-06 | Submit assessment | H | Submit answers (0–3 per question) | Progress saved; score band shown |
| MTE-ASM-07 | Resubmit/update answers | M | Retake same assessment | Upserts existing progress |
| MTE-ASM-08 | Completed badge on list | M | After submission | Status shows completed |

---

## B12. Community (Mentee)

**URLs:** `/mentee/community`, `/mentee/community/{slug}`  
**Filters:** same as mentor (`search`, `type`, `category`, `joined`)  
**Pagination:** 18 channels/page; 30 messages/page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-COM-01 | Community index loads | H | Open `/mentee/community` | Channel cards with images |
| MTE-COM-02 | Filters work | M | Apply type/category/joined/search filters | Correct channels |
| MTE-COM-03 | Pagination | M | 19+ channels → page 2 | Page 2 loads |
| MTE-COM-04 | Cannot create channel | H | Try `/mentee/community/create` or create UI | 403 or no create button |
| MTE-COM-05 | Join public channel | H | Join public channel | Membership added |
| MTE-COM-06 | Cannot join private without invite | H | Try join private channel | Blocked |
| MTE-COM-07 | Channel thread loads | H | Open joined channel | Messages; auto-scroll to latest |
| MTE-COM-08 | Post message | H | Send text message | Message at bottom; own bubble on right |
| MTE-COM-09 | Reply via context menu | H | Right-click → Reply | Reply with quote |
| MTE-COM-10 | Like/report/delete (context menu) | M | Right-click actions | Each action works |
| MTE-COM-11 | Leave channel | M | Leave joined channel | Membership removed |
| MTE-COM-12 | Creator cannot leave | M | Channel creator tries leave | Blocked or warning |
| MTE-COM-13 | UI matches mentor/admin thread | M | Compare with mentor channel view | Same layout, theme colors (not WhatsApp green) |

---

## B13. Job Listings

**URLs:** `/mentee/jobs`, `/mentee/jobs/{id}`, apply  
**Filters:** `search`, `job_type`, `location_type`  
**Pagination:** 12 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-JOB-01 | Jobs list loads | H | Open `/mentee/jobs` | Active job listings shown |
| MTE-JOB-02 | Search jobs | M | Search title/department/location | Filtered results |
| MTE-JOB-03 | Filter job type | M | Filter full_time/internship/etc. | Correct subset |
| MTE-JOB-04 | Filter location type | M | Filter remote/hybrid/onsite | Correct subset |
| MTE-JOB-05 | Pagination | M | 13+ jobs → page 2 | Page 2 loads |
| MTE-JOB-06 | Job detail page | H | Open a job | Full description, apply button |
| MTE-JOB-07 | Apply for job | H | Fill application form, submit | Success; application recorded |
| MTE-JOB-08 | Apply validation | H | Submit empty application | Field errors |
| MTE-JOB-09 | Duplicate application blocked | M | Apply same job twice | Error or already applied message |
| MTE-JOB-10 | Inactive jobs hidden | M | Admin deactivates job | Job not in mentee list |

---

## B14. Wellness Surveys (optional)

**URLs:** `/mentee/wellness`, `/mentee/wellness/{survey}`  
**Note:** Sidebar link may be commented out; routes exist.

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-WEL-01 | Wellness list loads | L | Open `/mentee/wellness` directly | Active surveys listed |
| MTE-WEL-02 | Take survey | L | Complete survey questions, submit | Responses saved |
| MTE-WEL-03 | Cannot retake completed survey | L | Reopen completed survey | Blocked or read-only |
| MTE-WEL-04 | Pagination | L | 21+ surveys → page 2 | Page 2 loads |

---

# PART C — SHARED ROUTES (Mentor & Mentee)

---

## C1. Account Settings

**URL:** `/account`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| SHR-ACC-01 | Account page loads | H | Open `/account` as mentor or mentee | Profile form visible |
| SHR-ACC-02 | Update name and phone | H | Change name/phone, save | Success; data updated |
| SHR-ACC-03 | Email read-only | M | Try change email on form | Email not editable |
| SHR-ACC-04 | Upload avatar | M | Upload profile photo | Avatar updated globally |
| SHR-ACC-05 | Change password | H | Current + new password (8+ chars) | Password updated; can re-login |
| SHR-ACC-06 | Wrong current password | H | Change password with wrong current | Validation error |
| SHR-ACC-07 | Delete account | M | Confirm delete with password | Account deactivated/deleted |

---

## C2. Video Call & Session Notes (shared)

**URLs:** `/sessions/{id}/call`, `/sessions/{id}/video-token`, `/sessions/{id}/my-note`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| SHR-CAL-01 | Mentor joins call | H | Mentor opens call for upcoming session | Agora/video UI loads |
| SHR-CAL-02 | Mentee joins call | H | Mentee opens same session call | Both can connect |
| SHR-CAL-03 | Non-participant blocked | H | Third user tries join URL | 403 or error |
| SHR-CAL-04 | Completed session call blocked | M | Try join call after session completed | Blocked or error |
| SHR-CAL-05 | End call | M | Click end call | Call ends; session updated if configured |
| SHR-CAL-06 | Private my-note — mentor | M | Save private note during/after call | Note saved; mentee cannot see |
| SHR-CAL-07 | Private my-note — mentee | M | Save private note | Note saved; mentor cannot see |
| SHR-CAL-08 | Shared notes JSON API | M | `GET /sessions/{id}/notes` as participant | Own + shared notes returned |

---

# PART D — CROSS-MODULE REGRESSION

Run after any major mentor/mentee release:

| # | Check | Expected |
|---|-------|----------|
| 1 | Mentee onboarding → dashboard → logout | No errors |
| 2 | Mentor onboarding → pending → admin approve → dashboard | Full flow works |
| 3 | Mentee finds mentor → sends request → mentor accepts | Mentee linked |
| 4 | Mentee books session → mentor adds link → both join call → mentor completes | Session completed; earnings/wallet correct |
| 5 | Mentor creates curriculum track → mentee completes task/MCQ | Progress on both sides |
| 6 | Admin assigns journey → visible on mentor curriculum + mentee journey | End-to-end visibility |
| 7 | Mentor creates assessment + questions → mentee submits | Score/completion recorded |
| 8 | Mentee subscribes to plan → journey progress unlocks | Plan gating works |
| 9 | Mentor creates community channel → mentee joins → message/reply/like | Messaging works both roles |
| 10 | Mentee wallet top-up → book session → cancel (>2h) → refund | Balances correct |
| 11 | Pagination + filters on sessions, community, curriculum | Page 2 preserves query string |
| 12 | Light/dark theme on community thread | Readable; correct bubble alignment |
| 13 | Mentee applies for job → visible in admin | Application stored |


### Mentor dashboard

| Sidebar label | URL | Pagination | Main filters |
|---------------|-----|------------|--------------|
| Dashboard | `/mentor/dashboard` | — | — |
| My Sessions | `/mentor/sessions` | 15 | status, q, date |
| Set Availability | `/mentor/availability` | — | — |
| Requests | `/mentor/requests` | 15 | status, search |
| My Mentees | `/mentor/mentees` | 20 | search |
| Curriculum | `/mentor/curriculum` | 12 | mentee_id, search |
| Progress Tracker | `/mentor/journey` | 15 | search, status |
| Community | `/mentor/community` | 18 | search, type, category, joined |
| Assessments → Categories | `/mentor/assessments` | 20 | search, status |
| Assessments → Questions | `/mentor/assessment-questions` | 20 | assessment_id, search |
| Earnings | `/mentor/wallet` | 20 / 10 withdrawals | page, withdrawals_page |
| Edit Profile | `/mentor/profile/edit` | — | — |
| Account Settings | `/account` | — | — |
| Session Notes | `/mentor/notes` | 20 | search, visibility |

### Mentee dashboard

| Sidebar label | URL | Pagination | Main filters |
|---------------|-----|------------|--------------|
| Dashboard | `/mentee/dashboard` | — | — |
| My Journey | `/mentee/journey` | — | — |
| Find Mentors | `/mentors` | varies | public search |
| My Mentor | `/mentee/mentor/change` | 12 | search, field |
| My Sessions | `/mentee/sessions` | 15 | status, q, date |
| Assessments | `/mentee/assessments` | 15 | status, search |
| Quizzes | `/mentee/quizzes` | 12 | status, search |
| Channels | `/mentee/community` | 18 | search, type, category, joined |
| Job Listings | `/mentee/jobs` | 12 | search, job_type, location_type |
| Plans | `/mentee/plans` | 10 (history) | status, search |
| Wallet | `/mentee/wallet` | 20 | — |
| Profile Settings | `/account` | — | — |

### Onboarding (pre-dashboard)

| Role | URL prefix | Steps |
|------|------------|-------|
| Mentor | `/mentor/onboarding/1`–`5` | 5 steps + pending page |
| Mentee | `/mentee/onboarding/1`–`4` | 4 steps |

---