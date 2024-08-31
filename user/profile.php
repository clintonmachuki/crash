<?php
include '../includes/db.php';  // Database connection
include '../includes/header.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile
if ($stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $balance);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Error fetching user profile.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="/crash/assets/css/styles.css">
    <script src="/crash/assets/js/main.js"></script> <!-- Ensure you have your JavaScript file linked -->
</head>
<body>

    <main>
        <h2>User Profile</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Balance:</strong> $<?php echo number_format($balance, 2); ?></p>

        <h3>Add Balance</h3>
        <form id="addBalanceForm">
            <label for="amount">Amount (USD):</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
            <button type="submit">Add Balance</button>
        </form>
        <p id="responseMessage"></p> <!-- To display success or error messages -->
    </main>
</body>
</html>
