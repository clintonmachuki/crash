<?php
include '../includes/admin_header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch game history
$query = "
    SELECT gh.id, u.username, gh.bet_amount, gh.payout, gh.created_at
    FROM game_history gh
    JOIN users u ON gh.user_id = u.id
    ORDER BY gh.created_at DESC
";

$game_history = $conn->query($query);

if (!$game_history) {
    die("Query failed: " . $conn->error);
}
?>

<h2>Game History</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Bet Amount</th>
            <th>Payout</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($record = $game_history->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['id']); ?></td>
            <td><?php echo htmlspecialchars($record['username']); ?></td>
            <td><?php echo number_format($record['bet_amount'], 2); ?> USD</td>
            <td style="color: <?php echo ($record['payout'] === null || $record['payout'] == 0) ? 'red' : 'green'; ?>;">
                <?php
                if ($record['payout'] === null || $record['payout'] == 0) {
                    echo number_format(-$record['bet_amount'], 2) . " USD"; // Indicate loss with negative bet amount
                } else {
                    echo number_format($record['payout'], 2) . " USD"; // Display payout
                }
                ?>
            </td>
            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
