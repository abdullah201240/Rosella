<?php
/**
 * Simple SSLCommerz Test
 */

// Include SSLCommerz class
require_once 'includes/SSLCommerz.php';

echo "<h1>SSLCommerz Payment Test</h1>";

try {
    // Test SSLCommerz class
    $sslcommerz = new SSLCommerz();
    echo "<p style='color: green;'>✓ SSLCommerz class loaded successfully</p>";
    
    // Test transaction ID generation
    $tran_id = $sslcommerz->generateTranId();
    echo "<p><strong>Generated Transaction ID:</strong> " . $tran_id . "</p>";
    
    // Test payment session creation with sample data
    $order_data = [
        'total_amount' => 100.00,
        'tran_id' => $tran_id,
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'customer_address' => 'Test Address',
        'customer_address2' => '',
        'customer_city' => 'Test City',
        'customer_state' => 'Test State',
        'customer_postcode' => '12345',
        'customer_country' => 'Test Country',
        'customer_phone' => '1234567890',
        'order_id' => '123',
        'user_id' => '1',
        'session_id' => 'test_session'
    ];
    
    echo "<h2>Testing Payment Session Creation</h2>";
    $response = $sslcommerz->createSession($order_data);
    
    if ($response && isset($response['GatewayPageURL'])) {
        echo "<p style='color: green;'>✓ Payment session created successfully!</p>";
        echo "<p><strong>Gateway URL:</strong> " . $response['GatewayPageURL'] . "</p>";
        echo "<p><strong>Session Key:</strong> " . $response['sessionkey'] . "</p>";
        
        // Create a test payment button
        echo "<h2>Test Payment</h2>";
        echo "<form action='" . $response['GatewayPageURL'] . "' method='POST'>";
        echo "<input type='hidden' name='sessionkey' value='" . $response['sessionkey'] . "'>";
        echo "<button type='submit' style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;'>Proceed to Payment Gateway</button>";
        echo "</form>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to create payment session</p>";
        if (is_array($response)) {
            echo "<pre>Response: " . print_r($response, true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Configuration Details</h2>";
echo "<p><strong>Store ID:</strong> sakib67396cbb1abd3</p>";
echo "<p><strong>Sandbox Mode:</strong> Enabled</p>";
echo "<p><strong>API URL:</strong> https://sandbox.sslcommerz.com</p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Test the payment button above</li>";
echo "<li>Complete a test payment on SSLCommerz sandbox</li>";
echo "<li>Check if you're redirected back to success/fail page</li>";
echo "<li>Verify the payment flow in your checkout process</li>";
echo "</ol>";
?>
