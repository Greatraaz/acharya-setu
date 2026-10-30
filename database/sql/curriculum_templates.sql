-- Curriculum templates from CurriculumTemplateSeeder.php
-- Run in phpMyAdmin (utf8mb4). Safe to re-run: existing tpl-* templates are replaced.
-- Prerequisite: education_streams already has is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

UPDATE education_streams SET cloned_from_id = NULL
WHERE cloned_from_id IN (SELECT id FROM (SELECT id FROM education_streams WHERE is_template = 1 AND slug LIKE 'tpl-%') x);

DELETE t FROM curriculum_tasks t
INNER JOIN curriculum_weeks w ON w.id = t.week_id
INNER JOIN curriculum_months m ON m.id = w.month_id
INNER JOIN education_streams s ON s.id = m.stream_id
WHERE s.is_template = 1 AND s.slug LIKE 'tpl-%';

DELETE w FROM curriculum_weeks w
INNER JOIN curriculum_months m ON m.id = w.month_id
INNER JOIN education_streams s ON s.id = m.stream_id
WHERE s.is_template = 1 AND s.slug LIKE 'tpl-%';

DELETE m FROM curriculum_months m
INNER JOIN education_streams s ON s.id = m.stream_id
WHERE s.is_template = 1 AND s.slug LIKE 'tpl-%';

DELETE FROM education_streams WHERE is_template = 1 AND slug LIKE 'tpl-%';

SET FOREIGN_KEY_CHECKS = 1;

-- Engineering & Technology Path (tpl-engineering-technology)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Engineering & Technology Path', 'tpl-engineering-technology', '💻', '#4f46e5', 'Programming, systems, projects, and interviews for engineering mentees.', 1, 1, 'Engineering & Technology', 'Engineering & Technology', '["DSA","System Design","Software Engineering"]', NULL, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Programming foundations', 'Write clean code and solve beginner DSA.', 'Fundamentals', '["Write clean code and solve beginner DSA.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Code Starter', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Language refresh', 'Revise syntax, Git, and a daily coding habit.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Set up GitHub and complete 5 warm-up problems.', 'Revise syntax, Git, and a daily coding habit.', 'reading', 0, 60, 1, 1, 'none', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Problem-solving patterns', 'Arrays, strings, hashing.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Explain 3 patterns the mentee should master this week.', 'Arrays, strings, hashing.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Complexity basics', 'Big-O in interviews.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Solve 12 easy-medium problems and log approaches.', 'Big-O in interviews.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mini coding kata', 'Apply the week in a tiny CLI or script.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Submit a GitHub repo with README.', 'Apply the week in a tiny CLI or script.', 'project', 0, 150, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Data structures & web basics', 'Build DSA confidence and a first web project.', 'Core skills', '["Build DSA confidence and a first web project.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Builder', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Trees & graphs intro', 'Core interview structures.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Implement 4 structures with 2 problems each.', 'Core interview structures.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'SQL & APIs', 'How backend data actually moves.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Design 8 tables and 6 API endpoints on paper.', 'How backend data actually moves.', 'reading', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Frontend or backend slice', 'Pick the mentee’s track.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Ship one CRUD feature with auth.', 'Pick the mentee’s track.', 'project', 0, 180, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Code review week', 'Quality over speed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Review the project against a checklist and refactor.', 'Quality over speed.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'System design & specialisation', 'Learn to talk about scale and pick a stack.', 'Depth', '["Learn to talk about scale and pick a stack.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Designer', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'System design 101', 'Clients, DBs, caches, queues.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Sketch 2 designs: URL shortener and news feed.', 'Clients, DBs, caches, queues.', 'reading', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Stack deep-dive', 'One language/framework to mastery.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Complete a guided module in the chosen stack.', 'One language/framework to mastery.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Design a feature', 'From PRD to components.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a 1-page design for a mentee-chosen product.', 'From PRD to components.', 'project', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock design interview', 'Talk through trade-offs.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Record or notes from a 30-min design conversation.', 'Talk through trade-offs.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Flagship project', 'Ship something that can go on a resume.', 'Build', '["Ship something that can go on a resume.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Shipper', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Scope the project', 'One problem, 4-week plan.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write problem statement, users, and milestones.', 'One problem, 4-week plan.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Build v1', 'Working vertical slice.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Demo a usable v1 with seed data.', 'Working vertical slice.', 'project', 0, 240, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Harden & document', 'Tests, README, deploy.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Deploy and add setup + architecture notes.', 'Tests, README, deploy.', 'task', 0, 150, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Peer / mentor review', 'Feedback loop.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'List 5 changes from review and apply 3.', 'Feedback loop.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Interview readiness', 'DSA + stories + resume.', 'Career', '["DSA + stories + resume.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Resume & LinkedIn', 'One sharp profile.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite resume bullets with impact numbers.', 'One sharp profile.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'DSA sprint', 'Timed practice.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Complete 20 mixed problems this week.', 'Timed practice.', 'task', 0, 180, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Behavioural stories', 'STAR for projects.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write 6 STAR stories from this curriculum.', 'STAR for projects.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock interview', 'Full loop dry-run.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Do one coding + one behavioural mock; note gaps.', 'Full loop dry-run.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Offers & first 90 days', 'Applications, negotiation, joining plan.', 'Launch', '["Applications, negotiation, joining plan.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Career Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Target list', 'Companies and roles.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Shortlist 15 roles that fit the stack.', 'Companies and roles.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Application sprint', 'Volume with quality.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Apply to 10 roles with tailored notes.', 'Volume with quality.', 'task', 0, 120, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Offer / negotiation basics', 'Comp, joining date, questions.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Prepare a negotiation script.', 'Comp, joining date, questions.', 'reading', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'Start strong.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Draft a 30-60-90 plan for the first role.', 'Start strong.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Product Management Path (tpl-product-management)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Product Management Path', 'tpl-product-management', '🧭', '#4338ca', 'Discovery, roadmaps, delivery, and PM interviews.', 1, 1, 'Product Management', 'Product Management', '["Product Sense","Roadmaps","Discovery"]', NULL, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Product foundations', 'What a PM actually owns.', 'Fundamentals', '["What a PM actually owns.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'PM Starter', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Role map', 'Discovery vs delivery vs go-to-market.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write the PM job the mentee wants in one page.', 'Discovery vs delivery vs go-to-market.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'User problems', 'Jobs-to-be-done.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Interview or observe 3 users; log pains.', 'Jobs-to-be-done.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Metrics literacy', 'Activation, retention, revenue.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Define 6 metrics for a known product.', 'Activation, retention, revenue.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Problem brief', 'One sharp problem.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page problem statement.', 'One sharp problem.', 'project', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Discovery', 'Talk to users before building.', 'Research', '["Talk to users before building.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Discoverer', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Research plan', 'Who, what, why.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a 5-interview script.', 'Who, what, why.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Synthesis', 'Patterns not quotes.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Affinity map of 8 insights.', 'Patterns not quotes.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Opportunity sizing', 'Reach × value × effort.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Score 5 opportunities.', 'Reach × value × effort.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'PRD lite', 'Scope a v1.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-pager with non-goals.', 'Scope a v1.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Roadmaps & delivery', 'Sequence work with engineering.', 'Build', '["Sequence work with engineering.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Shipped', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Roadmap story', 'Now / next / later.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Draft a 2-quarter view.', 'Now / next / later.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Spec a slice', 'Acceptance criteria.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'User stories for one epic.', 'Acceptance criteria.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Ceremony design', 'Standup, review, retro.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a team cadence.', 'Standup, review, retro.', 'reading', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Launch checklist', 'Comms, metrics, rollback.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Checklist for a feature launch.', 'Comms, metrics, rollback.', 'project', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Product sense project', 'A case that can go in interviews.', 'Craft', '["A case that can go in interviews.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Product Sense', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick a product', 'Improve or 0→1.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write the prompt and constraints.', 'Improve or 0→1.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Design the solution', 'Flows and trade-offs.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5-slide product sense case.', 'Flows and trade-offs.', 'project', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Prioritise', 'What you would not build.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'RICE or similar with commentary.', 'What you would not build.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Critique', 'Mentor red-team.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Revise from feedback.', 'Mentor red-team.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'PM interviews', 'Product sense + execution + behavioural.', 'Career', '["Product sense + execution + behavioural.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Resume for PMs', 'Impact bullets.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite 6 bullets with metrics.', 'Impact bullets.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Story bank', 'Leadership and conflict.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8 STAR stories.', 'Leadership and conflict.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Case drills', 'Estimate and design.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Log 4 practice cases.', 'Estimate and design.', 'task', 0, 120, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock loop', 'Full PM loop.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Full PM loop.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Land the role', 'Applications and first 90 days.', 'Launch', '["Applications and first 90 days.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Target list', 'Companies and team types.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15 roles with why-this-team.', 'Companies and team types.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Applications', 'Tailored.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8 applications sent.', 'Tailored.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Offer filter', 'Scope vs brand vs pay.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Decision rubric.', 'Scope vs brand vs pay.', 'reading', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'Listen, ship, relationships.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '30-60-90 for a PM seat.', 'Listen, ship, relationships.', 'project', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Design & UX / UI Path (tpl-design-ux-ui)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Design & UX / UI Path', 'tpl-design-ux-ui', '🎨', '#c026d3', 'Research, UI craft, design systems, and a portfolio case.', 1, 1, 'Design & UX / UI', 'Design & UX / UI', '["UX Research","UI","Figma"]', NULL, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Design craft foundations', 'See, sketch, and give critique.', 'Fundamentals', '["See, sketch, and give critique.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Craft', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Visual literacy', 'Hierarchy, type, color.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Redline 3 screens you admire.', 'Hierarchy, type, color.', 'reading', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Figma hygiene', 'Auto-layout, components.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rebuild one screen pixel-close.', 'Auto-layout, components.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Accessibility basics', 'Contrast, tap targets.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Audit 5 issues on a live site.', 'Contrast, tap targets.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Critique notes', 'Say why, not I like.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Written critique of a peer file.', 'Say why, not I like.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'UX research', 'Design from evidence.', 'Research', '["Design from evidence.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Researcher', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Plan a study', 'Goal and method.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Recruit script + 5 questions.', 'Goal and method.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Run sessions', 'Listen more than talk.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from 3 conversations.', 'Listen more than talk.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Journey map', 'Current experience.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'As-is map with pain points.', 'Current experience.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Insight memo', 'So what for design.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page insight brief.', 'So what for design.', 'reflection', 0, 40, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'UI systems', 'Consistent product UI.', 'UI', '["Consistent product UI.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Systems', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Audit a product', 'Patterns and gaps.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Screenshot board of 12 patterns.', 'Patterns and gaps.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Component set', 'Buttons to forms.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mini library in Figma.', 'Buttons to forms.', 'project', 0, 150, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Responsive pass', 'Mobile first.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Adapt 2 screens to small viewport.', 'Mobile first.', 'task', 0, 90, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Handoff', 'Dev-ready.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Specs + redlines for one flow.', 'Dev-ready.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Portfolio case', 'One story with before/after.', 'Build', '["One story with before/after.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Portfolio', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick the case', 'Problem with stakes.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Case outline: context, role, outcome.', 'Problem with stakes.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Design the solution', 'Flows and UI.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Hi-fi for the core flow.', 'Flows and UI.', 'project', 0, 180, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Write the story', 'Process over decoration.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Case study draft.', 'Process over decoration.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mentor critique', 'Tighten the narrative.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Revise 3 pages.', 'Tighten the narrative.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Design interviews', 'Portfolio + whiteboard + culture.', 'Career', '["Portfolio + whiteboard + culture.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Portfolio polish', '3 cases max.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Homepage + one deep case live.', '3 cases max.', 'task', 0, 90, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Whiteboard practice', 'Timeboxed flows.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2 timed exercises logged.', 'Timeboxed flows.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Resume', 'Impact, not tools list.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-page design resume.', 'Impact, not tools list.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock interview', 'Walk a case.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Walk a case.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Applications', 'Studios, product orgs, freelance.', 'Launch', '["Studios, product orgs, freelance.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Target list', 'Product vs agency.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '12 teams and why.', 'Product vs agency.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Outreach', 'Warm > cold.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8 applications or intros.', 'Warm > cold.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Take-home hygiene', 'Timebox and communicate.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Checklist for assignments.', 'Timebox and communicate.', 'reading', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'First design hire habits.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Onboarding questions list.', 'First design hire habits.', 'project', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Finance & Investment Banking Path (tpl-finance-investment-banking)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Finance & Investment Banking Path', 'tpl-finance-investment-banking', '💰', '#b45309', 'Accounting, analysis, markets, modelling, and finance interviews.', 1, 1, 'Finance & Investment Banking', 'Finance & Investment Banking', '["Accounting","Modelling","Markets"]', NULL, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Accounting foundations', 'Statements that actually make sense.', 'Fundamentals', '["Statements that actually make sense.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Accountant', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Three statements', 'P&L, BS, CF links.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Explain how a sale flows through all three.', 'P&L, BS, CF links.', 'reading', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Journal to trial balance', 'Mechanics.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Complete a 20-entry practice set.', 'Mechanics.', 'task', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Ratio starter pack', 'Liquidity, leverage, profitability.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compute ratios for one listed company.', 'Liquidity, leverage, profitability.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Week recap', 'Teach-back.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Record a 5-min explanation of working capital.', 'Teach-back.', 'reflection', 0, 30, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Financial analysis', 'Read a company like an analyst.', 'Analysis', '["Read a company like an analyst.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Analyst', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Annual report tour', 'MD&A and notes.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Summarise 5 red flags or strengths.', 'MD&A and notes.', 'reading', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Excel hygiene', 'Clean models.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Build a 3-statement stub in Excel.', 'Clean models.', 'task', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Peer comparison', 'Relative view.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compare 2 competitors on 8 metrics.', 'Relative view.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Insight memo', 'So what?', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page investment or credit view.', 'So what?', 'reflection', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Markets & instruments', 'How money is priced.', 'Markets', '["How money is priced.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Market Smart', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Time value of money', 'NPV, IRR, compounding.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Solve 10 TVM problems.', 'NPV, IRR, compounding.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Equity vs debt', 'Risk and return.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Explain 4 instruments to a beginner.', 'Risk and return.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Market diary', 'Watch one index / stock.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Log 5 days of moves with a hypothesis.', 'Watch one index / stock.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Case: raise capital', 'Founder vs CFO view.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Recommend a funding mix for a case firm.', 'Founder vs CFO view.', 'project', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Corporate finance project', 'A decision memo with numbers.', 'Build', '["A decision memo with numbers.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Modeller', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Scope a decision', 'Capex, pricing, or working capital.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write the decision question and data needed.', 'Capex, pricing, or working capital.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Simple model', 'Drivers, not decoration.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Build a 3-year model with scenarios.', 'Drivers, not decoration.', 'project', 0, 180, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Sensitivity', 'What actually moves the needle.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Tornado or two-way table + commentary.', 'What actually moves the needle.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Recommendation', 'Decide and defend.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page memo to a mock CFO.', 'Decide and defend.', 'reflection', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Career tracks', 'IB, FP&A, audit, markets, fintech.', 'Career', '["IB, FP&A, audit, markets, fintech.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Career Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Track map', 'Fit vs interest.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Score 4 tracks and pick a primary.', 'Fit vs interest.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Resume for finance', 'Deals, numbers, tools.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite 6 bullets with metrics.', 'Deals, numbers, tools.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Technical Qs', 'Accounting + markets.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Prepare 25 interview answers.', 'Accounting + markets.', 'task', 0, 120, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock case', 'Short case interview.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one case with mentor.', 'Short case interview.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Applications & certifications', 'CFA/CA/jobs as relevant.', 'Launch', '["CFA/CA/jobs as relevant.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Cert plan', 'If needed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '12-week study outline or skip-rationale.', 'If needed.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Target list', 'Firms and roles.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15 roles with why-this-firm notes.', 'Firms and roles.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Outreach', 'Referrals + apps.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8 applications or informational chats.', 'Referrals + apps.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'First seat.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Learning goals for FP&A / audit / markets.', 'First seat.', 'project', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- MBA & Management Consulting Path (tpl-mba-management-consulting)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'MBA & Management Consulting Path', 'tpl-mba-management-consulting', '📊', '#1d4ed8', 'Business fundamentals, cases, leadership, and a strategy capstone.', 1, 1, 'MBA & Management Consulting', 'MBA & Management Consulting', '["Case Interviews","Strategy","Leadership"]', NULL, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Business fundamentals', 'How a company actually makes money.', 'Fundamentals', '["How a company actually makes money.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Business Basics', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Value chain', 'From customer to P&L.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Map a company the mentee knows.', 'From customer to P&L.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Numbers for managers', 'Margins, CAC, unit economics.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compute 8 basic metrics from a case.', 'Margins, CAC, unit economics.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Functions tour', 'Sales, ops, finance, people, product.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Interview or research one function lead role.', 'Sales, ops, finance, people, product.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Problem framing', 'Define before solving.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a problem statement using SCQA.', 'Define before solving.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Marketing & customers', 'Who buys and why.', 'Customers', '["Who buys and why.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Marketer', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Segmentation', 'Who is the user.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-page persona + JTBD.', 'Who is the user.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Channel basics', 'Where demand comes from.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compare 3 channels with costs.', 'Where demand comes from.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Mini campaign', 'Message and offer.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Draft a landing-page outline + 5 ads.', 'Message and offer.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Review metrics', 'What you would measure.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Dashboard sketch of 6 KPIs.', 'What you would measure.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Operations & analytics', 'Process and data.', 'Systems', '["Process and data.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Operator', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Process map', 'Bottlenecks.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'As-is map of one process with wait times.', 'Bottlenecks.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Spreadsheet / BI', 'Analyst hygiene.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Clean a dataset and 4 charts.', 'Analyst hygiene.', 'task', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Ops case', 'Capacity or quality.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Recommend 3 changes with impact.', 'Capacity or quality.', 'project', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Automation ideas', 'Where tools help.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'List 5 automations ranked by effort/impact.', 'Where tools help.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Leadership & people', 'Influence without theatre.', 'People', '["Influence without theatre.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Lead', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Feedback practice', 'SBI / radical candor lite.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write 3 feedback scripts.', 'SBI / radical candor lite.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Stakeholder map', 'Who matters.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Power/interest grid for a project.', 'Who matters.', 'task', 0, 40, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Team ritual design', 'Meetings that work.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Design a weekly operating cadence.', 'Meetings that work.', 'project', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Self review', 'Strengths under stress.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '360-style self assessment.', 'Strengths under stress.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Strategy capstone', 'A full case for the resume.', 'Strategy', '["A full case for the resume.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Strategist', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Choose a company', 'Public or known SME.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Issue tree of the core problem.', 'Public or known SME.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Analysis pack', 'Market, competitors, internals.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8-slide fact pack.', 'Market, competitors, internals.', 'project', 0, 180, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Recommendation', 'So what, now what.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '3 options, 1 recommendation, risks.', 'So what, now what.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Storyline', 'Present it.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rehearse 8-minute narrative.', 'Present it.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Placement & first role', 'Consulting, product, ops, general management.', 'Launch', '["Consulting, product, ops, general management.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Track choice', 'Fit.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Pick a primary track and 10 target firms.', 'Fit.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Resume & stories', 'Impact bullets.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite resume + 6 PEI/STAR stories.', 'Impact bullets.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Case / guesstimate', 'As needed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5 cases logged with structures.', 'As needed.', 'task', 0, 150, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'First manager role.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Learning, relationships, quick wins.', 'First manager role.', 'project', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Marketing & Growth Path (tpl-marketing-growth)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Marketing & Growth Path', 'tpl-marketing-growth', '📈', '#db2777', 'Positioning, channels, experiments, and a growth case.', 1, 1, 'Marketing & Growth', 'Marketing & Growth', '["Growth","Brand","Performance"]', NULL, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Positioning', 'Who it is for and why they care.', 'Fundamentals', '["Who it is for and why they care.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Marketer', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Category & offer', 'What you are not.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Positioning canvas for one brand.', 'What you are not.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Audience', 'Jobs and objections.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One persona + JTBD.', 'Jobs and objections.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Message house', 'Headline to proof.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5 value props with evidence.', 'Headline to proof.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Competitive scan', 'How others talk.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Table of 4 competitors.', 'How others talk.', 'project', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Channels', 'Where demand actually comes from.', 'Growth', '["Where demand actually comes from.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Channels', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Channel map', 'Paid, owned, earned.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Score 6 channels for the offer.', 'Paid, owned, earned.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Content engine', 'One idea, many cuts.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '4-week content calendar.', 'One idea, many cuts.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Performance basics', 'CAC, CTR, CVR.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Define a tracking plan.', 'CAC, CTR, CVR.', 'reading', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Creative test', 'Copy and creative.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '3 ad/landing variants.', 'Copy and creative.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Experiments', 'Learn faster than you spend.', 'Build', '["Learn faster than you spend.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Growth', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Hypothesis', 'If we X then Y because Z.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write 5 test cards.', 'If we X then Y because Z.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Run a test', 'Small and clean.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One live or paper experiment.', 'Small and clean.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Read results', 'Guardrail metrics.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Decision: scale, iterate, kill.', 'Guardrail metrics.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Retro', 'What you would change.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page learning log.', 'What you would change.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Growth case', 'A story with numbers.', 'Craft', '["A story with numbers.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Case', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick a product', 'Stalled metric.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Problem + north-star metric.', 'Stalled metric.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Growth model', 'Loops not hacks.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Sketch the loop.', 'Loops not hacks.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, '90-day plan', 'Bets and owners.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Roadmap of 6 bets.', 'Bets and owners.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Narrative', 'Tell it in 6 slides.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Case deck.', 'Tell it in 6 slides.', 'reflection', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Career', 'Brand, performance, or growth.', 'Career', '["Brand, performance, or growth.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Track choice', 'Fit.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Primary track + 10 firms.', 'Fit.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Resume', 'Campaigns with numbers.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite 6 bullets.', 'Campaigns with numbers.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Case drills', 'Diagnose a funnel.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2 practice cases logged.', 'Diagnose a funnel.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock', 'Portfolio walkthrough.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Portfolio walkthrough.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Launch', 'Applications and first 90 days.', 'Launch', '["Applications and first 90 days.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pipeline', 'Roles.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '12 tailored applications.', 'Roles.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Work samples', 'One live artefact.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Folder of 3 pieces.', 'One live artefact.', 'project', 0, 60, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Offer filter', 'Learning vs brand.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rubric.', 'Learning vs brand.', 'reading', 0, 25, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'Audit, quick win, system.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Plan for a growth seat.', 'Audit, quick win, system.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Data Science & AI / ML Path (tpl-data-science-ai-ml)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Data Science & AI / ML Path', 'tpl-data-science-ai-ml', '🤖', '#0f766e', 'Data wrangling, modelling, ML projects, and interview prep.', 1, 1, 'Data Science & AI / ML', 'Data Science & AI / ML', '["Python","ML","SQL"]', NULL, 7, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Data foundations', 'SQL, Python, and clean tables.', 'Fundamentals', '["SQL, Python, and clean tables.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Analyst', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'SQL daily', 'Joins and aggregations.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Finish 20 practice queries.', 'Joins and aggregations.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Python / notebooks', 'Pandas hygiene.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Clean one messy dataset.', 'Pandas hygiene.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Stats intuition', 'Distributions, bias.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Explain 5 pitfalls in your own words.', 'Distributions, bias.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mini EDA', 'Ask a question.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notebook with 4 charts + conclusion.', 'Ask a question.', 'project', 0, 90, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Analytics in product', 'Metrics that change decisions.', 'Applied', '["Metrics that change decisions.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Decision', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Metric design', 'North star vs vanity.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Define a metric tree.', 'North star vs vanity.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Dashboard', 'One audience.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '4-chart dashboard.', 'One audience.', 'project', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Experiment basics', 'A/B literacy.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Critique one experiment write-up.', 'A/B literacy.', 'reading', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Insight memo', 'So what.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page recommendation.', 'So what.', 'reflection', 0, 40, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'ML starter', 'A model with a real question.', 'ML', '["A model with a real question.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Modeller', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Problem framing', 'Prediction vs explanation.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write the ML brief.', 'Prediction vs explanation.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Baseline model', 'Simple first.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Train and report metrics.', 'Simple first.', 'project', 0, 180, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Error analysis', 'Where it fails.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Slice metrics + next steps.', 'Where it fails.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Ethics check', 'Leakage, bias, consent.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-page risk note.', 'Leakage, bias, consent.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Flagship project', 'End-to-end and documented.', 'Build', '["End-to-end and documented.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Shipper', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Scope', 'Data + user of the insight.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Project one-pager.', 'Data + user of the insight.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Build', 'Reproducible.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Repo with README and notebook/app.', 'Reproducible.', 'project', 0, 240, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Communicate', 'Non-technical audience.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5-slide story.', 'Non-technical audience.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Review', 'Mentor critique.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Apply 3 changes.', 'Mentor critique.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Interviews', 'SQL + case + ML depth as needed.', 'Career', '["SQL + case + ML depth as needed.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Resume', 'Projects with impact.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite bullets.', 'Projects with impact.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'SQL sprint', 'Timed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15 interview-style queries.', 'Timed.', 'task', 0, 120, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Case', 'Business + data.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2 cases logged.', 'Business + data.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock', 'Take-home or live.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Take-home or live.', 'reflection', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Land the role', 'Analyst, DS, or ML-lite.', 'Launch', '["Analyst, DS, or ML-lite.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Targets', 'Team type.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15 roles tagged.', 'Team type.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Apply', 'Portfolio first.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '10 applications.', 'Portfolio first.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Take-homes', 'Timebox.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Checklist.', 'Timebox.', 'reading', 0, 25, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'Data stack onboarding.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Questions to ask week 1.', 'Data stack onboarding.', 'project', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Law & Legal Path (tpl-law-legal)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Law & Legal Path', 'tpl-law-legal', '⚖️', '#334155', 'Legal research, drafting, chambers/firm prep, and ethics.', 1, 1, 'Law & Legal', 'Law & Legal', '["Research","Drafting","Litigation"]', NULL, 8, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Legal foundations', 'How to read and research.', 'Fundamentals', '["How to read and research.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Foundations', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Map the paths', 'Litigation, corporate, policy, in-house.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page path comparison.', 'Litigation, corporate, policy, in-house.', 'reading', 0, 50, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Research method', 'Statutes, cases, commentary.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Research note on one live issue.', 'Statutes, cases, commentary.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Ethics', 'Conflict, confidentiality.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Summarise 6 duties with examples.', 'Conflict, confidentiality.', 'reading', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Citation drill', 'Clean footnotes.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '10 correctly cited authorities.', 'Clean footnotes.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Drafting', 'Clear writing is the craft.', 'Practice', '["Clear writing is the craft.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Drafter', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Memo structure', 'Issue, rule, application.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page office memo.', 'Issue, rule, application.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Contracts skim', 'Risk allocation.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Markup 8 clauses on a sample.', 'Risk allocation.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Client email', 'Plain language.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite a dense update.', 'Plain language.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Feedback loop', 'Mentor redlines.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Revise the memo.', 'Mentor redlines.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Applied practice', 'How work actually moves.', 'Chambers', '["How work actually moves.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Practitioner', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Matter map', 'From brief to filing.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Process map of one matter type.', 'From brief to filing.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Oral skills', 'Structure an argument.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5-minute submission outline.', 'Structure an argument.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Due diligence lite', 'Checklists.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'DD list for a simple deal or case.', 'Checklists.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Court / deal diary', 'Observe.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from 1 hearing or recorded argument.', 'Observe.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Specialisation file', 'One area to be known for.', 'Depth', '["One area to be known for.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Specialist', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick a desk', 'IP, disputes, corp, crime, etc.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Why this desk + 5 readings.', 'IP, disputes, corp, crime, etc.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Research brief', 'Live controversy.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '4-page brief.', 'Live controversy.', 'project', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Draft artefact', 'Petition, opinion, or clause set.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One artefact.', 'Petition, opinion, or clause set.', 'task', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Critique', 'Mentor comments.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Redline round.', 'Mentor comments.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Recruitment', 'Firms, judiciary, in-house, LLM.', 'Career', '["Firms, judiciary, in-house, LLM.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'CV & internships', 'Matters not marks only.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite CV.', 'Matters not marks only.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Interview Qs', 'Law + commercial awareness.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '20 answers.', 'Law + commercial awareness.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Writing test', 'Timed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One timed memo.', 'Timed.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock', 'Partner-style.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Partner-style.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Join-up', 'Applications and first seat.', 'Launch', '["Applications and first seat.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Applications', 'Chambers / firms / exams.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '8 submissions.', 'Chambers / firms / exams.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Portfolio', 'Memos and drafts.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Folder of 3 anonymised pieces.', 'Memos and drafts.', 'project', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Exam or LLM plan', 'If relevant.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '12-week outline or skip note.', 'If relevant.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'How juniors add value.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Learning checklist.', 'How juniors add value.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Medicine & Healthcare Path (tpl-medicine-healthcare)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Medicine & Healthcare Path', 'tpl-medicine-healthcare', '🩺', '#be123c', 'Clinical foundations, exams, research, and specialty choice.', 1, 1, 'Medicine & Healthcare', 'Medicine & Healthcare', '["Clinical","NEET PG","Healthcare"]', NULL, 9, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Clinical foundations', 'Strengthen the base you will use daily.', 'Fundamentals', '["Strengthen the base you will use daily.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Clinician', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Weak-topic map', 'From college/exams.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '10 must-know topics for the mentee’s stage.', 'From college/exams.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Case method', 'History, exam, differentials.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write 4 structured cases.', 'History, exam, differentials.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Guidelines', 'Standard of care.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Summarise 3 current guidelines.', 'Standard of care.', 'reading', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Ethics & consent', 'Communication.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Script for a difficult conversation.', 'Communication.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Exam or skills block', 'NEET PG, INI, or skills log as relevant.', 'Practice', '["NEET PG, INI, or skills log as relevant.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Prepared', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Study system', 'Spaced + questions.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Weekly timetable.', 'Spaced + questions.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Q bank sprint', 'Error log.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Timed set + 20 error notes.', 'Error log.', 'task', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'OSCE / practical', 'If applicable.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Checklist for 5 stations.', 'If applicable.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Recovery', 'Burnout check.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'What to drop this month.', 'Burnout check.', 'reflection', 0, 25, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Research & evidence', 'Read and write like a clinician.', 'Evidence', '["Read and write like a clinician.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Scholar', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Paper club', 'Methods first.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Critique one paper.', 'Methods first.', 'reading', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Question', 'PICO.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a research question.', 'PICO.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Mini review', 'Narrow scope.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page evidence table.', 'Narrow scope.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Stats sense', 'p-values and CI.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Interpret 5 results sections.', 'p-values and CI.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Specialty exploration', 'Choose with eyes open.', 'Depth', '["Choose with eyes open.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Specialty', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Role reality', 'Lifestyle and training.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compare 3 specialties.', 'Lifestyle and training.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Shadow notes', 'Or recorded grand rounds.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One observation log.', 'Or recorded grand rounds.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Skills unique to the field', 'Procedures or clinics.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Learning list.', 'Procedures or clinics.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Decision memo', 'Tentative choice.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page why.', 'Tentative choice.', 'project', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Applications', 'Residency, jobs, or overseas.', 'Career', '["Residency, jobs, or overseas.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'CV & SOP', 'Clinical narrative.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Draft SOP + CV.', 'Clinical narrative.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Interview stories', 'Ethics and cases.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '10 answers.', 'Ethics and cases.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Mock', 'Panel.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from one mock.', 'Panel.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Backup plan', 'Plan B year.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Written contingency.', 'Plan B year.', 'task', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'First posting', 'Survive and learn in month 1.', 'Launch', '["Survive and learn in month 1.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Onboarding list', 'Protocols, people, kit.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Week-1 checklist.', 'Protocols, people, kit.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Safety', 'Escalation.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'When to call for help — one pager.', 'Escalation.', 'reading', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Logbook', 'Procedures / cases.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Template started.', 'Procedures / cases.', 'project', 0, 40, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day learning', 'Topics to cover.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Personal curriculum.', 'Topics to cover.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Arts & Humanities Path (tpl-arts-humanities)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Arts & Humanities Path', 'tpl-arts-humanities', '🧠', '#7c3aed', 'Research, writing, applied projects, and career options in the humanities.', 1, 1, 'Arts & Humanities', 'Arts & Humanities', '["Writing","Research","Portfolio"]', NULL, 10, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Foundations of psychology', 'Core schools and ethical practice.', 'Fundamentals', '["Core schools and ethical practice.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Foundations', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Map the field', 'Clinical, org, counselling, research.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page map of paths and typical work.', 'Clinical, org, counselling, research.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Ethics first', 'Consent, confidentiality, competence.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Summarise 8 ethical principles with examples.', 'Consent, confidentiality, competence.', 'reading', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Concept deck', 'Must-know theories.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Flashcards for 15 core concepts.', 'Must-know theories.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Reflective journal', 'Why this field.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Write a personal purpose note.', 'Why this field.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Research methods', 'Read and run simple studies.', 'Methods', '["Read and run simple studies.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Researcher', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Study design', 'Qual vs quant.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Critique one paper’s method.', 'Qual vs quant.', 'reading', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Stats literacy', 'What numbers mean.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Interpret 5 graphs / tables from papers.', 'What numbers mean.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Mini survey', 'Practice.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Design a 10-item survey and collect 8 responses.', 'Practice.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Write-up', 'IMRAD sketch.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page results + limits.', 'IMRAD sketch.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Applied skills', 'Listening and formulation.', 'Practice', '["Listening and formulation.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Practitioner', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Helping conversations', 'Listening skills.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Practice 2 recorded role-plays (peer).', 'Listening skills.', 'task', 0, 90, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Case formulation', 'Not diagnosis theatre.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Formulate 1 anonymised case from a vignette.', 'Not diagnosis theatre.', 'task', 0, 75, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Org or education track', 'If not clinical.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Design a 45-min workshop outline.', 'If not clinical.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Supervision notes', 'Learning loop.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'What went well / what to change.', 'Learning loop.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Specialisation project', 'One niche the mentee may work in.', 'Depth', '["One niche the mentee may work in.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Specialist', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick a niche', 'Child, workplace, health, etc.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Why this niche + 5 readings.', 'Child, workplace, health, etc.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Literature brief', 'What is known.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '4-page annotated brief.', 'What is known.', 'reading', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Intervention sketch', 'Ethical, scoped.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Session plan or program outline.', 'Ethical, scoped.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Feedback round', 'Mentor critique.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Revise from comments.', 'Mentor critique.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Career & credentials', 'Masters, RCI, HR, research.', 'Career', '["Masters, RCI, HR, research.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Career Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Path options', 'India-specific reality.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Compare 3 realistic next steps.', 'India-specific reality.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'CV for psychology', 'Clinicals / projects / research.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite CV and a 200-word SOP draft.', 'Clinicals / projects / research.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Interview stories', 'Ethics + cases.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Prepare 8 answers.', 'Ethics + cases.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock interview', 'Panel style.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes and improvements.', 'Panel style.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Portfolio & next step', 'Applications or supervised practice plan.', 'Launch', '["Applications or supervised practice plan.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Portfolio pack', 'Projects + ethics statement.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Folder with 3 artefacts.', 'Projects + ethics statement.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Applications', 'Courses or roles.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Submit 5 applications.', 'Courses or roles.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Supervision plan', 'If practising-adjacent.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Who, how often, on what.', 'If practising-adjacent.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day learning', 'Stay current.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Reading and skill list.', 'Stay current.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Government & Public Policy Path (tpl-government-public-policy)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Government & Public Policy Path', 'tpl-government-public-policy', '🏛️', '#1e3a8a', 'Polity, policy memos, optional/exam prep, and public-sector careers.', 1, 1, 'Government & Public Policy', 'Government & Public Policy', '["Policy","UPSC","Governance"]', NULL, 11, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'State & society', 'How policy is actually made.', 'Fundamentals', '["How policy is actually made.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Citizen', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Institutions map', 'Union, state, local.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page map of actors for one issue.', 'Union, state, local.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Read a budget or bill', 'Follow the money.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Summary of 5 allocations or clauses.', 'Follow the money.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Current affairs system', 'Notes not hoarding.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Weekly brief template used once.', 'Notes not hoarding.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Ethics of public power', 'Discretion.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Case reflection.', 'Discretion.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Policy analysis', 'Define the problem before the scheme.', 'Analysis', '["Define the problem before the scheme.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Analyst', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Problem tree', 'Causes vs symptoms.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Tree for a live issue.', 'Causes vs symptoms.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Options memo', '3 options, 1 rec.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page memo.', '3 options, 1 rec.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Stakeholder map', 'Winners and losers.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Power/interest grid.', 'Winners and losers.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Data for policy', 'What is measurable.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Indicator list.', 'What is measurable.', 'reading', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Exam or fellowship track', 'UPSC, state PSC, or policy schools.', 'Prep', '["UPSC, state PSC, or policy schools.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Prepared', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Path choice', 'Service vs research vs NGOs.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Decision note.', 'Service vs research vs NGOs.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'GS / optional plan', 'If exam.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '12-week outline or skip.', 'If exam.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Answer writing', 'Structure.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '4 timed answers.', 'Structure.', 'task', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Essay / SOP', 'Voice.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One long-form piece.', 'Voice.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Field file', 'One sector to know cold.', 'Depth', '["One sector to know cold.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Specialist', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Pick a sector', 'Health, ed, climate, cities.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Reading list of 8.', 'Health, ed, climate, cities.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Sector brief', 'Problems and instruments.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '6-page brief.', 'Problems and instruments.', 'project', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Compare two states/countries', 'What travelled.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page comparison.', 'What travelled.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mentor critique', 'Sharpen recs.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Revise.', 'Sharpen recs.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Applications', 'Services, think tanks, CSR, multilaterals.', 'Career', '["Services, think tanks, CSR, multilaterals.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'CV', 'Projects and internships.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Rewrite.', 'Projects and internships.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Interview', 'Opinion with evidence.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15 Q&A.', 'Opinion with evidence.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Writing test', 'Timed memo.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One test.', 'Timed memo.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock', 'Panel.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes.', 'Panel.', 'reflection', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Public career launch', 'First role in the ecosystem.', 'Launch', '["First role in the ecosystem.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Target list', '10 organisations.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Why each.', '10 organisations.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Applications', '8 submissions.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Sent.', '8 submissions.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Network', 'Informational chats.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '5 outreaches.', 'Informational chats.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, '90-day plan', 'Listen, map, one memo.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Plan.', 'Listen, map, one memo.', 'project', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Education & Academia Path (tpl-education-academia)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Education & Academia Path', 'tpl-education-academia', '🎓', '#0369a1', 'Teaching practice, research methods, and academic/edtech careers.', 1, 1, 'Education & Academia', 'Education & Academia', '["Teaching","Research","Pedagogy"]', NULL, 12, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Learning science', 'How people actually learn.', 'Fundamentals', '["How people actually learn.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Educator', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Core ideas', 'Retrieval, spacing, feedback.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes on 5 principles.', 'Retrieval, spacing, feedback.', 'reading', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Observe a class', 'Or a recorded lesson.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Observation log.', 'Or a recorded lesson.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Learner profile', 'One student type.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Persona + barriers.', 'One student type.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Teaching stance', 'Why you teach.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Personal note.', 'Why you teach.', 'reflection', 0, 25, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'Lesson design', 'Plan, teach, adjust.', 'Practice', '["Plan, teach, adjust.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Designer', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Objective & assessment', 'Begin with the end.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One lesson plan.', 'Begin with the end.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Teach a slice', 'Peer or recording.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '15-min session.', 'Peer or recording.', 'project', 0, 90, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Feedback', 'From learners or mentor.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'What to change.', 'From learners or mentor.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Materials', 'Worksheet or slides.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One artefact.', 'Worksheet or slides.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Assessment & inclusion', 'Fair measures and access.', 'Inclusion', '["Fair measures and access.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Inclusive', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Design a quiz', 'Valid not tricky.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '10 items + key.', 'Valid not tricky.', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Formative checks', 'During learning.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '3 in-class techniques.', 'During learning.', 'reading', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Inclusion pass', 'Language, disability, device.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Audit of one lesson.', 'Language, disability, device.', 'task', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Report to a parent / manager', 'Plain language.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One update.', 'Plain language.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Ed project', 'Classroom, edtech, or research.', 'Build', '["Classroom, edtech, or research.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Builder', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Brief', 'Problem in learning.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-pager.', 'Problem in learning.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Prototype', 'Module, club, or tool.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'v1 delivered.', 'Module, club, or tool.', 'project', 0, 150, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Evidence', 'Did it help?', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Small evaluation.', 'Did it help?', 'task', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Case write-up', 'Portfolio.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '2-page case.', 'Portfolio.', 'reflection', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Career tracks', 'Schools, HE, edtech, research.', 'Career', '["Schools, HE, edtech, research.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Interview Ready', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Path map', 'Fit.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Score 3 tracks.', 'Fit.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'CV & teaching statement', 'Evidence of impact.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Two documents.', 'Evidence of impact.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Demo lesson', 'If hiring asks.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Plan + slides.', 'If hiring asks.', 'project', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Mock', 'Panel or demo.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes.', 'Panel or demo.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'First term', 'Survive week 1 and set a system.', 'Launch', '["Survive week 1 and set a system.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Applications', '8 roles or programmes.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Sent.', '8 roles or programmes.', 'task', 0, 90, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Classroom systems', 'Routines.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Week-1 playbook.', 'Routines.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, '90-day learning', 'Curriculum + classroom mgmt.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Plan.', 'Curriculum + classroom mgmt.', 'project', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Support net', 'Mentors and peers.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Who you will ask.', 'Mentors and peers.', 'reflection', 0, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

-- Entrepreneurship & Startups Path (tpl-entrepreneurship-startups)
INSERT INTO education_streams (mentee_id, mentor_id, name, slug, icon, color, description, is_active, is_template, expertise_stream, expertise_field, expertise_tags, cloned_from_id, sort_order, created_at, updated_at) VALUES (
NULL, NULL, 'Entrepreneurship & Startups Path', 'tpl-entrepreneurship-startups', '🚀', '#be185d', 'Problem, MVP, customers, and fundraising basics.', 1, 1, 'Entrepreneurship & Startups', 'Entrepreneurship & Startups', '["Founder","MVP","Fundraising"]', NULL, 13, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @stream_id = LAST_INSERT_ID();

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 1, 'Founder thesis', 'A problem worth a company.', 'Foundations', '["A problem worth a company.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Founder', 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Why this', 'Motivation vs market.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '1-page founder thesis.', 'Motivation vs market.', 'reflection', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Problem interviews', 'Before the pitch.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Notes from 5 conversations.', 'Before the pitch.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Market sketch', 'Who pays.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'TAM/SAM note without fake precision.', 'Who pays.', 'task', 0, 60, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Risks', 'Why it dies.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Top 5 risks.', 'Why it dies.', 'reading', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 2, 'MVP', 'Ugly but real.', 'Build', '["Ugly but real.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Builder', 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Scope v1', 'One job.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'MVP brief.', 'One job.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Ship', 'No-code or code.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Working demo.', 'No-code or code.', 'project', 0, 240, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Measure', 'One activation metric.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Tracking defined.', 'One activation metric.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Learn', 'What users did.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Learning log.', 'What users did.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 3, 'Customers', 'Someone pays or commits.', 'Demand', '["Someone pays or commits.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Sales', 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Offer', 'Price and promise.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-pager offer.', 'Price and promise.', 'task', 0, 45, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Pipeline', '10 conversations.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'CRM-style log.', '10 conversations.', 'task', 0, 90, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'First revenue or LOI', 'Even tiny.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Evidence of demand.', 'Even tiny.', 'project', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Objection map', 'What they said no to.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Playbook v1.', 'What they said no to.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 4, 'Team & ops', 'How work will happen.', 'Company', '["How work will happen.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Operator', 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Roles', 'What you will not do.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Founder split.', 'What you will not do.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Legal basics', 'Entity, IP, equity (overview).', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Question list for a CA/lawyer.', 'Entity, IP, equity (overview).', 'reading', 0, 45, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Runway', 'Simple model.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '6-month cash sketch.', 'Simple model.', 'project', 0, 75, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Cadence', 'Weekly operating rhythm.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'One-page OS.', 'Weekly operating rhythm.', 'task', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 5, 'Story & raise (optional)', 'Pitch only if it helps.', 'Narrative', '["Pitch only if it helps.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Pitch', 1, 5, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'Narrative', 'Problem, why now, why you.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Deck outline.', 'Problem, why now, why you.', 'task', 0, 60, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Deck or update', '10 slides or a memo.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'v1 artefact.', '10 slides or a memo.', 'project', 0, 120, 1, 1, 'file', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Practice', 'Timeboxed.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Recorded 5-min pitch.', 'Timeboxed.', 'task', 0, 40, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Decision', 'Raise, bootstrap, or pause.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Written choice.', 'Raise, bootstrap, or pause.', 'reflection', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_months (stream_id, mentee_id, month_number, title, description, theme, learning_outcomes, milestone_badge, is_active, sort_order, created_at, updated_at) VALUES (
@stream_id, NULL, 6, 'Next 90 days', 'Focus over theatre.', 'Launch', '["Focus over theatre.","Leave with artefacts the mentee can show (notes, repo, memo, or slides)."]', 'Launch', 1, 6, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @month_id = LAST_INSERT_ID();

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 1, 'OKRs', 'One product, one channel.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 1, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, '90-day OKRs.', 'One product, one channel.', 'task', 0, 40, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 2, 'Hiring or help', 'First support.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 2, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Role scorecard or no-hire note.', 'First support.', 'task', 0, 30, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 3, 'Mentor board', 'Who you will use.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 3, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Ask list.', 'Who you will use.', 'reflection', 0, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

INSERT INTO curriculum_weeks (month_id, mentee_id, week_number, title, focus, mentor_guide, video_url, is_active, sort_order, created_at, updated_at) VALUES (
@month_id, NULL, 4, 'Public artefact', 'Update or landing page.', 'Use this as a starting point. Swap tools, readings, and difficulty to match the mentee.', NULL, 1, 4, '2026-10-30 10:34:34', '2026-10-30 10:34:34');
SET @week_id = LAST_INSERT_ID();

INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Shipped.', 'Update or landing page.', 'project', 0, 60, 1, 1, 'link', '2026-10-30 10:34:34', '2026-10-30 10:34:34');
INSERT INTO curriculum_tasks (week_id, mentee_id, title, description, type, order_index, estimated_minutes, is_required, is_active, submission_type, created_at, updated_at) VALUES (
@week_id, NULL, 'Mentor check-in', 'Confirm progress, unblock, and adjust next week if needed.', 'reflection', 1, 20, 1, 1, 'text', '2026-10-30 10:34:34', '2026-10-30 10:34:34');

