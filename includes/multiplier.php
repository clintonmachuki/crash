<?php
/**
 * Function to generate a valid multiplier based on company settings and user balance.
 *
 * @param float $bet_amount The amount the user is betting.
 * @param float $current_balance The company's current balance.
 * @param float $current_loss The company's current loss.
 * @param float $max_loss The maximum loss the company can afford.
 * @param float $profit_target The profit target for the company.
 * @param float $user_balance The user's current balance.
 * @return float The generated multiplier.
 */
function generate_valid_multiplier($bet_amount, $current_balance, $current_loss, $max_loss, $profit_target, $user_balance) {
    // Define multiplier range
    $min_multiplier = 1.00;
    $max_multiplier = 50.00; // Increased maximum multiplier for better user experience

    // Calculate maximum valid multiplier to avoid exceeding company loss limits
    $max_valid_multiplier = ($current_balance - $max_loss) / $bet_amount;
    if ($max_valid_multiplier < $min_multiplier) {
        $max_valid_multiplier = $min_multiplier;
    }
    
    // Adjust multiplier range based on company performance
    $loss_ratio = ($current_loss / $max_loss);
    $adjusted_max_multiplier = $max_multiplier * (1 - $loss_ratio);
    $adjusted_max_multiplier = max($adjusted_max_multiplier, $min_multiplier);

    // Generate a base multiplier with weighted randomness
    $base_multiplier = mt_rand($min_multiplier * 100, min($adjusted_max_multiplier * 100, $max_valid_multiplier * 100)) / 100;
    
    // Apply a probability distribution for multipliers
    // Higher multipliers are less frequent
    $random_factor = mt_rand(1, 100) / 100;
    if ($random_factor < 0.65) { // 65% chance for a lower multiplier
        $multiplier = $base_multiplier < 2 ? $base_multiplier : mt_rand(100, 200) / 100;
    } else { // 35% chance for a higher multiplier
        $multiplier = $base_multiplier > 2 ? $base_multiplier : mt_rand(200, min($adjusted_max_multiplier * 100, 500)) / 100;
    }

    // Ensure the multiplier does not exceed the allowed maximum
    $multiplier = min($multiplier, $max_valid_multiplier);
    $multiplier = max($multiplier, $min_multiplier);

    // Adjust multiplier based on user balance
    if ($user_balance < 10) {
        // If user balance is less than $10, give a higher chance to win
        $win_chance = 0.80; // Increased chance to win for low balance
        if (mt_rand() / mt_getrandmax() <= $win_chance) {
            // Ensure the multiplier is adjusted to favor the user
            $multiplier = max($multiplier, $bet_amount * 2); // Ensure the multiplier is at least 2x bet amount for a win
        }
    }

    return $multiplier;
}
?>
