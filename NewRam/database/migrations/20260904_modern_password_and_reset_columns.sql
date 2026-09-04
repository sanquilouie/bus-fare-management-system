-- Pre-deployment migration for modern password hashes and reset codes.
-- Back up the target database and confirm the useracc table/columns exist before running.

ALTER TABLE `useracc`
    MODIFY COLUMN `password` VARCHAR(255) NOT NULL,
    MODIFY COLUMN `otp` VARCHAR(255) NULL,
    MODIFY COLUMN `otp_expiry` DATETIME NULL;
