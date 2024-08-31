<?php
include '../includes/db.php';  // Database connection

// Start session to access user data
session_start();

$response = ['success' => false, 'message' => ''];

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle AJAX request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    $amount = $_POST['amount'];

    // Validate amount
    if (is_numeric($amount) && $amount > 0) {
        // Update user balance
        if ($stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")) {
            $stmt->bind_param("di", $amount, $user_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Balance updated successfully.';
            } else {
                $response['message'] = 'Error updating balance.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error preparing statement.';
        }
    } else {
        $response['message'] = 'Invalid amount.';
    }
} else {
    $response['message'] = 'No amount specified.';
}

echo json_encode($response);
