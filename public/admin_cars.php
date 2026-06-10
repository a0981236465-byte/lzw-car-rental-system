<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_admin();

$allowedStatuses = ['available', 'maintenance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $carId = (int)($_POST['car_id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $plate = strtoupper(trim($_POST['plate_number'] ?? ''));
            $brand = trim($_POST['brand'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $seatCount = (int)($_POST['seat_count'] ?? 0);
            $dailyPrice = (float)($_POST['daily_price'] ?? 0);
            $status = $_POST['status'] ?? 'available';

            if ($plate === '' || $brand === '' || $model === '' || $seatCount <= 0 || $dailyPrice < 0 || !valid_status($status, $allowedStatuses)) {
                set_flash('Invalid car data.', 'error');
                redirect_to('admin_cars.php');
            }

            if ($action === 'create') {
                $stmt = db()->prepare('INSERT INTO cars (plate_number, brand, model, seat_count, daily_price, status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$plate, $brand, $model, $seatCount, $dailyPrice, $status]);
                set_flash('Car created successfully.');
            } else {
                $stmt = db()->prepare('UPDATE cars SET plate_number = ?, brand = ?, model = ?, seat_count = ?, daily_price = ?, status = ? WHERE car_id = ?');
                $stmt->execute([$plate, $brand, $model, $seatCount, $dailyPrice, $status, $carId]);
                set_flash('Car updated successfully.');
            }
        } elseif ($action === 'delete' && $carId > 0) {
            $stmt = db()->prepare('DELETE FROM cars WHERE car_id = ?');
            $stmt->execute([$carId]);
            set_flash('Car deleted successfully.');
        }
    } catch (Throwable $e) {
        set_flash('Operation failed. This car may be used by rental records or the plate number may already exist.', 'error');
    }

    redirect_to('admin_cars.php');
}

require_once __DIR__ . '/../src/header.php';
$editId = (int)($_GET['edit'] ?? 0);
$editCar = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM cars WHERE car_id = ?');
    $stmt->execute([$editId]);
    $editCar = $stmt->fetch();
}
$cars = db()->query('SELECT * FROM cars ORDER BY car_id DESC')->fetchAll();
?>
<h2>Manage Cars</h2>
<p>Admin can create, read, update, and delete car records.</p>

<h3><?= $editCar ? 'Edit Car' : 'Add Car' ?></h3>
<form method="post" action="admin_cars.php">
    <input type="hidden" name="action" value="<?= $editCar ? 'update' : 'create' ?>">
    <?php if ($editCar): ?>
        <input type="hidden" name="car_id" value="<?= e($editCar['car_id']) ?>">
    <?php endif; ?>
    <div class="form-grid">
        <div>
            <label for="plate_number">Plate Number</label>
            <input id="plate_number" name="plate_number" required value="<?= e($editCar['plate_number'] ?? '') ?>">
        </div>
        <div>
            <label for="brand">Brand</label>
            <input id="brand" name="brand" required value="<?= e($editCar['brand'] ?? '') ?>">
        </div>
        <div>
            <label for="model">Model</label>
            <input id="model" name="model" required value="<?= e($editCar['model'] ?? '') ?>">
        </div>
        <div>
            <label for="seat_count">Seat Count</label>
            <input id="seat_count" name="seat_count" type="number" min="1" required value="<?= e($editCar['seat_count'] ?? 5) ?>">
        </div>
        <div>
            <label for="daily_price">Daily Price</label>
            <input id="daily_price" name="daily_price" type="number" min="0" step="1" required value="<?= e($editCar['daily_price'] ?? 1200) ?>">
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($editCar['status'] ?? 'available', $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="submit"><?= $editCar ? 'Update Car' : 'Add Car' ?></button>
    <?php if ($editCar): ?>
        <a class="button secondary" href="admin_cars.php">Cancel</a>
    <?php endif; ?>
</form>

<h3>Car Records</h3>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Plate</th>
        <th>Brand / Model</th>
        <th>Seats</th>
        <th>Daily Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($cars as $car): ?>
        <tr>
            <td data-label="ID"><?= e($car['car_id']) ?></td>
            <td data-label="Plate"><?= e($car['plate_number']) ?></td>
            <td data-label="Brand / Model"><?= e($car['brand'] . ' ' . $car['model']) ?></td>
            <td data-label="Seats"><?= e($car['seat_count']) ?></td>
            <td data-label="Daily Price">NT$<?= e(number_format((float)$car['daily_price'], 0)) ?></td>
            <td data-label="Status"><span class="badge <?= e($car['status']) ?>"><?= e($car['status']) ?></span></td>
            <td data-label="Actions">
                <div class="actions">
                    <a class="button" href="admin_cars.php?edit=<?= e($car['car_id']) ?>">Edit</a>
                    <form method="post" action="admin_cars.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="car_id" value="<?= e($car['car_id']) ?>">
                        <button class="danger" type="submit">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
