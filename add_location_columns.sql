-- Add latitude and longitude columns to users table if they don't exist
-- Run this SQL script to add location columns for storing GPS coordinates

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) NULL AFTER address,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) NULL AFTER latitude,
ADD COLUMN IF NOT EXISTS pincode VARCHAR(10) NULL AFTER address,
ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL AFTER pincode,
ADD COLUMN IF NOT EXISTS state VARCHAR(100) NULL AFTER city;

-- Note: IF NOT EXISTS syntax may not work in all MySQL versions
-- If you get an error, you can check manually:
-- DESCRIBE users;
-- And only add columns that don't exist

