<?php
include '../includes/admin_header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch some basic metrics
$total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $total_users_result->fetch_assoc()['count'] ?? 0;

$total_bets_result = $conn->query("SELECT SUM(bet_amount) as total FROM game_history");
$total_bets = $total_bets_result->fetch_assoc()['total'] ?? 0.00;

$settings_result = $conn->query("SELECT * FROM game_settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();

// If settings are not set, provide default values
$max_loss = $settings['max_loss'] ?? 0.00;
$profit_target = $settings['profit_target'] ?? 0.00;
$company_balance = $settings['balance'] ?? 0.00;
?>

<h2>Admin Dashboard</h2>

<div class="admin-stats">
    <p>Total Users: <?php echo $total_users; ?></p>
    <p>Total Bets: <?php echo number_format((float)$total_bets, 2); ?> USD</p>
    <p>Max Loss: <?php echo number_format((float)$max_loss, 2); ?> USD</p>
    <p>Profit Target: <?php echo number_format((float)$profit_target, 2); ?> USD</p>
    <p>Company Balance: <?php echo number_format((float)$company_balance, 2); ?> USD</p>
</div>

<?php include '../includes/footer.php'; ?>
