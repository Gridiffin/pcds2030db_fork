-- Migration: Add program status and hold points support

-- 1. Add status field to programs table (if not exists)
ALTER TABLE programs
ADD COLUMN status ENUM('active', 'on_hold', 'completed', 'delayed', 'cancelled') DEFAULT 'active' AFTER program_name;

-- 2. Create program_status_history table
CREATE TABLE IF NOT EXISTS program_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    status ENUM('active', 'on_hold', 'completed', 'delayed', 'cancelled') NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);

-- 3. Create program_hold_points table
CREATE TABLE IF NOT EXISTS program_hold_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    reason TEXT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
); 