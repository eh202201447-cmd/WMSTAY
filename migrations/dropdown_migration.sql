-- Dynamic Dropdown Migration

USE wmstay;

-- Create colleges table
CREATE TABLE colleges (
  id INT AUTO_INCREMENT PRIMARY KEY,
  college_name VARCHAR(255) NOT NULL UNIQUE
);

-- Create programs table
CREATE TABLE programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  college_id INT NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
);

-- Add columns to students
ALTER TABLE students ADD COLUMN college_id INT;
ALTER TABLE students ADD COLUMN program_id INT;

-- Insert sample colleges
INSERT INTO colleges (college_name) VALUES
('College of Computing Studies'),
('College of Engineering'),
('College of Science and Mathematics'),
('College of Liberal Arts'),
('College of Business Administration');

-- Insert sample programs
INSERT INTO programs (college_id, program_name) VALUES
(1, 'BS Computer Science'),
(1, 'BS Information Technology'),
(1, 'BS Information Systems'),
(2, 'BS Civil Engineering'),
(2, 'BS Mechanical Engineering'),
(2, 'BS Electrical Engineering'),
(3, 'BS Mathematics'),
(3, 'BS Biology'),
(3, 'BS Chemistry'),
(4, 'BA English'),
(4, 'BA History'),
(4, 'BA Psychology'),
(5, 'BS Business Administration'),
(5, 'BS Accountancy'),
(5, 'BS Marketing');