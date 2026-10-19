<?php

$page_title = $page_title ?? 'Parkora';
$base = '/Parkora_Parking_Garage/';
$admin = str_contains($_SERVER['SCRIPT_NAME'], '/admin/');

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> · Parkora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= $base ?>index.php">
        <span class="mark">P</span>
        PARKORA
    </a>
    <nav>
        <a href="<?= $base ?>parking.php">Parking</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="<?= $base ?>user_dashboard.php">My Garage</a>
            <a class="pill" href="<?= $base ?>logout.php">Logout</a>
        <?php elseif (!empty($_SESSION['admin_id'])): ?>
            <a href="<?= $base ?>admin/dashboard.php">Admin</a>
            <a class="dark" href="<?= $base ?>admin_logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= $base ?>register.php">Driver Register</a>
            <a href="<?= $base ?>user_login.php">Driver Login</a>
            <a class="dark" href="<?= $base ?>login.php">Admin Login</a>
        <?php endif; ?>
    </nav>
</header>
<main>
