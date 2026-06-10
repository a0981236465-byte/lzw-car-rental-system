<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/header.php';

$carCount = (int)db()->query('SELECT COUNT(*) FROM cars')->fetchColumn();
$userCount = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$rentalCount = (int)db()->query('SELECT COUNT(*) FROM rentals')->fetchColumn();
$availableCount = (int)db()->query("SELECT COUNT(*) FROM cars WHERE status = 'available'")->fetchColumn();
?>
<div class="grid">
    <div class="card">
        <h3>Cars</h3>
        <p><?= e($carCount) ?> total cars</p>
        <p><?= e($availableCount) ?> available cars</p>
    </div>
    <div class="card">
        <h3>Users</h3>
        <p><?= e($userCount) ?> registered accounts</p>
    </div>
    <div class="card">
        <h3>Rentals</h3>
        <p><?= e($rentalCount) ?> rental records</p>
    </div>
</div>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
