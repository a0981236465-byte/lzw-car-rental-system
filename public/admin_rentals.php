<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_admin();

$allowedStatuses = ['reserved', 'picked_up', 'returned', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rentalId = (int)($_POST['rental_id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $carId = (int)($_POST['car_id'] ?? 0);
            $pickupDate = trim($_POST['pickup_date'] ?? '');
            $returnDate = trim($_POST['return_date'] ?? '');
            $purpose = trim($_POST['purpose'] ?? '');
            $status = $_POST['rental_status'] ?? 'reserved';

            if ($userId <= 0 || $carId <= 0 || $pickupDate === '' || $returnDate === '' || !valid_status($status, $allowedStatuses)) {
                set_flash('Invalid rental data.', 'error');
                redirect_to('admin_rentals.php');
            }

            if ($returnDate < $pickupDate) {
                set_flash('Return date cannot be earlier than pickup date.', 'error');
                redirect_to('admin_rentals.php');
            }

            $stmt = db()->prepare('SELECT * FROM cars WHERE car_id = ?');
            $stmt->execute([$carId]);
            $car = $stmt->fetch();
            if (!$car) {
                set_flash('Selected car does not exist.', 'error');
                redirect_to('admin_rentals.php');
            }

            if (in_array($status, ['reserved', 'picked_up'], true)) {
                if ($car['status'] !== 'available') {
                    set_flash('Maintenance cars cannot be assigned to active rentals.', 'error');
                    redirect_to('admin_rentals.php');
                }

                $excludeId = $action === 'update' ? $rentalId : null;
                if (rental_has_conflict($carId, $pickupDate, $returnDate, $excludeId)) {
                    set_flash('Double booking blocked: this car already has an active rental during the selected dates.', 'error');
                    redirect_to($action === 'update' ? 'admin_rentals.php?edit=' . $rentalId : 'admin_rentals.php');
                }
            }

            if ($action === 'create') {
                $stmt = db()->prepare('INSERT INTO rentals (user_id, car_id, pickup_date, return_date, purpose, rental_status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $carId, $pickupDate, $returnDate, $purpose, $status]);
                set_flash('Rental created successfully.');
            } else {
                $stmt = db()->prepare('UPDATE rentals SET user_id = ?, car_id = ?, pickup_date = ?, return_date = ?, purpose = ?, rental_status = ? WHERE rental_id = ?');
                $stmt->execute([$userId, $carId, $pickupDate, $returnDate, $purpose, $status, $rentalId]);
                set_flash('Rental updated successfully.');
            }
        } elseif ($action === 'delete' && $rentalId > 0) {
            $stmt = db()->prepare('DELETE FROM rentals WHERE rental_id = ?');
            $stmt->execute([$rentalId]);
            set_flash('Rental deleted successfully.');
        }
    } catch (Throwable $e) {
        set_flash('Operation failed: ' . $e->getMessage(), 'error');
    }

    redirect_to('admin_rentals.php');
}

require_once __DIR__ . '/../src/header.php';
$editId = (int)($_GET['edit'] ?? 0);
$editRental = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM rentals WHERE rental_id = ?');
    $stmt->execute([$editId]);
    $editRental = $stmt->fetch();
}

$users = db()->query('SELECT user_id, name, email, role FROM users ORDER BY name ASC')->fetchAll();
$cars = db()->query('SELECT * FROM cars ORDER BY brand ASC, model ASC')->fetchAll();
$rentals = db()->query(
    'SELECT r.*, u.name AS user_name, u.email, car.brand, car.model, car.plate_number
     FROM rentals r
     JOIN users u ON r.user_id = u.user_id
     JOIN cars car ON r.car_id = car.car_id
     ORDER BY r.created_at DESC'
)->fetchAll();
?>
<h2>Manage Rentals</h2>
<p>Admin can view and modify all rental orders. The system blocks overlapping active rentals for the same car.</p>

<h3><?= $editRental ? 'Edit Rental' : 'Add Rental' ?></h3>
<form method="post" action="admin_rentals.php">
    <input type="hidden" name="action" value="<?= $editRental ? 'update' : 'create' ?>">
    <?php if ($editRental): ?>
        <input type="hidden" name="rental_id" value="<?= e($editRental['rental_id']) ?>">
    <?php endif; ?>
    <div class="form-grid">
        <div>
            <label for="user_id">User</label>
            <select id="user_id" name="user_id" required>
                <option value="">Select a user</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= e($user['user_id']) ?>" <?= selected($editRental['user_id'] ?? '', $user['user_id']) ?>>
                        <?= e($user['name'] . ' - ' . $user['email']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="car_id">Car</label>
            <select id="car_id" name="car_id" required>
                <option value="">Select a car</option>
                <?php foreach ($cars as $car): ?>
                    <option value="<?= e($car['car_id']) ?>" <?= selected($editRental['car_id'] ?? '', $car['car_id']) ?>>
                        <?= e($car['brand'] . ' ' . $car['model'] . ' - ' . $car['plate_number'] . ' (' . $car['status'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="pickup_date">Pickup Date</label>
            <input id="pickup_date" name="pickup_date" type="date" required value="<?= e($editRental['pickup_date'] ?? '') ?>">
        </div>
        <div>
            <label for="return_date">Return Date</label>
            <input id="return_date" name="return_date" type="date" required value="<?= e($editRental['return_date'] ?? '') ?>">
        </div>
        <div>
            <label for="rental_status">Status</label>
            <select id="rental_status" name="rental_status" required>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($editRental['rental_status'] ?? 'reserved', $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <label for="purpose">Purpose</label>
    <textarea id="purpose" name="purpose"><?= e($editRental['purpose'] ?? '') ?></textarea>
    <button type="submit"><?= $editRental ? 'Update Rental' : 'Add Rental' ?></button>
    <?php if ($editRental): ?>
        <a class="button secondary" href="admin_rentals.php">Cancel</a>
    <?php endif; ?>
</form>

<h3>Rental Records</h3>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Car</th>
        <th>Pickup</th>
        <th>Return</th>
        <th>Status</th>
        <th>Purpose</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rentals as $rental): ?>
        <tr>
            <td data-label="ID"><?= e($rental['rental_id']) ?></td>
            <td data-label="User"><?= e($rental['user_name'] . ' (' . $rental['email'] . ')') ?></td>
            <td data-label="Car"><?= e($rental['brand'] . ' ' . $rental['model'] . ' (' . $rental['plate_number'] . ')') ?></td>
            <td data-label="Pickup"><?= e($rental['pickup_date']) ?></td>
            <td data-label="Return"><?= e($rental['return_date']) ?></td>
            <td data-label="Status"><span class="badge <?= e($rental['rental_status']) ?>"><?= e($rental['rental_status']) ?></span></td>
            <td data-label="Purpose"><?= e($rental['purpose']) ?></td>
            <td data-label="Actions">
                <div class="actions">
                    <a class="button" href="admin_rentals.php?edit=<?= e($rental['rental_id']) ?>">Edit</a>
                    <form method="post" action="admin_rentals.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="rental_id" value="<?= e($rental['rental_id']) ?>">
                        <button class="danger" type="submit">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
