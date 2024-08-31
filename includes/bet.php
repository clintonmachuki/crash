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
                // Generate a valid multiplier
                $actual_multiplier = generate_valid_multiplier($bet_amount, $current_balance, $current_loss, $max_loss, $profit_target, $balance);
                
                // Deduct bet amount from user balance
                $new_balance = $balance - $bet_amount;
                
                if ($stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?")) {
                    $stmt->bind_param("di", $new_balance, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Determine payout or loss
                    if ($actual_multiplier >= $predicted_multiplier) {
                        // User wins
                        $payout = $bet_amount * $predicted_multiplier;
                        $new_balance += $payout; // Add payout to balance
                        
                        // Update game settings
                        $new_game_balance = $current_balance - $bet_amount + $payout;
                        $new_current_loss = $new_game_balance < 0 ? abs($new_game_balance) : 0;
                        
                        if ($new_current_loss > $max_loss) {
                            echo json_encode(['error' => 'Game over: maximum loss exceeded.']);
                            exit;
                        } else {
                            if ($stmt = $conn->prepare("UPDATE game_settings SET balance = ?, current_loss = ? WHERE id = 1")) {
                                $stmt->bind_param("dd", $new_game_balance, $new_current_loss);
                                $stmt->execute();
                                $stmt->close();
                            } else {
                                echo json_encode(['error' => 'Error updating game settings.']);
                                exit;
                            }
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

                        echo json_encode([
                            'success' => true,
                            'message' => "Congratulations! You predicted correctly. Predicted Multiplier: " . number_format($predicted_multiplier, 2) . ". Multiplier was " . number_format($actual_multiplier, 2) . ". You have won " . number_format($predicted_multiplier * $bet_amount, 2) . " USD."

                        ]);
                    } else {
                        // User loses
                        // Record the bet in the game history with 0 payout
                        $payout = 0;
                        $new_balance = $balance - $bet_amount; // Deduct bet amount from balance
                        
                        // Update game settings
                        $new_game_balance = $current_balance - $bet_amount;
                        $new_current_loss = $new_game_balance < 0 ? abs($new_game_balance) : 0;
                        
                        if ($new_current_loss > $max_loss) {
                            echo json_encode(['error' => 'Game over: maximum loss exceeded.']);
                            exit;
                        } else {
                            if ($stmt = $conn->prepare("UPDATE game_settings SET balance = ?, current_loss = ? WHERE id = 1")) {
                                $stmt->bind_param("dd", $new_game_balance, $new_current_loss);
                                $stmt->execute();
                                $stmt->close();
                            } else {
                                echo json_encode(['error' => 'Error updating game settings.']);
                                exit;
                            }
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

                        echo json_encode([
                            'success' => true,
                            'message' => "Sorry, you predicted too high. Multiplier was " . number_format($actual_multiplier, 2) . ". Your new balance is $" . number_format($new_balance, 2)
                        ]);
                    }
                } else {
                    echo json_encode(['error' => 'Error updating balance.']);
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
