<?php
/**
 * SSLCommerz Payment Gateway Integration
 * Handles payment processing through SSLCommerz
 */

class SSLCommerz {
    private $store_id;
    private $store_password;
    private $is_sandbox;
    private $api_url;
    
    public function __construct() {
        // SSLCommerz configuration - Update these values with your actual credentials
        $this->store_id = 'sakib67396cbb1abd3';
        $this->store_password = 'sakib67396cbb1abd3@ssl';
        $this->is_sandbox = true; // Set to false for production
        
        if ($this->is_sandbox) {
            $this->api_url = 'https://sandbox.sslcommerz.com';
        } else {
            $this->api_url = 'https://securepay.sslcommerz.com';
        }
    }
    
    /**
     * Create payment session
     */
    public function createSession($order_data) {
        $post_data = array();
        $post_data['store_id'] = $this->store_id;
        $post_data['store_passwd'] = $this->store_password;
        $post_data['total_amount'] = $order_data['total_amount'];
        $post_data['currency'] = 'BDT';
        $post_data['tran_id'] = $order_data['tran_id'];
        $post_data['product_category'] = 'Clothing';
        $post_data['product_name'] = 'Test Product'; // Required parameter - product name
        $post_data['product_profile'] = 'physical-goods'; // Required parameter - product profile type
        $post_data['shipping_method'] = 'NO'; // Required parameter - NO means no shipping needed
        // Update these URLs to match your domain
        $post_data['success_url'] = 'http://localhost/rosella/user/payment_success.php';
        $post_data['fail_url'] = 'http://localhost/rosella/user/payment_fail.php';
        $post_data['cancel_url'] = 'http://localhost/rosella/user/payment_cancel.php';
        $post_data['ipn_url'] = 'http://localhost/rosella/user/payment_ipn.php';
        
        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $order_data['customer_name'];
        $post_data['cus_email'] = $order_data['customer_email'];
        $post_data['cus_add1'] = $order_data['customer_address'];
        $post_data['cus_add2'] = $order_data['customer_address2'] ?? '';
        $post_data['cus_city'] = $order_data['customer_city'];
        $post_data['cus_state'] = $order_data['customer_state'];
        $post_data['cus_postcode'] = $order_data['customer_postcode'];
        $post_data['cus_country'] = $order_data['customer_country'];
        $post_data['cus_phone'] = $order_data['customer_phone'];
        
        # SHIPMENT INFORMATION
        $post_data['ship_name'] = $order_data['customer_name'];
        $post_data['ship_add1'] = $order_data['customer_address'];
        $post_data['ship_add2'] = $order_data['customer_address2'] ?? '';
        $post_data['ship_city'] = $order_data['customer_city'];
        $post_data['ship_state'] = $order_data['customer_state'];
        $post_data['ship_postcode'] = $order_data['customer_postcode'];
        $post_data['ship_country'] = $order_data['customer_country'];
        
        # OPTIONAL PARAMETERS
        $post_data['value_a'] = $order_data['order_id'];
        $post_data['value_b'] = $order_data['user_id'] ?? '';
        $post_data['value_c'] = $order_data['session_id'] ?? '';
        
        $direct_api_url = $this->api_url . "/gwprocess/v4/api.php";
        
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        
        $content = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($handle);
        
        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close($handle);
            error_log("SSLCommerz cURL failed - HTTP Code: " . $code . ", Error: " . $curl_error);
            return false;
        }
        
        $sslcz = json_decode($sslcommerzResponse, true);
        
        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            return $sslcz;
        } else {
            error_log("SSLCommerz: GatewayPageURL not found in response");
            return false;
        }
    }
    
    /**
     * Validate payment response
     */
    public function validateResponse($post_data) {
        // Check if required fields exist, use null coalescing for safety
        $val_id = $post_data['val_id'] ?? null;
        $amount = $post_data['amount'] ?? null;
        $currency = $post_data['currency'] ?? null;
        $tran_id = $post_data['tran_id'] ?? null;
        $store_amount = $post_data['store_amount'] ?? null;
        $store_id = $post_data['store_id'] ?? null;
        $tran_date = $post_data['tran_date'] ?? null;
        $status = $post_data['status'] ?? null;
        $card_type = $post_data['card_type'] ?? null;
        $card_no = $post_data['card_no'] ?? null;
        $error = $post_data['error'] ?? null;
        $card_issuer = $post_data['card_issuer'] ?? null;
        $card_brand = $post_data['card_brand'] ?? null;
        $card_sub_brand = $post_data['card_sub_brand'] ?? null;
        $card_issuer_country = $post_data['card_issuer_country'] ?? null;
        $card_issuer_country_code = $post_data['card_issuer_country_code'] ?? null;
        $verify_sign = $post_data['verify_sign'] ?? null;
        $verify_key = $post_data['verify_key'] ?? null;
        $base64_verify_sign = $post_data['base64_verify_sign'] ?? null;
        
        // Log the received data for debugging
        error_log("SSLCommerz Response Data: " . print_r($post_data, true));
        
        // Check if this is a success or failure response
        if (isset($post_data['status']) && $post_data['status'] === 'VALID') {
            // Success response - validate hash if available
            if ($verify_sign && $base64_verify_sign) {
                if ($verify_sign == $base64_verify_sign) {
                    return [
                        'status' => 'success',
                        'tran_id' => $tran_id,
                        'val_id' => $val_id,
                        'amount' => $amount,
                        'currency' => $currency,
                        'card_type' => $card_type,
                        'card_no' => $card_no,
                        'card_issuer' => $card_issuer,
                        'card_brand' => $card_brand,
                        'tran_date' => $tran_date
                    ];
                } else {
                    return [
                        'status' => 'failed',
                        'message' => 'Hash verification failed'
                    ];
                }
            } else {
                // Hash verification not available, but status is VALID
                return [
                    'status' => 'success',
                    'tran_id' => $tran_id,
                    'val_id' => $val_id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'card_type' => $card_type,
                    'card_no' => $card_no,
                    'card_issuer' => $card_issuer,
                    'card_brand' => $card_brand,
                    'tran_date' => $tran_date
                ];
            }
        } elseif (isset($post_data['status']) && $post_data['status'] === 'FAILED') {
            // Failed response
            return [
                'status' => 'failed',
                'message' => $error ?? 'Payment failed'
            ];
        } elseif (isset($post_data['status']) && $post_data['status'] === 'CANCELLED') {
            // Cancelled response
            return [
                'status' => 'cancelled',
                'message' => 'Payment was cancelled'
            ];
        } else {
            // Unknown response status
            return [
                'status' => 'unknown',
                'message' => 'Unknown payment response status'
            ];
        }
    }
    
    /**
     * Generate unique transaction ID
     */
    public function generateTranId() {
        return 'TXN_' . time() . '_' . rand(1000, 9999);
    }
    
    /**
     * Get payment status
     */
    public function getPaymentStatus($val_id) {
        $post_data = array();
        $post_data['store_id'] = $this->store_id;
        $post_data['store_passwd'] = $this->store_password;
        $post_data['val_id'] = $val_id;
        
        $direct_api_url = $this->api_url . "/validator/api/merchantTransIDvalidationAPI.php";
        
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        
        $content = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            return json_decode($content, true);
        } else {
            curl_close($handle);
            return false;
        }
    }
}
?>
