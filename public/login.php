<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

if (is_logged_in()) {
    redirect_to(is_admin() ? 'admin.php' : 'rentals.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user);
        set_flash('Login successful.');
        redirect_to($user['role'] === 'admin' ? 'admin.php' : 'rentals.php');
    }

    set_flash('Invalid email or password.', 'error');
    redirect_to('login.php');
}

require_once __DIR__ . '/../src/header.php';
?>
<h2>Login</h2>
<div class="auth-box">
    <form method="post" action="login.php">
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>No account yet? <a href="signup.php">Sign up here</a>.</p>
</div>
<?php require_once __DIR__ . '/../src/footer.php'; ?>
