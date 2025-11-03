CREATE TABLE services_list (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    button_text TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1
);

CREATE TABLE services_fields (
    field_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    field_type ENUM('text', 'textarea', 'date', 'select', 'checkbox', 'radio', 'file') NOT NULL,
    is_required BOOLEAN NOT NULL DEFAULT 1,
    display_order INT NOT NULL,
    allowed_file_types VARCHAR(255) NULL,

    FOREIGN KEY (service_id) REFERENCES services_list(service_id)
        ON DELETE CASCADE,

    -- This new line enforces your exact logic
    CONSTRAINT chk_file_types
        CHECK (
            -- The type MUST be 'file' OR the file_types column must be NULL
            (field_type = 'file') OR (allowed_file_types IS NULL)
        )
);

CREATE TABLE services_field_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    field_id INT NOT NULL,
    option_label VARCHAR(255) NOT NULL, 
    option_value VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,

    FOREIGN KEY (field_id) REFERENCES services_fields(field_id)
        ON DELETE CASCADE
);

CREATE TABLE services_request_statuses (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    
    -- Here is your new hex color column!
    color_hex VARCHAR(7) NOT NULL 
);

-- ---
-- I recommend adding these default statuses to start
-- ---
INSERT INTO services_request_statuses (status_name, description, color_hex) VALUES
('Pending', 'Waiting for admin review.', '#FFC107'),        -- Yellow
('In Progress', 'Your request is being processed.', '#007BFF'), -- Blue
('Completed', 'Your request is complete.', '#28A745'),       -- Green
('Rejected', 'Your request was rejected.', '#DC3545'),        -- Red
('Needs Resubmission', 'Missing information or files.', '#FD7E14'); -- Orange

CREATE TABLE services_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id BIGINT NOT NULL, 
    
    -- MODIFIED: This is now a Foreign Key to the table above.
    -- (We assume 'Pending' will have status_id = 1)
    status_id INT NOT NULL DEFAULT 1, 
    
    -- NEW: A place for admins to leave notes or rejection reasons
    admin_remarks TEXT NULL,

    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services_list(service_id),
    FOREIGN KEY (user_id) REFERENCES services_users(id),
    
    -- NEW: Foreign key relationship to the statuses table
    FOREIGN KEY (status_id) REFERENCES services_request_statuses(status_id)
);

CREATE TABLE services_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    field_id INT NOT NULL,
    answer_value TEXT NULL, -- Stores text, 'on' for checkbox, option_value, or file path

    FOREIGN KEY (request_id) REFERENCES services_requests(request_id)
        ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES services_fields(field_id)
        ON DELETE CASCADE
);