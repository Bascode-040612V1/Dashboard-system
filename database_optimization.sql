-- database_optimization.sql
-- Run these queries to optimize your database performance

-- Add indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);
CREATE INDEX IF NOT EXISTS idx_attendance_student_time ON attendance(student_id, time_in);
CREATE INDEX IF NOT EXISTS idx_attendance_student_timeout ON attendance(student_id, time_out);
CREATE INDEX IF NOT EXISTS idx_saved_attendance_date ON saved_attendance(saved_date);
CREATE INDEX IF NOT EXISTS idx_saved_attendance_student ON saved_attendance(student_id, saved_date);
CREATE INDEX IF NOT EXISTS idx_students_rfid ON students(rfid);
CREATE INDEX IF NOT EXISTS idx_students_number ON students(student_number);

-- Composite indexes for better JOIN performance
CREATE INDEX IF NOT EXISTS idx_attendance_student_date ON attendance(student_id, date);
CREATE INDEX IF NOT EXISTS idx_saved_attendance_composite ON saved_attendance(student_id, saved_date, saved_time_in);

-- Enable query cache (add to my.cnf or my.ini)
-- query_cache_type = 1
-- query_cache_size = 64M
-- query_cache_limit = 2M

-- Optimize table storage
OPTIMIZE TABLE students;
OPTIMIZE TABLE attendance;
OPTIMIZE TABLE saved_attendance;
OPTIMIZE TABLE admins;

-- Analyze tables for better query planning
ANALYZE TABLE students;
ANALYZE TABLE attendance;
ANALYZE TABLE saved_attendance;
ANALYZE TABLE admins;

-- Update table engine to InnoDB if not already (for better performance)
ALTER TABLE students ENGINE=InnoDB;
ALTER TABLE attendance ENGINE=InnoDB;
ALTER TABLE saved_attendance ENGINE=InnoDB;
ALTER TABLE admins ENGINE=InnoDB;