-- Create database
CREATE DATABASE IF NOT EXISTS flow_metering;
USE flow_metering;

-- Create streams table
CREATE TABLE IF NOT EXISTS streams (
    stream_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    status ENUM('active', 'standby', 'maintenance') DEFAULT 'standby',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create measurements table
CREATE TABLE IF NOT EXISTS measurements (
    measurement_id INT PRIMARY KEY AUTO_INCREMENT,
    stream_id INT NOT NULL,
    flow_rate DECIMAL(10,2) NOT NULL,
    pressure DECIMAL(10,2) NOT NULL,
    temperature DECIMAL(10,2) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stream_id) REFERENCES streams(stream_id)
);

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default streams
INSERT INTO streams (name, status) VALUES
('Stream A', 'active'),
('Stream B', 'standby');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('changeover_threshold', 100.00),
('pressure_limit', 10.00),
('temperature_limit', 50.00);

-- Create indexes for better performance
CREATE INDEX idx_measurements_timestamp ON measurements(timestamp);
CREATE INDEX idx_measurements_stream_id ON measurements(stream_id);
CREATE INDEX idx_streams_status ON streams(status); 