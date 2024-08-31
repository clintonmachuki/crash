<?php
include '../includes/db.php'; // Database connection

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the most recent bet from the database
if ($stmt = $conn->prepare("
    SELECT bet_amount, multiplier AS actual_multiplier,
           CASE 
               WHEN payout >= bet_amount * multiplier THEN 'Win'
               ELSE 'Loss'
           END AS result
    FROM game_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($bet_amount, $actual_multiplier, $result);

    if ($stmt->fetch()) {
        // Set predicted multiplier to the actual multiplier for display purposes
        $predicted_multiplier = $actual_multiplier;

        $bet = [
            'bet_amount' => $bet_amount,
            'predicted_multiplier' => $predicted_multiplier,
            'actual_multiplier' => $actual_multiplier,
            'result' => $result
        ];

        echo json_encode(['bet' => $bet]);
    } else {
        echo json_encode(['error' => 'No recent bets found.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error fetching recent bet.']);
}
?>
