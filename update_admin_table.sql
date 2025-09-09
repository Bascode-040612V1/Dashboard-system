-- Add RFID column to admin table and update with secure data
-- Run this SQL to update your database

-- Add RFID column to admins table
ALTER TABLE `admins` ADD COLUMN `rfid` VARCHAR(50) UNIQUE AFTER `username`;

-- Update existing admin with proper hashed password and RFID
UPDATE `admins` SET 
    `rfid` = '3870770196',
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `username` = 'ajJ';

-- The password above is a hash for 'admin123' - change as needed
-- You can generate new hashes in PHP using: password_hash('your_password', PASSWORD_DEFAULT)

-- Add a second admin if needed
UPDATE `admins` SET 
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE `username` = 'Guard';