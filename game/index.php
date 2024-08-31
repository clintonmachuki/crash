<?php include '../includes/header.php'; ?>
<?php include '../includes/db.php'; ?>

<!-- Crash Game -->
<h2>Crash Game</h2>
<p id="multiplier">1.00x</p>
<form id="betForm" method="POST">
    <input type="number" step="0.01" name="bet_amount" placeholder="Bet Amount"><br>
    <button type="submit">Place Bet</button>
</form>
<p>Your Balance: <?php echo $conn->query("SELECT balance FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc()['balance']; ?></p>

<?php include '../includes/footer.php'; ?>
