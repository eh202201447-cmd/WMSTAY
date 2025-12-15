-- Maintenance Request Workflow Migration

USE wmstay;

-- Add columns to maintenance_reports for ticketing workflow
ALTER TABLE maintenance_reports ADD COLUMN assigned_staff_id INT NULL;
ALTER TABLE maintenance_reports ADD COLUMN started_at TIMESTAMP NULL;
ALTER TABLE maintenance_reports ADD COLUMN completed_at TIMESTAMP NULL;
ALTER TABLE maintenance_reports ADD COLUMN resolution_notes TEXT;

-- Add foreign key for assigned_staff_id
ALTER TABLE maintenance_reports ADD FOREIGN KEY (assigned_staff_id) REFERENCES admins(id) ON DELETE SET NULL;