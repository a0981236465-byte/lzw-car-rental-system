-- Lzw Car Rental System
-- MariaDB schema for final project demo with authentication and role-based access control

CREATE DATABASE IF NOT EXISTS lzw_car_rental
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'rental_user'@'localhost' IDENTIFIED BY 'ChangeThisPassword';
GRANT ALL PRIVILEGES ON lzw_car_rental.* TO 'rental_user'@'localhost';
FLUSH PRIVILEGES;

USE lzw_car_rental;

DROP TABLE IF EXISTS rentals;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS members;

CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    role VARCHAR(80) NOT NULL,
    intro TEXT NOT NULL,
    photo_url VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cars (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    brand VARCHAR(60) NOT NULL,
    model VARCHAR(60) NOT NULL,
    seat_count INT NOT NULL,
    daily_price DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'maintenance') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (seat_count > 0),
    CHECK (daily_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rentals (
    rental_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    purpose VARCHAR(255) NULL,
    rental_status ENUM('reserved', 'picked_up', 'returned', 'cancelled') NOT NULL DEFAULT 'reserved',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rentals_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_rentals_car
        FOREIGN KEY (car_id) REFERENCES cars(car_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CHECK (return_date >= pickup_date),
    INDEX idx_rentals_car_dates (car_id, pickup_date, return_date),
    INDEX idx_rentals_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
