-- Create the database if not exists
CREATE DATABASE IF NOT EXISTS donation_system;

-- Use the database
USE donation_system;

-- Table for storing signups (user account creation)
CREATE TABLE IF NOT EXISTS signup (
    id INT AUTO_INCREMENT PRIMARY KEY,               -- Primary key
    full_name VARCHAR(100) NOT NULL,                 -- Full name of the registrant
    phone VARCHAR(15) NOT NULL,                      -- Phone number
    username VARCHAR(50) NOT NULL UNIQUE,            -- Unique username
    password VARCHAR(255) NOT NULL,                  -- Password (should be hashed)
    ktm_number VARCHAR(50) NOT NULL,                 -- KTM number (identity card number)
    email VARCHAR(100) NOT NULL,                     -- Email address
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP   -- Timestamp of the signup
);

-- Table for storing admin login information
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,               -- Primary key
    username VARCHAR(50) NOT NULL UNIQUE,            -- Unique username for the admin
    password VARCHAR(255) NOT NULL,                  -- Hashed password for security
    email VARCHAR(100) NOT NULL,                     -- Email address of the admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Timestamp of admin account creation
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Timestamp of last login
);

-- Insert default admin with hashed password
INSERT INTO admins (username, password, email)
VALUES
('admin', SHA2('1234', 256), 'admin@example.com'); -- Default admin username: 'admin', password: '1234'

-- Table for storing donation centers
CREATE TABLE IF NOT EXISTS donation_centers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,                      -- Name of the donation center
    address VARCHAR(255) NOT NULL,                   -- Address of the donation center
    contact_info VARCHAR(50) NOT NULL,               -- Contact information for the donation center
    signup_id INT NOT NULL,                          -- Foreign key for the user making the donation
    status ENUM('Pending', 'Approved', 'Rejected', 'Default') DEFAULT 'Pending', -- Status of the donation center
    rejection_reason TEXT NULL,                      -- Reason for rejection, if applicable
    FOREIGN KEY (signup_id) REFERENCES signup(id) NOT NULL DEFAULT 1 ON DELETE CASCADE -- Cascade delete if the user is deleted
);

-- Table for storing financial donations
CREATE TABLE IF NOT EXISTS financial_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(20, 2) NOT NULL,
    contact_info VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Confirmed') NOT NULL DEFAULT 'Pending',
    donation_center_id INT NOT NULL,                -- Foreign key for the associated donation center
    rejection_reason TEXT NULL,                      -- Reason for rejection, if applicable
    FOREIGN KEY (donation_center_id) REFERENCES donation_centers(id) NOT NULL DEFAULT 1 ON DELETE CASCADE -- Cascade delete if the donation center is deleted
);

-- Table for storing item donations
CREATE TABLE IF NOT EXISTS item_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_type VARCHAR(100) NOT NULL,                 -- Type of item being donated
    location VARCHAR(100) NOT NULL,                  -- Drop-off location for item
    contact_info VARCHAR(100) NOT NULL,              -- Contact information of the donor
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Timestamp of donation
    status ENUM('pending', 'confirmed') DEFAULT 'pending', -- Status of donation
    rejection_reason TEXT NULL,                      -- Reason for rejection, if applicable
    signup_id INT NOT NULL,                          -- Foreign key for the user making the donation
    donation_center_id INT NOT NULL,                 -- Foreign key for the associated donation center
    FOREIGN KEY (signup_id) REFERENCES signup(id) ON DELETE CASCADE, -- Cascade delete if the user is deleted
    FOREIGN KEY (donation_center_id) REFERENCES donation_centers(id) NOT NULL DEFAULT 1 ON DELETE CASCADE -- Cascade delete if the donation center is deleted
);

-- Table for storing donation assistance requests
CREATE TABLE IF NOT EXISTS donation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistance_type ENUM('Bantuan Barang dan Pangan', 'Bantuan Dana') NOT NULL,  -- Type of assistance
    description TEXT NOT NULL,                                         -- Description of the request
    supporting_documents VARCHAR(255),                                  -- Path to uploaded documents
    status ENUM('Pending', 'Confirmed', 'Reject') DEFAULT 'Pending',    -- Status of the request
    rejection_reason TEXT NULL,
    signup_id INT NOT NULL,                                             -- User ID from signup table
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                     -- Timestamp of the request
    FOREIGN KEY (signup_id) REFERENCES signup(id) ON DELETE CASCADE, -- Cascade delete if the user is deleted
    FOREIGN KEY (signup_id) REFERENCES signup(id) ON DELETE CASCADE    -- Foreign key to the signup table
);

-- Table for storing contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
