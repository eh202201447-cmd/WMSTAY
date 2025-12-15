-- RBAC Migration

USE wmstay;

-- Add role columns
ALTER TABLE admins ADD COLUMN role VARCHAR(20) DEFAULT 'admin';
ALTER TABLE students ADD COLUMN role VARCHAR(20) DEFAULT 'student';

-- Insert sample staff account (password: staff123)
INSERT INTO admins (username, password_hash, name, email, role) VALUES
('staff', '$2y$10$examplehashforstaff', 'Dormitory Staff', 'staff@wmstay.local', 'staff');