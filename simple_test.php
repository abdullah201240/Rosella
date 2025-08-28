<?php
/**
 * Simple SSLCommerz Test - No Database Required
 */

echo "<h1>SSLCommerz Integration Test</h1>";

// Test 1: Check if SSLCommerz class file exists
if (file_exists('includes/SSLCommerz.php')) {
    echo "<p style='color: green;'>✓ SSLCommerz.php file exists</p>";
} else {
    echo "<p style='color: red;'>✗ SSLCommerz.php file missing</p>";
    exit;
}

// Test 2: Try to include the class
try {
    require_once 'includes/SSLCommerz.php';
    echo "<p style='color: green;'>✓ SSLCommerz class included successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error including SSLCommerz class: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Try to instantiate the class
try {
    $sslcommerz = new SSLCommerz();
    echo "<p style='color: green;'>✓ SSLCommerz class instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error creating SSLCommerz instance: " . $e->getMessage() . "</p>";
    exit;
}

// Test 4: Test transaction ID generation
try {
    $tran_id = $sslcommerz->generateTranId();
    echo "<p style='color: green;'>✓ Transaction ID generated: " . $tran_id . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error generating transaction ID: " . $e->getMessage() . "</p>";
    exit;
}

// Test 5: Test payment session creation
try {
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
    
    echo "<p>Testing payment session creation...</p>";
    $response = $sslcommerz->createSession($order_data);
    
    if ($response && isset($response['GatewayPageURL'])) {
        echo "<p style='color: green;'>✓ Payment session created successfully!</p>";
        echo "<p><strong>Gateway URL:</strong> " . $response['GatewayPageURL'] . "</p>";
        echo "<p><strong>Session Key:</strong> " . $response['sessionkey'] . "</p>";
        
        // Create payment button
        echo "<h2>🚀 Test Payment Button</h2>";
        echo "<form action='" . $response['GatewayPageURL'] . "' method='POST'>";
        echo "<input type='hidden' name='sessionkey' value='" . $response['sessionkey'] . "'>";
        echo "<button type='submit' style='background: #007bff; color: white; padding: 20px 40px; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer;'>💳 Proceed to SSLCommerz Payment Gateway</button>";
        echo "</form>";
        
        echo "<p><strong>Note:</strong> This will redirect you to SSLCommerz sandbox. Use test cards:</p>";
        echo "<ul>";
        echo "<li><strong>Visa:</strong> 4111111111111111</li>";
        echo "<li><strong>Mastercard:</strong> 5555555555554444</li>";
        echo "<li><strong>Expiry:</strong> Any future date</li>";
        echo "<li><strong>CVV:</strong> Any 3 digits</li>";
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to create payment session</p>";
        if (is_array($response)) {
            echo "<pre>Response: " . print_r($response, true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error creating payment session: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Configuration Summary</h2>";
echo "<p><strong>Store ID:</strong> sakib67396cbb1abd3</p>";
echo "<p><strong>Sandbox Mode:</strong> Enabled</p>";
echo "<p><strong>API URL:</strong> https://sandbox.sslcommerz.com</p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Click the payment button above to test SSLCommerz integration</li>";
echo "<li>Complete a test payment on the sandbox</li>";
echo "<li>Check if you're redirected back to your success/fail pages</li>";
echo "<li>If successful, test the full checkout flow</li>";
echo "</ol>";

echo "<p><strong>Important:</strong> Make sure your XAMPP MySQL service is running for the full checkout process.</p>";
?>
