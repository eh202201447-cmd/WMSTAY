CREATE DATABASE IF NOT EXISTS wmstay;
USE wmstay;

-- Admins
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(20) UNIQUE NOT NULL,     -- e.g. 2022-01447
  email VARCHAR(100) UNIQUE NOT NULL,             -- school email
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  department VARCHAR(150) NOT NULL,
  program VARCHAR(200) NOT NULL,
  gender VARCHAR(50),
  contact VARCHAR(50),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(50) NOT NULL,
  room_type VARCHAR(50) NOT NULL,                 -- Bed Spacer / Single Room / Double Room
  status VARCHAR(20) NOT NULL DEFAULT 'available',-- available / not available
  rent_fee DECIMAL(12,2) NOT NULL,
  description TEXT,
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  room_id INT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- pending / approved / rejected
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL,
  remarks TEXT,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Payments
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payment_type VARCHAR(50),                       -- Monthly / Semester etc.
  status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- pending / paid / rejected
  reference VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Maintenance / Reports
CREATE TABLE maintenance_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  room_id INT NULL,
  title VARCHAR(150),
  description TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- pending / in-progress / resolved
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- Default admin (username: admin, password: admin123)
INSERT INTO admins (username, password_hash, name, email) VALUES
('admin', '$2y$10$Vb8LKvQd4yClWjA5yPzM/ObK05i7ka1TfwAkLu2A1Gm79uzXU8UjW', 'System Admin', 'admin@wmstay.local');
