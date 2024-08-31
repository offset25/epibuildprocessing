<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the path to your WordPress installation
$wp_load_path = '../../../wp-load.php';

// Include WordPress
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Could not find wp-load.php. Please check the path.');
}

print "blah\n";


include("./epibuild-payment-processor.php");

print "blah\n";
// Test data
$amount = 5.00; // Amount to be charged
$cc_name = "John Doe"; // Cardholder's name
$cc_number = "4111111111111111"; // Test card number (Visa)
$cc_exp = "12/25"; // Expiration date
$cc_code = "123"; // CVV code
$customer = [
    'address' => '123 Main Street',
    'city' => 'Townsville',
    'state' => 'NY',
    'zip' => '12345',
    'country' => 'USA'
];
print "balbhalbha\n";

// Sample test data
$amount = 5.00;
$cc_name = "John Doe";
$cc_number = "4111111111111111"; // Test Visa card number
$cc_exp = "2025-12"; // Expiration date (YYYY-MM format)
$cc_code = "123"; // CVV code
$customer = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'street_address' => '123 Main Street',
    'city' => 'Townsville',
    'state' => 'NY',
    'zip_code' => '12345',
];

$customer = new stdClass();
$customer->first_name = 'John';
$customer->last_name = 'Doe';
$customer->street_address = '123 Main Street';
$customer->city = 'Townsville';
$customer->state = 'NY';
$customer->zip_code = '12345';
//print_r($customer);

// Call the function with test data
$response = authorizeChargeCreditCard($amount, $cc_name, $cc_number, $cc_exp, $cc_code, $customer);

// Handle the response
if ($response != null) {
    if ($response->getMessages()->getResultCode() == "Ok") {
        $tresponse = $response->getTransactionResponse();
        if ($tresponse != null && $tresponse->getMessages() != null) {
            echo "Transaction Successful!\n";
            echo "Transaction ID: " . $tresponse->getTransId() . "\n";
            echo "Response Code: " . $tresponse->getResponseCode() . "\n";
            echo "Auth Code: " . $tresponse->getAuthCode() . "\n";
            echo "Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
        } else {
            echo "Transaction Failed\n";
            if ($tresponse->getErrors() != null) {
                echo "Error Code: " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                echo "Error Message: " . $tresponse->getErrors()[0]->getErrorText() . "\n";
            }
        }
    } else {
        echo "Transaction Failed\n";
        $errorMessages = $response->getMessages()->getMessage();
        echo "Error Code: " . $errorMessages[0]->getCode() . "\n";
        echo "Error Message: " . $errorMessages[0]->getText() . "\n";
    }
} else {
    echo "No response returned\n";
}

//$epibuildPaymentProcessing = new EpibuildPaymentProcessing();
// Call the function with test data
//$response = authorizeChargeCreditCard($amount, $cc_name, $cc_number, $cc_exp, $cc_code, $customer);
//print_r($response);
?>
