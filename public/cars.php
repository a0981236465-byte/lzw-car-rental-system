<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/header.php';

$cars = db()->query('SELECT * FROM cars ORDER BY brand ASC, model ASC')->fetchAll();
?>
<h2>Cars</h2>
<p>Browse the car fleet. Login to create a rental order.</p>

<?php if (!$cars): ?>
    <p>No cars found.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($cars as $car): ?>
            <article class="card">
                <h3><?= e($car['brand'] . ' ' . $car['model']) ?></h3>
                <p><strong>Plate:</strong> <?= e($car['plate_number']) ?></p>
                <p><strong>Seats:</strong> <?= e($car['seat_count']) ?></p>
                <p><strong>Daily Price:</strong> NT$<?= e(number_format((float)$car['daily_price'], 0)) ?></p>
                <p><strong>Status:</strong> <span class="badge <?= e($car['status']) ?>"><?= e($car['status']) ?></span></p>
                <?php if (is_logged_in() && $car['status'] === 'available'): ?>
                    <a class="button" href="rentals.php?car_id=<?= e($car['car_id']) ?>">Rent This Car</a>
                <?php elseif (!is_logged_in()): ?>
                    <a class="button secondary" href="login.php">Login to Rent</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
