<?php
include '../includes/admin_header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Query to get user details
$users = $conn->query("
    SELECT 
        u.id, 
        u.username, 
        u.balance,
        COALESCE(SUM(gh.bet_amount), 0) AS total_gambled,
        COALESCE(SUM(gh.payout - gh.bet_amount), 0) AS pnl,
        u.is_disabled
    FROM users u
    LEFT JOIN game_history gh ON u.id = gh.user_id
    GROUP BY u.id
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $conn->query("DELETE FROM users WHERE id = $user_id");
        header("Location: users.php?deleted=1");
        exit;
    }
    
    if (isset($_POST['toggle_disable'])) {
        $user_id = $_POST['user_id'];
        $current_status = $_POST['current_status'];
        $new_status = $current_status == 0 ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET is_disabled = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: users.php?status_updated=1");
        exit;
    }
}
?>

<h2>Manage Users</h2>

<?php if (isset($_GET['deleted'])): ?>
    <p style="color: green;">User deleted successfully!</p>
<?php endif; ?>

<?php if (isset($_GET['status_updated'])): ?>
    <p style="color: green;">User status updated successfully!</p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Balance</th>
            <th>Total Gambled</th>
            <th>Profit/Loss</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo number_format($user['balance'], 2); ?> USD</td>
            <td><?php echo number_format($user['total_gambled'], 2); ?> USD</td>
            <td>
                <?php
                // Display profit or loss with + and - signs
                $pnl = $user['pnl'];
                if ($pnl >= 0) {
                    echo '<span style="color: green;">+' . number_format($pnl, 2) . ' USD</span>';
                } else {
                    echo '<span style="color: red;">' . number_format($pnl, 2) . ' USD</span>';
                }
                ?>
            </td>
            <td>
                <?php
                if ($user['is_disabled']) {
                    echo '<span style="color: red;">Disabled</span>';
                } else {
                    echo '<span style="color: green;">Active</span>';
                }
                ?>
            </td>
            <td>
                <form method="POST" action="users.php" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <input type="hidden" name="current_status" value="<?php echo $user['is_disabled']; ?>">
                    <button type="submit" name="toggle_disable">
                        <?php echo $user['is_disabled'] ? 'Enable' : 'Disable'; ?>
                    </button>
                </form>
                
                <form method="POST" action="users.php" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" name="delete_user">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
