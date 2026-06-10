# Admin Guide

## Login as Admin

```text
Email: admin@lzw.local
Password: admin123
```

After login, the **Admin** navigation item will appear. Normal users cannot see or access admin pages.

## Manage Cars

Open **Admin → Manage Cars**.

Admin can:

- Add cars
- Edit plate number, brand, model, seats, daily price, and status
- Delete cars that are not referenced by rental records

Car status values:

- `available`
- `maintenance`

## Manage Rentals

Open **Admin → Manage Rentals**.

Admin can:

- Create rental records for any user
- View all rental records
- Update rental date, car, user, purpose, and status
- Delete rental records

The system checks date overlap before creating or updating an active rental. Active statuses are:

- `reserved`
- `picked_up`

Returned and cancelled records do not block future bookings.

## Manage Users

Open **Admin → Manage Users**.

Admin can:

- Create user accounts
- Edit user information
- Reset user password
- Promote a user to admin
- Demote an admin to user
- Delete users without rental records

The current admin account cannot delete or demote itself.

## Maintenance

To reset database sample data:

```bash
cd /var/www/html/lzw-car-rental-system
sudo mysql < database/schema.sql
sudo mysql < database/seed.sql
```
