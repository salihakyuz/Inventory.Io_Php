DROP DATABASE IF EXISTS inventory_system;
CREATE DATABASE inventory_system;

USE inventory_system;

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);

-- Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(50) NOT NULL
);

-- Role Permissions Table
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Warehouses Table
CREATE TABLE IF NOT EXISTS warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    warehouse_id INT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- Insert Default Roles
INSERT INTO roles (role_name) VALUES ('admin'), ('user');

-- Insert Default Permissions
INSERT INTO permissions (permission_name) VALUES ('manage_users'), ('manage_warehouses'), ('manage_products');

-- Assign Permissions to Roles
-- Admin can manage users, warehouses, and products
INSERT INTO role_permissions (role_id, permission_id) VALUES (1, 1), (1, 2), (1, 3);
-- User can manage products
INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 3);

-- Insert Admin User
INSERT INTO users (full_name, email, password, role_id) VALUES
('Admin User', 'admin@example.com', '$2y$10$mGYgIg5k0vg/p2943whlhOLPNuMDO1bIsdAGQ7toQONR8TpeS5Cje', 1);

-- Insert Regular Users
INSERT INTO users (full_name, email, password, role_id) VALUES
('User One', 'user1@example.com', '$2y$10$mGYgIg5k0vg/p2943whlhOLPNuMDO1bIsdAGQ7toQONR8TpeS5Cje', 2),
('User Two', 'user2@example.com', '$2y$10$mGYgIg5k0vg/p2943whlhOLPNuMDO1bIsdAGQ7toQONR8TpeS5Cje', 2);