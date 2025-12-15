-- Migration for Automated Room Allocation

USE wmstay;

-- Add columns to rooms
ALTER TABLE rooms ADD COLUMN gender_allowed VARCHAR(10) DEFAULT 'Any';
ALTER TABLE rooms ADD COLUMN room_group VARCHAR(100);
ALTER TABLE rooms ADD COLUMN capacity INT DEFAULT 1;
ALTER TABLE rooms ADD COLUMN occupancy INT DEFAULT 0;

-- Add columns to students
ALTER TABLE students ADD COLUMN course VARCHAR(100);
ALTER TABLE students ADD COLUMN year_level INT;
ALTER TABLE students ADD COLUMN is_scholar BOOLEAN DEFAULT FALSE;
ALTER TABLE students ADD COLUMN distance_km DECIMAL(5,2);

-- Modify bookings
ALTER TABLE bookings MODIFY COLUMN room_id INT NULL;
ALTER TABLE bookings ADD COLUMN priority_score DECIMAL(5,2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN semester_id INT;
ALTER TABLE bookings ADD COLUMN allocation_reason TEXT;

-- Create semesters table
CREATE TABLE semesters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  start_date DATE,
  end_date DATE
);

-- Insert sample semester
INSERT INTO semesters (name, start_date, end_date) VALUES ('2025-1', '2025-01-01', '2025-06-30');