<?php
include '../includes/db.php'; // Database connection
include '../includes/header.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user betting history
if ($stmt = $conn->prepare("SELECT bet_amount, payout, multiplier, created_at FROM game_history WHERE user_id = ? ORDER BY created_at DESC")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Handle error if query preparation fails
    echo "Error fetching history.";
    exit;
}

// Calculate total bet amount, total payouts, and net profit/loss
$total_bet_amount = 0;
$total_payout = 0;

while ($row = $result->fetch_assoc()) {
    $bet_amount = $row['bet_amount'] ?? 0.00;
    $payout = $row['payout'] ?? 0.00;

    $total_bet_amount += $bet_amount;

    if ($payout > 0) {
        $total_payout += $payout;
    } else {
        $total_payout -= $bet_amount; // Subtract bet amount for losses
    }
}

$net_profit_loss = $total_payout - $total_bet_amount;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History</title>
    <link rel="stylesheet" href="/crash/assets/css/styles.css">
    <style>
        .loss {
            color: red;
        }
        .profit {
            color: green;
        }
        .summary {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <main>
        <h2>My Betting History</h2>

        <div class="summary">
            <h3>Summary</h3>
            <p><strong>Total Amount Placed:</strong> <?php echo number_format($total_bet_amount, 2); ?> USD</p>
            <p><strong>Total Payouts:</strong> 
                <?php 
                if ($total_payout >= 0) {
                    echo '<span class="profit">+' . number_format($total_payout, 2) . ' USD</span>';
                } else {
                    echo '<span class="loss">-' . number_format(abs($total_payout), 2) . ' USD</span>';
                }
                ?>
            </p>
            <p><strong>Net Profit/Loss:</strong> 
                <?php 
                if ($net_profit_loss >= 0) {
                    echo '<span class="profit">+' . number_format($net_profit_loss, 2) . ' USD</span>';
                } else {
                    echo '<span class="loss">-' . number_format(abs($net_profit_loss), 2) . ' USD</span>';
                }
                ?>
            </p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Bet Amount</th>
                        <th>Payout</th>
                        <th>Multiplier</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Re-fetch the result set to display the table data
                    $result->data_seek(0); // Reset result pointer
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                $bet_amount = $row['bet_amount'] ?? 0.00;
                                echo number_format($bet_amount, 2); 
                                ?> USD
                            </td>
                            <td>
                                <?php 
                                $payout = $row['payout'] ?? 0.00;
                                if ($payout > 0) {
                                    echo '<span class="profit">+' . number_format($payout, 2) . ' USD</span>';
                                } else {
                                    $loss_amount = $bet_amount;
                                    echo '<span class="loss">-' . number_format($loss_amount, 2) . ' USD</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo number_format($row['multiplier'] ?? 0.00, 2); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No betting history found.</p>
        <?php endif; ?>

    </main>
</body>
</html>
