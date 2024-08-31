<?php
session_start();  // Start session to access user data

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Game</title>
    <link rel="stylesheet" href="/crash/assets/css/styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <a href="../index.php">Home</a>
                <?php if ($is_logged_in): ?>
                    <a href="bet.php">Bet</a> <!-- Link to bet page -->
                    <a href="dashboard.php">Dashboard</a>
                    <a href="profile.php">Profile</a>
                    <a href="history.php">My History</a> <!-- Link to user history -->
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="/crash//user/login.php">Login</a>
                    <a href="/crash/user/register.php">Register</a>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
