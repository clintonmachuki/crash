<?php
session_start(); // Start the session

include '../includes/db.php';  // Database connection

// Redirect to login page if user is not logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fetch game settings
if ($stmt = $conn->prepare("SELECT max_loss, profit_target, balance, current_loss FROM game_settings WHERE id = 1")) {
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($max_loss, $profit_target, $current_balance, $current_loss);
    $stmt->fetch();
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error fetching game settings.']);
    exit;
}

// Check if the profit target has been achieved
$profit_achieved = $current_balance >= $profit_target;

// Return the stats as JSON
echo json_encode([
    'total_users' => $total_users ?? 0,
    'total_bets' => number_format((float)($total_bets ?? 0.00), 2),
    'max_loss' => number_format((float)($max_loss ?? 0.00), 2),
    'profit_target' => number_format((float)($profit_target ?? 0.00), 2),
    'company_balance' => number_format((float)($current_balance ?? 0.00), 2),
    'profit_achieved' => $profit_achieved
]);
?>
