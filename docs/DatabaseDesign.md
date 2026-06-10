# Database Design

## 1. ERD Description

Entities:

- `members`: project member introductions
- `users`: application login accounts
- `cars`: rentable cars
- `rentals`: rental orders

Relationships:

- One user can have many rentals.
- One car can have many rentals over time.
- Each rental belongs to one user and one car.

Text ERD:

```text
members(member_id PK)

users(user_id PK)
  1 ───< rentals(rental_id PK, user_id FK, car_id FK) >─── 1 cars(car_id PK)
```

## 2. Relational Model

```text
members(member_id, student_id, name, role, intro, photo_url)
users(user_id, name, phone, email, password_hash, role, created_at)
cars(car_id, plate_number, brand, model, seat_count, daily_price, status, created_at)
rentals(rental_id, user_id, car_id, pickup_date, return_date, purpose, rental_status, created_at)
```

## 3. Third Normal Form

The schema is in 3NF because:

1. Each table stores one main entity type.
2. Each non-key attribute depends on the primary key.
3. There are no transitive dependencies between non-key attributes.
4. User login data is separated from rental orders.
5. Car information is separated from rental orders.

## 4. RDB Tables

### members

| Column | Type | Notes |
|---|---|---|
| member_id | INT | Primary key |
| student_id | VARCHAR(20) | Unique student ID |
| name | VARCHAR(80) | Member name |
| role | VARCHAR(80) | Project role |
| intro | TEXT | Member introduction |
| photo_url | VARCHAR(255) | Optional |

### users

| Column | Type | Notes |
|---|---|---|
| user_id | INT | Primary key |
| name | VARCHAR(80) | User name |
| phone | VARCHAR(30) | User phone |
| email | VARCHAR(120) | Unique login email |
| password_hash | VARCHAR(255) | Hashed password |
| role | ENUM('user','admin') | Permission role |
| created_at | TIMESTAMP | Creation time |

### cars

| Column | Type | Notes |
|---|---|---|
| car_id | INT | Primary key |
| plate_number | VARCHAR(20) | Unique plate number |
| brand | VARCHAR(60) | Car brand |
| model | VARCHAR(60) | Car model |
| seat_count | INT | Must be greater than 0 |
| daily_price | DECIMAL(10,2) | Must be greater than or equal to 0 |
| status | ENUM('available','maintenance') | Car availability status |
| created_at | TIMESTAMP | Creation time |

### rentals

| Column | Type | Notes |
|---|---|---|
| rental_id | INT | Primary key |
| user_id | INT | Foreign key to users |
| car_id | INT | Foreign key to cars |
| pickup_date | DATE | Rental start date |
| return_date | DATE | Rental end date |
| purpose | VARCHAR(255) | Optional rental purpose |
| rental_status | ENUM('reserved','picked_up','returned','cancelled') | Rental status |
| created_at | TIMESTAMP | Creation time |

## 5. Double Booking Rule

A car cannot have two active rental records with overlapping date ranges.

Active statuses:

```text
reserved
picked_up
```

Overlap rule used in PHP:

```sql
NOT (return_date < new_pickup_date OR pickup_date > new_return_date)
```

If this condition is true for an active rental of the same car, the new or updated rental is rejected.
