<?php
namespace FigmaToElementor;

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
}