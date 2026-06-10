<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_admin();
require_once __DIR__ . '/../src/header.php';

$carCount = (int)db()->query('SELECT COUNT(*) FROM cars')->fetchColumn();
$userCount = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$rentalCount = (int)db()->query('SELECT COUNT(*) FROM rentals')->fetchColumn();
$activeRentalCount = (int)db()->query("SELECT COUNT(*) FROM rentals WHERE rental_status IN ('reserved', 'picked_up')")->fetchColumn();
?>
<h2>Admin Dashboard</h2>
<p>This page is visible only to admin accounts.</p>

<div class="grid">
    <div class="card">
        <h3>Cars</h3>
        <p><?= e($carCount) ?> records</p>
        <a class="button" href="admin_cars.php">Manage Cars</a>
    </div>
    <div class="card">
        <h3>Rentals</h3>
        <p><?= e($rentalCount) ?> total records</p>
        <p><?= e($activeRentalCount) ?> active records</p>
        <a class="button" href="admin_rentals.php">Manage Rentals</a>
    </div>
    <div class="card">
        <h3>Users</h3>
        <p><?= e($userCount) ?> accounts</p>
        <a class="button" href="admin_users.php">Manage Users</a>
    </div>
</div>

<section class="note">
    <p><strong>Admin permissions:</strong> create/update/delete cars, view/update all rental orders, and manage user roles.</p>
</section>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
