<?php
	//require 'sdk-php-master/autoload.php';  // Composer autoloader
	require 'vendor/autoload.php';  // Composer autoloader

	use net\authorize\api\contract\v1 as AnetAPI;
	use net\authorize\api\controller as AnetController;

	$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
	$MERCHANT_LOGIN_ID = "5KP3u95bQpv";
	$MERCHANT_TRANSACTION_KEY = "346HZ32z3fP4hTG2";
	    $merchantAuthentication->setName($MERCHANT_LOGIN_ID);
	    $merchantAuthentication->setTransactionKey($MERCHANT_TRANSACTION_KEY);
	    //$merchantAuthentication->setName('bizdev05');
	    //$merchantAuthentication->setTransactionKey('4kJd237rZu59qAZd');

	$refId = 'ref' . time();

	// Create a transaction request
	$transactionRequestType = new AnetAPI\TransactionRequestType();
	$transactionRequestType->setTransactionType("authCaptureTransaction");
	$transactionRequestType->setAmount(123.45);

	$paymentType = new AnetAPI\PaymentType();
	$creditCard = new AnetAPI\CreditCardType();
	$creditCard->setCardNumber("4111111111111111");
	$creditCard->setExpirationDate("1225");
	$paymentType->setCreditCard($creditCard);
	$transactionRequestType->setPayment($paymentType);

	$request = new AnetAPI\CreateTransactionRequest();
	$request->setMerchantAuthentication($merchantAuthentication);
	$request->setRefId($refId);
	$request->setTransactionRequest($transactionRequestType);

	$controller = new AnetController\CreateTransactionController($request);
	$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
	//$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);

	if ($response != null) {
	    if ($response->getMessages()->getResultCode() == "Ok") {
		$tresponse = $response->getTransactionResponse();
		if ($tresponse != null && $tresponse->getMessages() != null) {
		    echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
		    echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
		} else {
		    echo "Transaction Failed\n";
		    if ($tresponse != null && $tresponse->getErrors() != null) {
			echo " Error Code: " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
			echo " Error Message: " . $tresponse->getErrors()[0]->getErrorText() . "\n";
		    }
		}
	    } else {
		echo "Transaction Failed\n";
		$tresponse = $response->getTransactionResponse();
		if ($tresponse != null && $tresponse->getErrors() != null) {
		    echo " Error Code: " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
		    echo " Error Message: " . $tresponse->getErrors()[0]->getErrorText() . "\n";
		} else {
		    echo " Error Code: " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
		    echo " Error Message: " . $response->getMessages()->getMessage()[0]->getText() . "\n";
		}
	    }
	} else {
	    echo "No response returned\n";
	}
	if ($response != null) {
	    if ($response->getMessages()->getResultCode() == "Ok") {
		$tresponse = $response->getTransactionResponse();
		if ($tresponse != null && $tresponse->getMessages() != null) {
		    echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
		    echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
		} else {
		    echo "Transaction Failed\n";
		}
	    } else {
		echo "Transaction Failed\n";
	    }
	} else {
	    echo "No response returned\n";
	}

?>
