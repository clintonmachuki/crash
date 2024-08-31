<?php
include '../includes/db.php';

session_start();

$user_id = $_SESSION['user_id'];
$bet_amount = $_POST['bet_amount'];
$multiplier = $_POST['multiplier'];

// Fetch company settings
$settings_result = $conn->query("SELECT * FROM game_settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();
$company_balance = $settings['balance'];

// Calculate the outcome
$payout = $bet_amount * $multiplier;

if ($multiplier >= 1) {
    // User wins
    $user_profit = $payout - $bet_amount;
    $company_balance -= $user_profit;
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $payout, $user_id);
} else {
    // User loses
    $company_balance += $bet_amount;
    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("di", $bet_amount, $user_id);
}

$stmt->execute();
$stmt->close();

// Update company balance in the database
$stmt = $conn->prepare("UPDATE ga
