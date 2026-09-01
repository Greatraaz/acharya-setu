## 1. Authentication & Global UI

| TC ID | Title | Priority | Preconditions | Steps | Expected |
|-------|-------|----------|---------------|-------|----------|
| AUTH-01 | Admin login — valid credentials | H | Admin account exists | Open `/admin/login`, enter valid email/password, submit | Redirect to dashboard; session active |
| AUTH-02 | Admin login — invalid credentials | H | — | Enter wrong password, submit | Error message; stay on login page |
| AUTH-03 | Non-admin blocked from admin | H | Logged in as mentor/mentee | Visit `/admin/dashboard` directly | 403 or redirect away from admin |
| AUTH-04 | Admin logout | H | Logged in as admin | Click logout | Session cleared; redirected to login |
| AUTH-05 | Sidebar navigation | M | Logged in as admin | Click each sidebar section link | Correct page loads; active state highlights |
| AUTH-06 | Utilities submenu expand/collapse | L | Logged in as admin | Click **Utilities** group toggle | Sub-items (Blogs, White Papers, etc.) show/hide |
| AUTH-07 | Admin profile update | M | Logged in as admin | Go to profile, update name, save | Success message; name updated |
| AUTH-08 | Admin password change | M | Logged in as admin | Profile → change password with valid current password | Password updated; can login with new password |
| AUTH-09 | Notifications page loads | L | Logged in as admin | Open `/admin/notifications` | Page loads without error |

---

## 2. Dashboard

**URL:** `/admin/dashboard`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| DASH-01 | Dashboard loads stats | H | Open dashboard | Stats cards visible (mentors, mentees, sessions, revenue, etc.) |
| DASH-02 | Recent activity links work | M | Click a recent session/log link if shown | Navigates to correct detail/list page |
| DASH-03 | Empty/new install dashboard | L | Fresh DB with minimal data | Dashboard loads without PHP/JS errors |

---

## 3. Mentors

**URLs:** `/admin/mentors`, `/admin/mentor/create`, `/admin/mentor/{id}/edit`, `/admin/mentors/{id}` (review)

**List filters:** `search`, `mentor_status` (all / approved / pending / rejected / suspended), `field`, `pending_changes=1`  
**Pagination:** 20 per page

**Create/Edit fields:** `name`, `email`, `password` / `new_password`, `phone`, `gender`, `avatar`, `designation`, `company`, `experience_years`, `linkedin`, `field`, `expertise[]`, `bio`, `rate_per_minute`, `preferences[]`, `mentor_status`, `is_active`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MNT-01 | List mentors page loads | H | Open `/admin/mentors` | Table/grid with mentor rows |
| MNT-02 | Search by name/email | H | Enter mentor name in search, submit | Only matching mentors shown |
| MNT-03 | Filter by status — pending | H | Select pending tab/filter | Only pending mentors shown |
| MNT-04 | Filter by status — approved | M | Select approved filter | Only approved mentors shown |
| MNT-05 | Pagination | H | With 21+ mentors, go to page 2 | Next set of mentors; URL has `?page=2` |
| MNT-06 | Pagination + filter preserved | H | Apply search, go to page 2 | Search term remains in URL/results |
| MNT-07 | Create mentor — required fields | H | Add Mentor → fill name, email, password, submit | Mentor created; success message |
| MNT-08 | Create mentor — validation | H | Submit empty form | Field errors on required fields |
| MNT-09 | Edit mentor — update bio/rate | H | Edit mentor, change bio and rate, save | Changes saved |
| MNT-10 | Upload avatar on edit | M | Edit mentor, upload image, save | Avatar displays on list/profile |
| MNT-11 | Toggle active/inactive | H | Click toggle status on approved mentor | `is_active` flips; reflected in list |
| MNT-12 | Review mentor profile | M | Open review page for a mentor | Full mentor details visible |
| MNT-13 | Soft delete mentor | H | Delete mentor, confirm | Mentor removed from active list |
| MNT-14 | Restore deleted mentor | M | Open deleted list, restore mentor | Mentor back in active list |
| MNT-15 | Pending profile changes filter | M | Filter `pending_changes=1` | Mentors with pending edits listed |

---

## 4. Mentor Approvals

**URL:** `/admin/mentor-approvals`, `/admin/mentors/approvals`, `/admin/mentors/pending-changes`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MAP-01 | Approval queue loads | H | Open mentor approvals | Pending/non-approved mentors listed |
| MAP-02 | Approve pending mentor | H | Review pending mentor → Approve | Status becomes approved; mentor can access dashboard |
| MAP-03 | Reject mentor with reason | H | Reject with reason text | Status rejected; reason stored |
| MAP-04 | Suspend approved mentor | M | Suspend active mentor | Status suspended; login blocked |
| MAP-05 | Reinstate suspended mentor | M | Reinstate suspended mentor | Status restored |
| MAP-06 | Approve profile change request | M | Pending changes → approve change | Updated fields applied |
| MAP-07 | Reject profile change request | M | Pending changes → reject with reason | Change rejected; old data kept |
| MAP-08 | Pagination on approvals list | M | 21+ pending items → page 2 | Pagination works |

---

## 5. Mentees

**URLs:** `/admin/mentees`, `/admin/mentee/create`, `/admin/mentee/{id}/edit`, `/admin/mentees/{id}`

**List filters:** `search`, `status` (active/inactive), `onboarded` (0/1), `assigned` (yes/no)  
**Pagination:** 20 per page

**Create/Edit fields:** `name`, `email`, `password`, `phone`, `gender`, `address`, `avatar`, `education_stream`, `field`, `college`, `year`, `tracks[]`, `weekly_time_commitment`, `monthly_budget`, `preferred_language`, `session_modes[]`, `assigned_mentor_id`, `subscription_plan`, `auto_assign_mentor`, `is_active`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| MTE-01 | List mentees page loads | H | Open `/admin/mentees` | Mentee list visible |
| MTE-02 | Search mentee | H | Search by name/email | Filtered results |
| MTE-03 | Filter active/inactive | M | Filter by status | Correct subset shown |
| MTE-04 | Filter onboarded | M | Filter onboarded = yes/no | Correct subset shown |
| MTE-05 | Filter assigned mentor | M | Filter assigned yes/no | Correct subset shown |
| MTE-06 | Pagination | H | 21+ mentees → page 2 | Page 2 loads correctly |
| MTE-07 | Create mentee | H | Fill required fields, submit | Mentee created |
| MTE-08 | Edit mentee | H | Update college/field, save | Changes saved |
| MTE-09 | Assign mentor | H | Open mentee → assign mentor modal → select mentor | `assigned_mentor_id` updated |
| MTE-10 | Toggle mentee status | H | Deactivate mentee | Mentee marked inactive |
| MTE-11 | View mentee detail | M | Click view on mentee | Detail page with onboarding info |
| MTE-12 | View mentee journey link | M | Click journey from mentee list | Opens curriculum with `mentee_id` filter |
| MTE-13 | Soft delete mentee | H | Delete mentee | Removed from active list |
| MTE-14 | Restore deleted mentee | M | Restore from trashed | Mentee restored |

---

## 6. Sessions

**URLs:** `/admin/sessions`, `/admin/sessions/create`, `/admin/sessions/{id}`, `/admin/sessions/{id}/edit`

**List filters:** `search`, `status`, `date_from`, `date_to`  
**Pagination:** 15 per page

**Form fields:** `mentor_id`, `mentee_id`, `title`, `agenda`, `scheduled_at`, `duration_minutes`, `timezone`, `meeting_provider`, `meeting_link`, `amount`, `currency`, `status`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| SES-01 | Sessions list loads | H | Open `/admin/sessions` | Session rows visible |
| SES-02 | Search session | H | Search by title/booking ref/mentee name | Filtered results |
| SES-03 | Filter by status — upcoming | H | Filter upcoming | Only upcoming sessions |
| SES-04 | Filter by date range | M | Set date_from and date_to | Sessions in range only |
| SES-05 | Pagination | H | 16+ sessions → page 2 | Pagination works |
| SES-06 | Create session | H | Create with mentor, mentee, title, datetime | Session created |
| SES-07 | Edit session | M | Change title/meeting link | Updated successfully |
| SES-08 | View session detail | H | Open session show page | Mentor, mentee, status, notes visible |
| SES-09 | Mark session complete | H | Complete an upcoming session | Status → completed |
| SES-10 | Cancel session | H | Cancel upcoming session | Status → cancelled |
| SES-11 | Mark no-show | M | Mark no-show on session | Status updated |
| SES-12 | Add session note | M | Add note on session | Note saved and visible |
| SES-13 | Export sessions CSV | M | Click export | CSV downloads with session data |
| SES-14 | Generate session invoice | M | Generate invoice for paid session | Invoice created/downloadable |
| SES-15 | Delete session | M | Delete session (if allowed) | Session removed |

---

## 7. Wallet

**URL:** `/admin/wallet`, `/admin/wallet/customer/{user}`

**List filters:** `from_date`, `to_date`, `type` (credit/debit/refund/transfer), `search` (TXN reference)  
**Pagination:** 20 per page

**Adjust modal:** `action` (credit/debit), `amount`, `description`  
**Transfer modal:** `sender_type`, `sender_id`, `receiver_type`, `receiver_id`, `amount`, `note`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| WAL-01 | Wallet index loads | H | Open `/admin/wallet` | Transaction list visible |
| WAL-02 | Filter by date range | M | Set from/to dates | Filtered transactions |
| WAL-03 | Filter by type — credit | M | Filter type = credit | Credits only |
| WAL-04 | Search by reference | M | Search TXN id | Matching row(s) |
| WAL-05 | Pagination | H | 21+ transactions → page 2 | Pagination works |
| WAL-06 | View customer wallet | H | Open user wallet detail | Balance + history shown |
| WAL-07 | Credit user wallet | H | Adjust → credit amount + description | Balance increases; transaction logged |
| WAL-08 | Debit user wallet | H | Adjust → debit (within balance) | Balance decreases |
| WAL-09 | Debit exceeds balance | H | Debit more than balance | Error; no invalid debit |
| WAL-10 | Transfer between users | M | Transfer from mentor to mentee | Both balances update; transfer logged |

---

## 8. Withdrawals

**URL:** `/admin/withdrawals`

**List filters:** `search`, `status` (pending/paid/rejected)  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| WDR-01 | Withdrawals list loads | H | Open withdrawals | Pending/paid/rejected rows |
| WDR-02 | Filter pending | H | Filter status = pending | Pending only |
| WDR-03 | Search withdrawal | M | Search by mentor name/ref | Filtered results |
| WDR-04 | Pagination | M | 21+ rows → page 2 | Works |
| WDR-05 | Approve withdrawal | H | Approve pending request | Status paid; wallet debited |
| WDR-06 | Reject withdrawal | H | Reject with admin note (min 5 chars) | Status rejected; funds not debited |
| WDR-07 | Reject without note | M | Submit reject with empty note | Validation error |

---

## 9. Call Logs (Call Records)

**URL:** `/admin/call-logs`, `/admin/call-logs/{id}`

**List filters:** `search`, `status`, `provider` (agora/zoom/google), `date_from`, `date_to`, `user_id`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| CLG-01 | Call logs list loads | H | Open call logs | Log entries visible |
| CLG-02 | Filter by provider | M | Filter Agora | Agora calls only |
| CLG-03 | Filter by date range | M | Set date filters | Filtered list |
| CLG-04 | Search call log | M | Search by session/user | Matching logs |
| CLG-05 | Pagination | M | Page 2 with enough data | Works |
| CLG-06 | View call log detail | M | Open single log | Duration, participants, status shown |
| CLG-07 | Delete single log | M | Delete one log | Log removed |
| CLG-08 | Bulk delete logs | M | Select multiple → bulk delete | Selected logs removed |
| CLG-09 | Export call logs CSV | M | Click export | CSV downloads |

---

## 10. Curriculum — Global Streams (Catalog)

**URL:** `/admin/curriculum/catalog`

**Form fields:** `name`, `icon`, `color`, `description`, `is_active`, `sort_order`  
**Pagination:** 12 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| CUR-01 | Catalog list loads | H | Open curriculum catalog | Global streams listed |
| CUR-02 | Create global stream | H | Add stream with name, icon, description | Stream created |
| CUR-03 | Edit global stream | M | Update name/description | Saved |
| CUR-04 | Toggle stream active | M | Deactivate stream | `is_active` false |
| CUR-05 | Delete global stream | M | Delete with confirmation | Stream removed |
| CUR-06 | Pagination | M | 13+ streams → page 2 | Works |

---

## 11. Curriculum — 6-Month Journey Manager

**URLs:** `/admin/curriculum`, `/admin/curriculum/streams/{id}/months`, `/admin/curriculum/months/{id}/weeks`

**Stream form:** `mentee_id`, `name`, `icon`, `color`, `description`, `is_active`, `sort_order`  
**Month form:** `mentee_id`, `month_number` (1–12), `title`, `description`, `theme`, `learning_outcomes`, `milestone_badge`, `is_active`  
**Week form:** `week_number`, `title`, `focus`, `mentor_guide`, `video_url`, `is_active`  
**List filter:** `mentee_id`  
**Pagination:** 12 per page (streams)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| JRN-01 | Journey streams list | H | Open `/admin/curriculum` | Mentee tracks listed |
| JRN-02 | Filter by mentee | H | Select mentee in filter | Only that mentee's tracks |
| JRN-03 | Create mentee track | H | Create track for mentee | Track appears in list |
| JRN-04 | Add month to track | H | Open track → add month 1 | Month created |
| JRN-05 | Add week to month | H | Add week with title/focus | Week created |
| JRN-06 | Add task to week | M | Create task on week page | Task saved |
| JRN-07 | Add MCQ to week | M | Create MCQ topic/question | MCQ saved |
| JRN-08 | Edit stream/month/week | M | Update titles | Changes saved |
| JRN-09 | Delete task/MCQ | M | Delete item | Removed |
| JRN-10 | View enrollments | M | Open enrollments list | Enrolled mentees shown |
| JRN-11 | Review submission | H | Approve/reject mentee submission with note | Status updated |
| JRN-12 | Pagination on streams | M | 13+ tracks → page 2 | Works |

---

## 12. Quizzes & MCQs

**URL:** `/admin/quizzes`, `/admin/quizzes/create`, `/admin/quizzes/{id}`

**Form fields:** `title`, `description`, `time_limit`, `pass_score`, `is_published`; questions: `question`, `type` (mcq/true_false/short_answer), `marks`, `options[]`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| QUZ-01 | Quizzes list loads | H | Open quizzes | Published quizzes listed |
| QUZ-02 | Pagination | M | 21+ quizzes → page 2 | Works |
| QUZ-03 | Create quiz — MCQ | H | Create quiz with 1+ MCQ question | Quiz saved |
| QUZ-04 | Validation — no questions | M | Submit quiz without questions | Error shown |
| QUZ-05 | View quiz detail | M | Open quiz show | Questions listed |
| QUZ-06 | Take quiz (admin preview) | M | Start attempt → submit answers | Result/score shown |
| QUZ-07 | Delete quiz | M | Delete own quiz | Quiz removed |

---

## 13. Assessments (Categories)

**URLs:** `/admin/assessments`, create/edit/show

**Form fields:** `title`, `description`, `instructions`, `status`, `image_file`, `icon_file`, `bands[0–3][from|to|heading|description]`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| ASM-01 | Assessments list loads | H | Open assessments | Table with assessments |
| ASM-02 | Pagination | H | 21+ assessments → page 2 | Works |
| ASM-03 | Create assessment | H | Fill title, description, 4 score bands, status active | Assessment created |
| ASM-04 | Upload image and icon | M | Add image/icon files | Files stored; show on list |
| ASM-05 | Edit assessment | H | Change title/status | Updated |
| ASM-06 | View assessment + completions | M | Open show page | Questions count, recent completions |
| ASM-07 | Delete assessment | H | Delete with confirm | Removed (and questions cascade per app rules) |
| ASM-08 | Inactive assessment | M | Set status inactive | Not shown to mentees on frontend |

---

## 14. Assessment Questions

**URL:** `/admin/assessment-questions`

**Form fields:** `assessment_id`, `question`, `options[0–3]` (Likert 0–3 scale)  
**List filters:** `search`, `assessment_id`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| ASQ-01 | Questions list loads | H | Open assessment questions | Questions table |
| ASQ-02 | Filter by assessment | H | Select assessment dropdown | Questions for that assessment only |
| ASQ-03 | Search question text | M | Search keyword | Filtered rows |
| ASQ-04 | Pagination | M | Page 2 | Works |
| ASQ-05 | Create question | H | Select assessment, enter question + 4 options | Question created |
| ASQ-06 | Edit question | M | Update question text | Saved |
| ASQ-07 | Delete question | M | Delete question | Removed |

---

## 15. Job Listings

**URL:** `/admin/jobs`, create/edit/show

**Form fields:** `title`, `department`, `location`, `location_type`, `job_type`, `experience_level`, salary fields, `description`, `responsibilities`, `requirements`, `benefits`, `skills_raw`, `apply_url`, `apply_email`, `deadline`, `openings`, `status`, `is_featured`  
**List filters:** `search`, `status`, `department`, `job_type`, `location_type`  
**Pagination:** 15 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| JOB-01 | Jobs list loads | H | Open jobs | Job rows visible |
| JOB-02 | Search jobs | H | Search by title | Filtered |
| JOB-03 | Filter by job type | M | Filter full_time/internship | Correct subset |
| JOB-04 | Filter by location type | M | Filter remote/onsite | Correct subset |
| JOB-05 | Pagination | H | 16+ jobs → page 2 | Works |
| JOB-06 | Create job — draft | H | Fill required fields, status draft | Job created |
| JOB-07 | Toggle publish | H | Toggle status to active | Job visible on public jobs page |
| JOB-08 | Edit job | M | Update salary/description | Saved |
| JOB-09 | View applications | M | Open job show → applications tab | Applications listed (paginated) |
| JOB-10 | Delete job | M | Delete job | Removed |

---

## 16. Community Channels

**URLs:** `/admin/community`, `/admin/community/create`, `/admin/community/{slug}`

**Create form:** `name`, `slug`, `description`, `icon`, `type` (public/private), `category`, `image`, `video`  
**Pagination:** 18 per page (channels), 30 per page (messages)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| COM-01 | Channels list loads | H | Open community | Channels grid/table |
| COM-02 | Pagination | M | 19+ channels → page 2 | Works |
| COM-03 | Create public channel | H | Fill name, type public, category | Channel created |
| COM-04 | Create private channel | M | Type = private | Channel created; join restricted |
| COM-05 | Open channel — messages | H | Click channel | Message thread loads |
| COM-06 | Post message as admin | H | Type message, send | Message appears |
| COM-07 | Message pagination | M | Channel with 31+ messages | Page 2 of messages |
| COM-08 | Invite member | M | Invite user to channel | User added to members |
| COM-09 | Remove member | M | Remove member from channel | Member removed |
| COM-10 | Delete message | M | Delete reported/spam message | Message removed |
| COM-11 | Delete channel | M | Delete channel | Channel removed |

---

## 17. Premium Plans

**URL:** `/admin/plans`, create/edit

**Form fields:** `name`, `description`, `badge_label`, `badge_color`, `price_monthly`, `price_yearly`, `currency`, GST fields, `duration`, `limit_sessions`, `features_raw`, toggles (`progress_report_enabled`, `is_active`, `is_featured`), `sort_order`, payment gateway price IDs  
**Pagination:** 20 per page (includes stats for total/active/featured)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| PLN-01 | Plans list loads | H | Open plans | Plans with stats summary |
| PLN-02 | Pagination | M | 21+ plans → page 2 | Works |
| PLN-03 | Create plan | H | Fill name, prices, features, active | Plan created |
| PLN-04 | Edit plan | H | Update pricing/description | Saved |
| PLN-05 | Toggle active | H | Deactivate plan | Plan inactive on mentee plans page |
| PLN-06 | Toggle featured | M | Mark featured | Featured badge on frontend |
| PLN-07 | Drag reorder | M | Reorder plans | `sort_order` updated |
| PLN-08 | Soft delete plan | M | Delete plan | Plan in trashed state |
| PLN-09 | Restore plan | M | Restore deleted plan | Plan active again |

---

## 18. Subscriptions & Invoices

**URLs:** `/admin/subscriptions`, `/admin/subscriptions/{id}`, `/admin/invoices`

**Subscription filters:** `q`, `plan_id`, `status`, `payment_status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| SUB-01 | Subscriptions list loads | H | Open subscriptions | Subscription rows |
| SUB-02 | Search subscription | M | Search by user/plan | Filtered |
| SUB-03 | Filter by plan | M | Select plan filter | Subscriptions for plan only |
| SUB-04 | Filter by status | M | active/cancelled/expired | Correct subset |
| SUB-05 | Pagination | M | Page 2 | Works |
| SUB-06 | View subscription detail | H | Open show page | Plan, dates, payment status |
| SUB-07 | Generate invoice | H | Generate invoice for paid subscription | Invoice created |
| SUB-08 | Download invoice PDF | M | Download from subscription/invoices | PDF downloads |

---

## 19. Blogs

**URL:** `/admin/blogs`

**Form fields:** `title`, `category`, `author`, `blog_date`, `status`, `image`, `description` (TinyMCE), `meta_title`, `meta_description`, `meta_keywords`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| BLG-01 | Blogs list loads | H | Open blogs | Blog rows |
| BLG-02 | Search by title | H | Search keyword | Filtered |
| BLG-03 | Filter active/inactive | M | Status filter | Correct subset |
| BLG-04 | Pagination | H | Page 2 | Works; Sr. No. continues correctly |
| BLG-05 | Create blog | H | Title, image, description, status active | Blog created |
| BLG-06 | Edit blog | H | Update content | Saved |
| BLG-07 | Delete blog | H | Delete blog | Removed |
| BLG-08 | Export Excel | M | Click export Excel | File downloads |
| BLG-09 | Export PDF | M | Click export PDF | PDF downloads |
| BLG-10 | Frontend visibility | H | Active blog on `/insights/blogs` | Blog visible; inactive hidden |

---

## 20. White Papers

**URL:** `/admin/white-papers`

**Form fields:** `title`, `image`, `description`, `status`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| WTP-01 | List loads | H | Open white papers | Rows visible |
| WTP-02 | Search + status filter | M | Apply filters | Filtered list |
| WTP-03 | Pagination | M | Page 2 | Works |
| WTP-04 | Create white paper | H | Title, image, description, active | Created |
| WTP-05 | Edit white paper | M | Update description | Saved |
| WTP-06 | Delete white paper | M | Delete | Removed |
| WTP-07 | Frontend download | H | Download from `/insights/white-papers` | PDF/content downloads |

---

## 21. Case Studies

**URL:** `/admin/case-studies`

**Form fields:** `title`, `industry`, `image`, `description`, `result`, `status`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| CSC-01 | List loads | H | Open case studies | Rows visible |
| CSC-02 | Search + filter | M | Search/filter status | Works |
| CSC-03 | Pagination | M | Page 2 | Works |
| CSC-04 | Create case study | H | All required fields + industry | Created |
| CSC-05 | Edit case study | M | Update result field | Saved |
| CSC-06 | Delete case study | M | Delete | Removed |
| CSC-07 | Frontend list + detail | H | Active case study on insights | List + show page work |

---

## 22. Testimonials

**URL:** `/admin/testimonials`

**Form fields:** `name`, `designation`, `image`, `message` (TinyMCE), `status`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| TST-01 | List loads | H | Open testimonials | Rows visible |
| TST-02 | Search + filter | M | Apply filters | Works |
| TST-03 | Pagination | M | Page 2 | Works |
| TST-04 | Create testimonial | H | Name, designation, message, image | Created |
| TST-05 | Edit testimonial | M | Update message | Saved |
| TST-06 | Delete testimonial | M | Delete | Removed |
| TST-07 | Frontend visibility | H | Active on `/insights/testimonials` | Card displays with stars |

---

## 23. Podcasts

**URL:** `/admin/podcasts`

**Form fields:** `title`, `slug`, `image`, `description`, `podcast_type` (audio / youtube_url), `status`, `audio` OR `youtube_url`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| POD-01 | List loads | H | Open podcasts | Rows visible |
| POD-02 | Pagination | M | Page 2 | Works |
| POD-03 | Create audio podcast | H | Type audio, upload audio file | Created |
| POD-04 | Create YouTube podcast | H | Type youtube_url, paste URL | Created |
| POD-05 | Validation — audio type without file | M | Submit without audio | Error |
| POD-06 | Edit podcast | M | Change description/status | Saved |
| POD-07 | Delete podcast | M | Delete | Removed |
| POD-08 | Frontend lightbox player | H | Open on `/insights/podcasts` | Modal player works |

---

## 24. Videos

**URL:** `/admin/videos`

**Form fields:** `title`, `slug`, `youtube_url`, `description`, `status`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| VID-01 | List loads | H | Open videos | Rows visible |
| VID-02 | Pagination | M | Page 2 | Works |
| VID-03 | Create video | H | Title, valid YouTube URL, active | Created; thumbnail from YouTube |
| VID-04 | Invalid YouTube URL | M | Bad URL | Validation error |
| VID-05 | Edit video | M | Update URL/description | Saved |
| VID-06 | Delete video | M | Delete | Removed |
| VID-07 | Frontend lightbox | H | Play on `/insights/videos` | YouTube embed in modal |

---

## 25. Download Centre

**URL:** `/admin/download-centres`

**Form fields:** `title`, `slug`, `image`, `document` (pdf/doc/xls/ppt/zip), `description`, `status`  
**List filters:** `search`, `status`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| DLC-01 | List loads | H | Open download centre | Rows visible |
| DLC-02 | Search + filter | M | Apply filters | Works |
| DLC-03 | Pagination | M | Page 2 | Works |
| DLC-04 | Create download item | H | Title, image, document upload, active | Created |
| DLC-05 | Auto slug from title | M | Leave slug blank | Slug auto-generated |
| DLC-06 | Edit — replace document | M | Upload new document | Old file replaced |
| DLC-07 | Delete item | M | Delete | Removed |
| DLC-08 | Frontend download | H | Download from `/insights/download-centre` | File downloads with correct name |

---

## 26. Events & Webinars

**URL:** `/admin/events-webinars`

**Form fields:** `type` (webinar/event), `status`, `title`, `speaker`, `location`, `image`, `start_date`, `end_date`, `start_time`, `end_time`, `description`, `event_agenda`, `who_should_attend`, `what_you_will_learn`, `faq`  
**List filters:** `search`, `status`, `type`  
**Pagination:** 20 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| EVT-01 | List loads | H | Open events & webinars | Rows visible |
| EVT-02 | Filter by type — webinar | H | Filter type webinar | Webinars only |
| EVT-03 | Filter by type — event | H | Filter type event | Events only |
| EVT-04 | Search + status filter | M | Apply filters | Works |
| EVT-05 | Pagination | M | Page 2 | Works |
| EVT-06 | Create webinar | H | Type webinar, dates, speaker, image | Created |
| EVT-07 | Create event with location | H | Type event, location field | Created |
| EVT-08 | Edit session | M | Update agenda/FAQ | Saved |
| EVT-09 | Delete session | M | Delete | Removed |
| EVT-10 | Frontend webinars page | H | Active webinar on `/insights/webinars` | Listed with upcoming/past filter |
| EVT-11 | Frontend events page | H | Active event on `/insights/events` | Listed |
| EVT-12 | Registration form | M | Register on frontend detail page | Registration saved |

---

## 27. App Settings

**URL:** `/admin/settings`

**Sections:** App, Appearance, Notifications, Email (SMTP), Storage, Payment (Razorpay/Stripe/etc.), SMS, Video call (Agora/Zoom/Google)

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| SET-01 | Settings page loads | H | Open settings | All sections/tabs visible |
| SET-02 | Save app settings | H | Update app name, contact email, save | Success message; values persist |
| SET-03 | Upload logo/favicon | M | Upload app logo | Logo saved and used |
| SET-04 | Save appearance colors | M | Change primary/accent color | Saved |
| SET-05 | Toggle maintenance mode | M | Enable maintenance mode | Frontend shows maintenance (if implemented) |
| SET-06 | Save SMTP settings | M | Update mail host/port | Saved |
| SET-07 | Test email | M | Click test email | Test email sent or clear error |
| SET-08 | Save Razorpay keys | H | Enter Razorpay key/secret, enable | Payment config saved |
| SET-09 | Save Agora/video settings | M | Update Agora app ID | Saved |
| SET-10 | Invalid section submit | L | Submit with invalid email format | Validation error |

---

## 28. Activity Logs

**URL:** `/admin/logs`, `/admin/logs/{id}`

**List filters:** `search`, `module`, `level`, `event`, `user_id`, `date_from`, `date_to`  
**Pagination:** 50 per page

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| LOG-01 | Logs list loads | H | Open logs | Log entries visible |
| LOG-02 | Filter by module | M | Select module filter | Filtered logs |
| LOG-03 | Filter by level | M | info/warning/danger | Correct subset |
| LOG-04 | Filter by date range | M | Set dates | Filtered |
| LOG-05 | Search logs | M | Search keyword | Matching logs |
| LOG-06 | Pagination | M | Page 2 | Works |
| LOG-07 | View log detail | M | Open single log | Full payload/metadata shown |
| LOG-08 | Delete single log | M | Delete log | Removed |
| LOG-09 | Purge old logs | M | Purge logs older than 30 days | Old logs removed |
| LOG-10 | Export logs CSV | M | Export | CSV downloads (max 10k rows) |

---

## 29. Wellness Surveys (optional — route exists, sidebar may be disabled)

**URL:** `/admin/wellness`

| TC ID | Title | Priority | Steps | Expected |
|-------|-------|----------|-------|----------|
| WEL-01 | Wellness list loads | L | Open `/admin/wellness` directly | Surveys listed |
| WEL-02 | Create survey | L | Create wellness survey | Saved |
| WEL-03 | View results | L | Open results for survey | Responses shown |
| WEL-04 | Delete survey | L | Delete survey | Removed |

---

## 30. Cross-module regression checklist

Run after any major admin release:

| # | Check | Expected |
|---|-------|----------|
| 1 | Login → Dashboard → Logout | No errors |
| 2 | Create item in each Insights utility module | Success + visible on frontend |
| 3 | Pagination on Blogs, Mentors, Sessions | Page 2 loads with filters preserved |
| 4 | Approve mentor → book session → complete session | End-to-end flow works |
| 5 | Create plan → mentee subscribes → subscription in admin | Subscription visible |
| 6 | Credit wallet → request withdrawal → approve | Balances correct |
| 7 | Create community channel → post message | Message visible |
| 8 | Export (sessions, blogs, call logs, logs) | Files download |
| 9 | Soft delete user → restore | User restored |
| 10 | Inactive content hidden on frontend | Active-only content shown |

---

| Sidebar label | Admin URL | Pagination | Main filters |
|---------------|-----------|------------|--------------|
| Dashboard | `/admin/dashboard` | — | — |
| Mentors | `/admin/mentors` | 20 | search, mentor_status, field |
| Mentee | `/admin/mentees` | 20 | search, status, onboarded, assigned |
| Sessions | `/admin/sessions` | 15 | search, status, date_from/to |
| Wallet | `/admin/wallet` | 20 | dates, type, search |
| Withdrawals | `/admin/withdrawals` | 20 | search, status |
| Call Records | `/admin/call-logs` | 20 | search, status, provider, dates |
| Mentor Approvals | `/admin/mentor-approvals` | 20 | status |
| Curriculum Streams | `/admin/curriculum/catalog` | 12 | — |
| 6-Month Journey | `/admin/curriculum` | 12 | mentee_id |
| Quizzes & MCQs | `/admin/quizzes` | 20 | — |
| Assessments Categories | `/admin/assessments` | 20 | — |
| Assessment Questions | `/admin/assessment-questions` | 20 | search, assessment_id |
| Job Listings | `/admin/jobs` | 15 | search, status, department, job_type |
| Community Channels | `/admin/community` | 18 | — |
| Premium Plans | `/admin/plans` | 20 | — |
| Subscriptions | `/admin/subscriptions` | 20 | q, plan_id, status |
| Blogs | `/admin/blogs` | 20 | search, status |
| White Papers | `/admin/white-papers` | 20 | search, status |
| Case Studies | `/admin/case-studies` | 20 | search, status |
| Testimonials | `/admin/testimonials` | 20 | search, status |
| Podcasts | `/admin/podcasts` | 20 | search, status |
| Videos | `/admin/videos` | 20 | search, status |
| Download Centre | `/admin/download-centres` | 20 | search, status |
| Events & Webinars | `/admin/events-webinars` | 20 | search, status, type |
| App Settings | `/admin/settings` | — | — |
| Logs | `/admin/logs` | 50 | search, module, level, dates |

---
