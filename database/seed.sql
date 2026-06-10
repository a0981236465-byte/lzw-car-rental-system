USE lzw_car_rental;

INSERT INTO members (student_id, name, role, intro) VALUES
('413850446', '陳昱丞', 'Project Integrator / Main Developer', 'Responsible for overall integration, authentication flow, role control, database scripts, and final deployment.'),
('413850305', '宋柏穎', 'Backend Developer', 'Responsible for PHP database connection, CRUD logic, session handling, and server-side validation.'),
('413850297', '李諺儒', 'System Tester', 'Responsible for testing rental workflows, checking CRUD operations, and verifying booking conflict behavior.'),
('413850164', '侯冠丞', 'Database Designer', 'Responsible for relational database design, table relationships, and sample data preparation.'),
('413850149', '張恩睿', 'Documentation Support', 'Responsible for basic page layout support and project documentation assistance.');

-- Default accounts:
-- Admin email: admin@lzw.local / password: admin123
-- User email: user@lzw.local / password: user123
INSERT INTO users (name, phone, email, password_hash, role) VALUES
('System Admin', '0900-000-000', 'admin@lzw.local', '$2y$12$IsCdx2h5e8I7Nki9xqf3DOWS2RJG4Ix4LmYeTNmB0TdGUUwaWeT0a', 'admin'),
('Demo User', '0912-345-678', 'user@lzw.local', '$2y$12$99SRP0G6M7/Bm6w91WZANesR2sdDGDp33XR6iljSY8pLgdCJ8w966', 'user');

INSERT INTO cars (plate_number, brand, model, seat_count, daily_price, status) VALUES
('LZW-1001', 'Toyota', 'Yaris', 5, 1200.00, 'available'),
('LZW-1002', 'Honda', 'Fit', 5, 1300.00, 'available'),
('LZW-1003', 'Toyota', 'Sienta', 7, 1800.00, 'available'),
('LZW-1004', 'Nissan', 'Kicks', 5, 1600.00, 'maintenance');

INSERT INTO rentals (user_id, car_id, pickup_date, return_date, purpose, rental_status) VALUES
(2, 1, '2026-06-10', '2026-06-12', 'Weekend trip demo record', 'reserved');
