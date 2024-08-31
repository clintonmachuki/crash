<?php
session_start(); // Ensure this is at the very top of the file

include '../includes/db.php';  // Database connection
include '../includes/multiplier.php'; // Include the multiplier generation function

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch game settings
if ($stmt = $conn->prepare("SELECT max_loss, profit_target, balance, current_loss FROM game_settings WHERE id = 1")) {
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($max_loss, $profit_target, $current_balance, $current_loss);
    $stmt->fetch();
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error fetching game settings.']);
    exit;
}

// Initialize the game counter (this should be persistent between requests)
if (!isset($_SESSION['game_counter'])) {
    $_SESSION['game_counter'] = 0;
}

// Handle bet submission via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bet_amount']) && isset($_POST['predicted_multiplier'])) {
    $bet_amount = $_POST['bet_amount'];
    $predicted_multiplier = $_POST['predicted_multiplier'];
    
    // Validate bet amount and predicted multiplier
    if (is_numeric($bet_amount) && $bet_amount > 0 && is_numeric($predicted_multiplier) && $predicted_multiplier > 0) {
        // Fetch user balance
        if ($stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?")) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($balance);
            $stmt->fetch();
            $stmt->close();
            
            // Check if the user has enough balance
            if ($bet_amount <= $balance) {
                // Call the generate_valid_multiplier function with the correct number of arguments
                $actual_multiplier = generate_valid_multiplier(
                    $bet_amount,         // Bet amount
                    $current_balance,    // Company's current balance
                    $current_loss,       // Company's current loss
                    $max_loss,           // Maximum allowable loss for the company
                    $profit_target,      // Profit target for the company
                    $balance,            // User's balance
                    $predicted_multiplier, // The multiplier predicted by the user
                    $_SESSION['game_counter'] // Pass the game counter by reference
                );

                // Increment the game counter
                $_SESSION['game_counter']++;
                
                // Deduct bet amount from user balance
                $new_balance = $balance - $bet_amount;
                
                // Check if the current loss would exceed the maximum loss
                $new_game_balance = $current_balance - $bet_amount;
                $new_current_loss = $new_game_balance < 0 ? abs($new_game_balance) : 0;

                if ($new_current_loss > $max_loss) {
                    // Force the multiplier to be less than predicted to ensure loss
                    $actual_multiplier = $predicted_multiplier - 0.01; // Ensure actual multiplier is lower
                    $payout = 0; // User loses
                    $new_balance = $balance - $bet_amount;
                    $company_profit = $current_balance + $bet_amount; // Add the loss to the company's balance
                } else {
                    // User wins
                    if ($actual_multiplier >= $predicted_multiplier) {
                        $payout = $bet_amount * $predicted_multiplier;
                        $new_balance += $payout;
                        $company_profit = $current_balance - ($payout - $bet_amount);
                    } else {
                        $payout = 0; // User loses
                        $company_profit = $current_balance + $bet_amount; // Add the loss to the company's balance
                    }
                }
                
                // Update user balance
                if ($stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?")) {
                    $stmt->bind_param("di", $new_balance, $user_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo json_encode(['error' => 'Error updating balance.']);
                    exit;
                }
                
                // Update game settings with new balance (company's profit)
                if ($stmt = $conn->prepare("UPDATE game_settings SET balance = ?, current_loss = ? WHERE id = 1")) {
                    $stmt->bind_param("dd", $company_profit, $new_current_loss);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo json_encode(['error' => 'Error updating game settings.']);
                    exit;
                }

                // Record the bet in the game history
                if ($stmt = $conn->prepare("INSERT INTO game_history (user_id, bet_amount, payout, multiplier, created_at) VALUES (?, ?, ?, ?, NOW())")) {
                    $stmt->bind_param("iddd", $user_id, $bet_amount, $payout, $actual_multiplier);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo json_encode(['error' => 'Error recording bet.']);
                    exit;
                }

                // Respond to the user based on the outcome
                if ($actual_multiplier >= $predicted_multiplier) {
                    echo json_encode([
                        'success' => true,
                        'message' => "Congratulations! You predicted correctly. Predicted Multiplier: " . number_format($predicted_multiplier, 2) . ". Multiplier was " . number_format($actual_multiplier, 2) . ". You have won " . number_format($payout, 2) . " USD."
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => "Sorry, you predicted too high. Multiplier was " . number_format($actual_multiplier, 2) . ". Your new balance is $" . number_format($new_balance, 2)
                    ]);
                }
            } else {
                echo json_encode(['error' => 'Insufficient balance.']);
            }
        } else {
            echo json_encode(['error' => 'Error fetching balance.']);
        }
    } else {
        echo json_encode(['error' => 'Invalid bet amount or predicted multiplier.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>
