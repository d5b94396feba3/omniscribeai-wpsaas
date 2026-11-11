<?php
namespace PaddleToMycred;

defined('ABSPATH') || exit;

class Mycred_Handler {
    private $user_id;
    private $last_transaction = null;
    
    public function __construct() {
        $this->user_id = get_current_user_id();
    }

    public function calculate_cost($mode, $output_type, $iterations) {
        // Base cost per iteration
        $cost_per_iteration = 100;
        
        // Total cost based on iterations
        $cost = $cost_per_iteration * $iterations;
        
        // Additional cost for Elementor JSON output
        // if ($output_type === 'elementor') {
        //     $cost += 1; 
        // }
        
        return max(1, $cost); // Ensure minimum 1 point
    }

    public function has_sufficient_balance($cost) {
        if (!function_exists('mycred_get_users_balance')) {
            error_log('myCred functions not available');
            return false;
        }
        
        $balance = mycred_get_users_balance($this->user_id);
        error_log("User balance: {$balance}, Required: {$cost}");
        
        return ($balance >= $cost);
    }

    /**
     * Gets the user's myCred balance and rank name.
     * @return array|false
     */
    public function mycred_balance_with_rank() {
        if (!function_exists('mycred_get_users_balance') || $this->user_id === 0) {
            error_log('myCred functions not available or User ID is zero.');
            return false;
        }

        $balance = mycred_get_users_balance($this->user_id);
        $rank = mycred_get_users_rank($this->user_id); // This gets the rank object

        $rank_name = 'Member'; // Default rank name

        // Check if a valid rank object was returned
        if (is_object($rank) && isset($rank->title)) {
           
            $rank_name = $rank->title;

        } else {
             error_log("myCred Handler: No specific rank found for user #{$this->user_id}. Using default.");
        }

        return [
            'balance'   => $balance,
            'rank_name' => $rank_name,
        ];
    }

    public function deduct_points($cost, $mode, $output_type, $iterations) {
        if (!function_exists('mycred_subtract')) {
            error_log('myCred subtract function not available');
            return false;
        }
        
        $entry = sprintf(
            __('Point deduction for %s conversion to %s with %d iterations', 'figma-to-elementor'),
            $mode,
            $output_type,
            $iterations
        );
        
        $this->last_transaction = (object) [
            'amount' => $cost,
            'data' => [
                'mode' => $mode,
                'output_type' => $output_type,
                'iterations' => $iterations
            ]
        ];
        
        $transaction_id = mycred_subtract(
            'figma_conversion', 
            $this->user_id, 
            $cost, 
            $entry, 
            '', 
            '', 
            time()
        );
        
        // Verify deduction was successful
        if ($transaction_id === false) {
            error_log('Failed to deduct points');
            $this->last_transaction = null;
            return false;
        }
        
        $this->last_transaction->id = $transaction_id;
        error_log("Successfully deducted {$cost} points. Transaction ID: {$transaction_id}");
        return true;
    }

    public function refund_points($reason = '') {
        if (!$this->last_transaction || !function_exists('mycred_add')) {
            return false;
        }
        
        $refund_entry = __('Refund for failed generation', 'figma-to-elementor');
        if ($reason) {
            $refund_entry .= ': ' . $reason;
        }
        
        $refund_data = [
            'original_transaction' => $this->last_transaction->id,
            'original_data' => $this->last_transaction->data
        ];
        
        return mycred_add(
            'figma_refund',
            $this->user_id,
            $this->last_transaction->amount,
            $refund_entry,
            '',
            $refund_data,
            time()
        );
    }

    /**
     * Grants myCRED points to a user upon a successful Paddle purchase.
     *
     * @param int    $user_id The WordPress user ID to grant points to.
     * @param float  $amount  The number of points to add.
     * @param string $reason  The package name/reason for the points.
     * @param array  $transaction_details Details from the Paddle transaction.
     * @return mixed Transaction ID on success, false on failure.
     */
    public function grant_points_on_paddle_purchase($user_id, $amount, $reason = '', $transaction_details = []) {
        if (!function_exists('mycred_add')) {
            error_log('myCred add function not available for granting points. Ensure myCRED plugin is active.');
            return false;
        }
        
        $entry = sprintf(
            __('Points added for %s upgrade', 'paddle-custom-integration'),
            $reason ?: 'Package purchase'
        );
        
        $log_data = [
            'paddle_transaction_id' => $transaction_details['id'] ?? null,
            'paddle_price_id' => $transaction_details['price_id'] ?? null,
            'package_name' => $transaction_details['package_name'] ?? $reason,
            'points_granted' => $amount,
            'quantity' => $transaction_details['quantity'] ?? 1
        ];
        
        // Add reference to mycred_rank update
        $log_data['mycred_rank_updated'] = false;
        
        $transaction_id = mycred_add(
            'paddle_purchase_points',
            $user_id,
            $amount,
            $entry,
            '', // Reference
            $log_data,
            time()
        );
        
        if ($transaction_id === false) {
            error_log("Failed to add {$amount} points for user {$user_id} for package: {$reason}");
            
        } else {
            error_log("Successfully added {$amount} points for user {$user_id} for package: {$reason}");

        }
        
        return $transaction_id;
    }
}