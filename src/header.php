<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$flash = get_flash();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <h1><?= e(APP_NAME) ?></h1>
    </div>
</header>
<nav class="site-nav">
    <div class="container nav-inner">
        <a<?= active_nav('index.php') ?> href="index.php">Home</a>
        <a<?= active_nav('cars.php') ?> href="cars.php">Cars</a>
        <?php if (is_logged_in()): ?>
            <a<?= active_nav('rentals.php') ?> href="rentals.php">Rentals</a>
        <?php endif; ?>
        <a<?= active_nav('members.php') ?> href="members.php">Members</a>
        <?php if (is_admin()): ?>
            <a<?= active_nav('admin.php') ?> href="admin.php">Admin</a>
        <?php endif; ?>
        <span class="nav-spacer"></span>
        <?php if (is_logged_in()): ?>
            <span class="nav-user">Hi, <?= e($user['name']) ?> (<?= e($user['role']) ?>)</span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a<?= active_nav('signup.php') ?> href="signup.php">Sign Up</a>
            <a<?= active_nav('login.php') ?> href="login.php">Login</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container main-content">
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
