-- Allow users.role = expert (session host who takes notes and sets a charge).
-- Run on live if artisan migrate cannot be used.

ALTER TABLE `users`
    MODIFY COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'mentee';

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_31_110000_add_expert_role_to_users', IFNULL(MAX(`batch`), 0) + 1
FROM (SELECT `batch` FROM `migrations`) AS `m`
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_31_110000_add_expert_role_to_users'
);
