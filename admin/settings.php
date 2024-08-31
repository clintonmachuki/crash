<?php
include '../includes/admin_header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle form submission to update settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $max_loss = $_POST['max_loss'];
    $profit_target = $_POST['profit_target'];
    $company_balance = $_POST['company_balance'];

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE game_settings SET max_loss = ?, profit_target = ?, balance = ? WHERE id = 1");
    $stmt->bind_param("ddd", $max_loss, $profit_target, $company_balance);
    $stmt->execute();
    $stmt->close();
    
    header("Location: settings.php?success=1");
    exit;
}

// Fetch current settings
$settings_result = $conn->query("SELECT * FROM game_settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();

// Default values if settings are not set
$max_loss = $settings['max_loss'] ?? 0.00;
$profit_target = $settings['profit_target'] ?? 0.00;
$company_balance = $settings['balance'] ?? 0.00;
?>

<h2>Game Settings</h2>

<?php if (isset($_GET['success'])): ?>
    <p style="color: green;">Settings updated successfully!</p>
<?php endif; ?>

<form method="POST" action="settings.php">
    <label for="max_loss">Max Loss:</label>
    <input type="number" step="0.01" name="max_loss" id="max_loss" value="<?php echo htmlspecialchars($max_loss); ?>" placeholder="Max Loss" required><br>
    
    <label for="profit_target">Profit Target:</label>
    <input type="number" step="0.01" name="profit_target" id="profit_target" value="<?php echo htmlspecialchars($profit_target); ?>" placeholder="Profit Target" required><br>
    
    <label for="company_balance">Company Balance:</label>
    <input type="number" step="0.01" name="company_balance" id="company_balance" value="<?php echo htmlspecialchars($company_balance); ?>" placeholder="Company Balance" required><br>
    
    <button type="submit">Save Settings</button>
</form>

<?php include '../includes/footer.php'; ?>
