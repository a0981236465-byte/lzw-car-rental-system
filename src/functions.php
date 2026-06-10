<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function active_nav(string $file): string
{
    return basename($_SERVER['SCRIPT_NAME']) === $file ? ' class="active"' : '';
}

function valid_status(string $status, array $allowed): bool
{
    return in_array($status, $allowed, true);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'user_id' => (int)$user['user_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('Please login first.', 'error');
        redirect_to('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        set_flash('You do not have permission to access the admin page.', 'error');
        redirect_to('index.php');
    }
}

function selected($value, $expected): string
{
    return (string)$value === (string)$expected ? 'selected' : '';
}

function rental_has_conflict(int $carId, string $pickupDate, string $returnDate, ?int $excludeRentalId = null): bool
{
    $sql = "SELECT COUNT(*)
            FROM rentals
            WHERE car_id = ?
              AND rental_status IN ('reserved', 'picked_up')
              AND NOT (return_date < ? OR pickup_date > ?)";
    $params = [$carId, $pickupDate, $returnDate];

    if ($excludeRentalId !== null && $excludeRentalId > 0) {
        $sql .= ' AND rental_id <> ?';
        $params[] = $excludeRentalId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}
?>
