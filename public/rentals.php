<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_login();

$allowedRentalStatuses = ['reserved', 'picked_up', 'returned', 'cancelled'];
$currentUser = current_user();
$userId = (int)$currentUser['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rentalId = (int)($_POST['rental_id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $carId = (int)($_POST['car_id'] ?? 0);
            $pickupDate = trim($_POST['pickup_date'] ?? '');
            $returnDate = trim($_POST['return_date'] ?? '');
            $purpose = trim($_POST['purpose'] ?? '');

            if ($carId <= 0 || $pickupDate === '' || $returnDate === '') {
                set_flash('Please select a car and rental dates.', 'error');
                redirect_to('rentals.php');
            }

            if ($returnDate < $pickupDate) {
                set_flash('Return date cannot be earlier than pickup date.', 'error');
                redirect_to('rentals.php');
            }

            $stmt = db()->prepare('SELECT * FROM cars WHERE car_id = ?');
            $stmt->execute([$carId]);
            $car = $stmt->fetch();
            if (!$car || $car['status'] !== 'available') {
                set_flash('The selected car is not available.', 'error');
                redirect_to('rentals.php');
            }

            $excludeId = null;
            if ($action === 'update') {
                $stmt = db()->prepare('SELECT * FROM rentals WHERE rental_id = ? AND user_id = ?');
                $stmt->execute([$rentalId, $userId]);
                $existingRental = $stmt->fetch();

                if (!$existingRental) {
                    set_flash('Rental record not found.', 'error');
                    redirect_to('rentals.php');
                }

                if ($existingRental['rental_status'] !== 'reserved') {
                    set_flash('Only reserved rental orders can be modified by users.', 'error');
                    redirect_to('rentals.php');
                }
                $excludeId = $rentalId;
            }

            if (rental_has_conflict($carId, $pickupDate, $returnDate, $excludeId)) {
                set_flash('This car is already booked during the selected dates. Please choose another car or date range.', 'error');
                redirect_to($action === 'update' ? 'rentals.php?edit=' . $rentalId : 'rentals.php');
            }

            if ($action === 'create') {
                $stmt = db()->prepare('INSERT INTO rentals (user_id, car_id, pickup_date, return_date, purpose, rental_status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $carId, $pickupDate, $returnDate, $purpose, 'reserved']);
                set_flash('Rental order created successfully.');
            } else {
                $stmt = db()->prepare('UPDATE rentals SET car_id = ?, pickup_date = ?, return_date = ?, purpose = ? WHERE rental_id = ? AND user_id = ?');
                $stmt->execute([$carId, $pickupDate, $returnDate, $purpose, $rentalId, $userId]);
                set_flash('Rental order updated successfully.');
            }
        } elseif ($action === 'cancel' && $rentalId > 0) {
            $stmt = db()->prepare("UPDATE rentals SET rental_status = 'cancelled' WHERE rental_id = ? AND user_id = ? AND rental_status = 'reserved'");
            $stmt->execute([$rentalId, $userId]);
            set_flash('Rental order cancelled.');
        }
    } catch (Throwable $e) {
        set_flash('Operation failed: ' . $e->getMessage(), 'error');
    }

    redirect_to('rentals.php');
}

require_once __DIR__ . '/../src/header.php';

$editId = (int)($_GET['edit'] ?? 0);
$editRental = null;
if ($editId > 0) {
    $stmt = db()->prepare('SELECT * FROM rentals WHERE rental_id = ? AND user_id = ?');
    $stmt->execute([$editId, $userId]);
    $editRental = $stmt->fetch();
    if ($editRental && $editRental['rental_status'] !== 'reserved') {
        set_flash('Only reserved rental orders can be modified.', 'error');
        redirect_to('rentals.php');
    }
}

$preselectedCarId = (int)($_GET['car_id'] ?? ($editRental['car_id'] ?? 0));
$stmt = db()->prepare('SELECT * FROM cars WHERE status = ? OR car_id = ? ORDER BY brand ASC, model ASC');
$stmt->execute(['available', $preselectedCarId]);
$availableCars = $stmt->fetchAll();

$stmt = db()->prepare(
    'SELECT r.*, car.brand, car.model, car.plate_number
     FROM rentals r
     JOIN cars car ON r.car_id = car.car_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC'
);
$stmt->execute([$userId]);
$myRentals = $stmt->fetchAll();
?>
<h2><?= $editRental ? 'Edit My Rental Order' : 'Rent a Car' ?></h2>
<form method="post" action="rentals.php">
    <input type="hidden" name="action" value="<?= $editRental ? 'update' : 'create' ?>">
    <?php if ($editRental): ?>
        <input type="hidden" name="rental_id" value="<?= e($editRental['rental_id']) ?>">
    <?php endif; ?>
    <div class="form-grid">
        <div>
            <label for="car_id">Car *</label>
            <select id="car_id" name="car_id" required>
                <option value="">Select a car</option>
                <?php foreach ($availableCars as $car): ?>
                    <option value="<?= e($car['car_id']) ?>" <?= selected($car['car_id'], $preselectedCarId) ?>>
                        <?= e($car['brand'] . ' ' . $car['model'] . ' - ' . $car['plate_number']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="pickup_date">Pickup Date *</label>
            <input id="pickup_date" name="pickup_date" type="date" required value="<?= e($editRental['pickup_date'] ?? '') ?>">
        </div>
        <div>
            <label for="return_date">Return Date *</label>
            <input id="return_date" name="return_date" type="date" required value="<?= e($editRental['return_date'] ?? '') ?>">
        </div>
    </div>
    <label for="purpose">Purpose</label>
    <textarea id="purpose" name="purpose" placeholder="Example: weekend travel"><?= e($editRental['purpose'] ?? '') ?></textarea>
    <button type="submit"><?= $editRental ? 'Update Rental Order' : 'Create Rental Order' ?></button>
    <?php if ($editRental): ?>
        <a class="button secondary" href="rentals.php">Cancel Edit</a>
    <?php endif; ?>
</form>

<h2>My Rental Orders</h2>
<?php if (!$myRentals): ?>
    <p>You have no rental records.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Car</th>
            <th>Pickup</th>
            <th>Return</th>
            <th>Status</th>
            <th>Purpose</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($myRentals as $rental): ?>
            <tr>
                <td data-label="ID"><?= e($rental['rental_id']) ?></td>
                <td data-label="Car"><?= e($rental['brand'] . ' ' . $rental['model'] . ' (' . $rental['plate_number'] . ')') ?></td>
                <td data-label="Pickup"><?= e($rental['pickup_date']) ?></td>
                <td data-label="Return"><?= e($rental['return_date']) ?></td>
                <td data-label="Status"><span class="badge <?= e($rental['rental_status']) ?>"><?= e($rental['rental_status']) ?></span></td>
                <td data-label="Purpose"><?= e($rental['purpose']) ?></td>
                <td data-label="Actions">
                    <?php if ($rental['rental_status'] === 'reserved'): ?>
                        <div class="actions">
                            <a class="button" href="rentals.php?edit=<?= e($rental['rental_id']) ?>">Edit</a>
                            <form method="post" action="rentals.php">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="rental_id" value="<?= e($rental['rental_id']) ?>">
                                <button class="danger" type="submit">Cancel</button>
                            </form>
                        </div>
                    <?php else: ?>
                        No actions
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
