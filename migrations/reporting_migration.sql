-- Enhanced Reporting Module Migration

USE wmstay;

-- Add building to rooms
ALTER TABLE rooms ADD COLUMN building VARCHAR(100) DEFAULT 'Main Building';

-- Add semester_id to payments
ALTER TABLE payments ADD COLUMN semester_id INT;

-- Add resolved_at to maintenance_reports
ALTER TABLE maintenance_reports ADD COLUMN resolved_at TIMESTAMP NULL;

-- Add due_date to payments
ALTER TABLE payments ADD COLUMN due_date DATE;