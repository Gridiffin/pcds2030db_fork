-- Sectors table (moved to the top to fix foreign key dependency)
CREATE TABLE sectors (
    sector_id INT AUTO_INCREMENT PRIMARY KEY,
    sector_name VARCHAR(100) NOT NULL, -- forestry, land, etc.
    description TEXT
);

-- Users/Agencies table (simplified and with direct sector link)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Hashed password
    agency_name VARCHAR(100) NULL, -- Made nullable for admin users
    role ENUM('admin', 'agency') NOT NULL, -- Admin is Ministry, agency is an agency user
    sector_id INT NULL, -- Each agency belongs to one sector (NULL for admin users)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sector_id) REFERENCES sectors(sector_id)
);

-- Programs table
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(255) NOT NULL,
    description TEXT,
    owner_agency_id INT NOT NULL, -- Which agency owns this program
    sector_id INT NOT NULL, -- Which sector this program belongs to
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_agency_id) REFERENCES users(user_id),
    FOREIGN KEY (sector_id) REFERENCES sectors(sector_id)
);

-- Reporting periods table
CREATE TABLE reporting_periods (
    period_id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    quarter INT NOT NULL, -- 1, 2, 3, or 4
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    UNIQUE KEY (year, quarter)
);

-- Program status and targets (consistent across all programs)
CREATE TABLE program_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    period_id INT NOT NULL,
    submitted_by INT NOT NULL, -- User ID who submitted
    target TEXT,
    achievement TEXT,
    status ENUM('on-track', 'delayed', 'completed', 'not-started') NOT NULL,
    remarks TEXT,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id),
    FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);

-- Sector Metric Definitions (what metrics exist for each sector)
CREATE TABLE sector_metrics_definition (
    metric_id INT AUTO_INCREMENT PRIMARY KEY,
    sector_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL, -- e.g., "Timber Export Value"
    metric_unit VARCHAR(50), -- e.g., "USD", "Hectares", "Percentage"
    metric_type ENUM('numeric', 'percentage', 'text') NOT NULL,
    display_order INT NOT NULL DEFAULT 0, -- For ordering in forms
    is_required BOOLEAN DEFAULT TRUE,
    description TEXT,
    added_by INT NOT NULL, -- User who added this metric
    is_approved BOOLEAN DEFAULT FALSE, -- Admin approval status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sector_id) REFERENCES sectors(sector_id),
    FOREIGN KEY (added_by) REFERENCES users(user_id),
    UNIQUE KEY (sector_id, metric_name)
);

-- Sector Metric Values (actual data submitted by agencies)
CREATE TABLE sector_metric_values (
    value_id INT AUTO_INCREMENT PRIMARY KEY,
    metric_id INT NOT NULL, -- References sector_metrics_definition
    agency_id INT NOT NULL, -- User ID of the agency submitting
    period_id INT NOT NULL,
    numeric_value DECIMAL(15,2) NULL,
    text_value TEXT NULL,
    notes TEXT,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (metric_id) REFERENCES sector_metrics_definition(metric_id),
    FOREIGN KEY (agency_id) REFERENCES users(user_id),
    FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id)
);

-- Generated reports table
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    period_id INT NOT NULL,
    generated_by INT NOT NULL, -- Admin user who generated the report
    pptx_path VARCHAR(255) NULL,
    pdf_path VARCHAR(255) NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (period_id) REFERENCES reporting_periods(period_id),
    FOREIGN KEY (generated_by) REFERENCES users(user_id)
);
