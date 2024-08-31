<?php

require 'vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

// Common setup for API credentials
$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
$merchantAuthentication->setName('bizdev05');
$merchantAuthentication->setTransactionKey('4kJd237rZu59qAZd');

$refId = 'ref' . time();

// Create the payment data for a credit card
$creditCard = new AnetAPI\CreditCardType();
$creditCard->setCardNumber("4111111111111111");
$creditCard->setExpirationDate("12/25");
$creditCard->setCardCode("123");

$paymentOne = new AnetAPI\PaymentType();
$paymentOne->setCreditCard($creditCard);

// Create a transaction request
$transactionRequestType = new AnetAPI\TransactionRequestType();
$transactionRequestType->setTransactionType("authCaptureTransaction");
$transactionRequestType->setAmount(5.00);
$transactionRequestType->setPayment($paymentOne);

$request = new AnetAPI\CreateTransactionRequest();
$request->setMerchantAuthentication($merchantAuthentication);
$request->setRefId($refId);
$request->setTransactionRequest($transactionRequestType);

$controller = new AnetController\CreateTransactionController($request);

try {
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

    if ($response != null) {
        if ($response->getMessages()->getResultCode() == "Ok") {
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getMessages() != null) {
                echo "Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
                echo "Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
                echo "Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
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
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    // You can also log the exception details to a file for further analysis
    error_log($e->getMessage(), 3, '/path/to/your/error.log');
}
?>

