<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        set_flash('Name, email, and password are required.', 'error');
        redirect_to('signup.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('Invalid email format.', 'error');
        redirect_to('signup.php');
    }

    if (strlen($password) < 6) {
        set_flash('Password must be at least 6 characters.', 'error');
        redirect_to('signup.php');
    }

    if ($password !== $confirmPassword) {
        set_flash('Passwords do not match.', 'error');
        redirect_to('signup.php');
    }

    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO users (name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $phone, $email, $hash, 'user']);

        $userId = (int)db()->lastInsertId();
        login_user([
            'user_id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'user',
        ]);

        set_flash('Sign up successful. You are now logged in.');
        redirect_to('rentals.php');
    } catch (Throwable $e) {
        set_flash('Sign up failed. The email may already be registered.', 'error');
        redirect_to('signup.php');
    }
}

require_once __DIR__ . '/../src/header.php';
?>
<h2>Sign Up</h2>
<div class="auth-box">
    <form method="post" action="signup.php">
        <div>
            <label for="name">Name *</label>
            <input id="name" name="name" required>
        </div>
        <div>
            <label for="phone">Phone</label>
            <input id="phone" name="phone">
        </div>
        <div>
            <label for="email">Email *</label>
            <input id="email" name="email" type="email" required>
        </div>
        <div>
            <label for="password">Password *</label>
            <input id="password" name="password" type="password" required>
        </div>
        <div>
            <label for="confirm_password">Confirm Password *</label>
            <input id="confirm_password" name="confirm_password" type="password" required>
        </div>
        <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
