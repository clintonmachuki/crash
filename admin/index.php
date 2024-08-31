<?php
include '../includes/admin_header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Admin Dashboard</h2>

<div class="admin-stats">
    <p>Total Users: <span id="total_users">Loading...</span></p>
    <p>Total Bets: <span id="total_bets">Loading...</span> USD</p>
    <p>Max Loss: <span id="max_loss">Loading...</span> USD</p>
    <p>Profit Target: <span id="profit_target">Loading...</span> USD</p>
    <p>Company Balance: <span id="company_balance">Loading...</span> USD</p>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function fetchAdminStats() {
        $.ajax({
            url: '../admin/get_admin_stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                $('#total_users').text(data.total_users);
                $('#total_bets').text(data.total_bets);
                $('#max_loss').text(data.max_loss);
                $('#profit_target').text(data.profit_target);
                $('#company_balance').text(data.company_balance);
                
                // Check if profit target has been achieved
                if (data.profit_achieved) {
                    $('#profit_status').text('Profit target achieved!');
                } else {
                    $('#profit_status').text('Profit target not achieved.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + error);
            }
        });
    }

    // Fetch the stats initially
    fetchAdminStats();

    // Set an interval to fetch stats every 4 seconds
    setInterval(fetchAdminStats, 4000);
</script>

<?php include '../includes/footer.php'; ?>
