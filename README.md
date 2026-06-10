# Lzw Car Rental System

A simple PHP + MariaDB car rental web application for the final project demo on Raspberry Pi Zero 2W.

## Features

- No frameworks
- Apache + PHP + MariaDB
- Sign Up / Login / Logout
- Password hashing with PHP `password_hash()`
- Role-based access control
  - Normal users can rent cars and manage only their own rental orders
  - Admin users can manage cars, rentals, and user accounts
- Admin can promote or demote users
- Double-booking prevention for overlapping rental date ranges
- In-app member introduction page
- Database design documentation: ERD, relational model, 3NF, and RDB tables

## Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@lzw.local` | `admin123` |
| User | `user@lzw.local` | `user123` |

## Main Pages

- `public/index.php` - Home
- `public/signup.php` - Sign Up
- `public/login.php` - Login
- `public/logout.php` - Logout
- `public/cars.php` - Car list
- `public/rentals.php` - User rental order page
- `public/members.php` - Member introductions
- `public/admin.php` - Admin dashboard
- `public/admin_cars.php` - Admin car CRUD
- `public/admin_rentals.php` - Admin rental CRUD
- `public/admin_users.php` - Admin user account and role management

## Repository Tag

The presented version should be tagged:

```bash
git tag FINAL_PRESENTATION
git push origin FINAL_PRESENTATION
```
