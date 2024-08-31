<?php 
/**
 * Plugin Name:       EpiBuild Payment Processing
 * Plugin URI:        
 * Description:       Process payments for NSF
 * Version:           2.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Charles Lima, Alecia Clapp
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

require 'sdk-php-master/autoload.php';

$plugin_name = "EPI BUilD Payment Processing";
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;


  define("AUTHORIZENET_LOG_FILE", "phplog");

if (! defined('ABSPATH') ) {
    die;
}


class EpibuildPaymentProcessing {
    public $plugin_name = "test";

    function __construct() {
        add_action( 'init', array($this,'custom_post_type') );

        $this->plugin_name = plugin_basename( __FILE__ );
    }

    function register() {
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue') );

        add_action('admin_menu', array($this, 'add_admin_pages'));

        add_filter( "plugin_action_links_$this->plugin_name", array($this, 'settings_link'));
    }

    public function settings_link($links) {
        // add custom settings link
        $settings_link = '<a href="admin.php?page=epibuild_plugin">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }

    public function add_admin_pages() {
        add_menu_page( 'EpiBuild Payment Plugin', 'EpiBuild', 'manage_options', 'epibuild_plugin', array($this, 'admin_index'), 'dashicons-analytics', 110 );
        
    }

    public function admin_index() {
        // require template
        require_once plugin_dir_path( __FILE__ ) . 'templates/admin.php';
    }

    function activate() {
        $this->custom_post_type();
        
        flush_rewrite_rules();
    }

    function deactivate() {
        flush_rewrite_rules();
    }

    function uninstall() {

    }

    function custom_post_type() {
        register_post_type( 'payment_processor', ['public' => true, 'label' => 'Payment Processor'] );

    }

    function enqueue() {
        wp_enqueue_style( 'mypluginstyle', plugins_url('/assets', __FILE__) );
    }
}

if (class_exists('EpibuildPaymentProcessing')) {
    $epibuildPaymentProcessing = new EpibuildPaymentProcessing();
    $epibuildPaymentProcessing->register();
}

function testEcho($message)
{
    $show_test_echoes = false;

    if($show_test_echoes)
    {
        echo $message;
    }
}

function handleFailedTransaction($gw, $paysafeSaleTransactionId, $subscribeTransactionId)
{

    
    //response=1&responsetext=Transaction Void Successful
    $paysafeVoidResponse = $gw->doVoid($paysafeSaleTransactionId);
    $subscribeVoidResponse = $gw->doVoid($subscribeTransactionId);

    parse_str($paysafeVoidResponse, $output);
    $paysafeVoidResponseCode = $output['response'];
    $paysafeVoidResponseText = $output['responsetext'];
    $paysafeVoidTransactionId = $output['transactionid'];
    

    if(strcmp($paysafeVoidResponseCode, '1') == 0 && strcmp($paysafeVoidResponseText, 'Transaction Void Successful') == 0)
    {
        // Success - Do nothing. Just let the page redirect to failure page
        testEcho('PaySafe VOID Transaction Success');
        testEcho( '<br>');
    }
    else
    {
        // API Call Unsuccessfull
        // TODO: Send an email that we were not able to Void the transaction?
        testEcho('PaySafe VOID Transaction Failed');
        testEcho( '<br>');
    }
    testEcho($paysafeVoidResponseText);
    testEcho( '<br>');
 
    
    $fc_final_response['success'] = "Congratulations and Welcome to National Association of Family Services.<br>";
    $fc_final_response['success'] .= "You have taken the first steps in securing your family's legacy.<br>";
    $fc_final_response['success'] .= " <br>";
    $fc_final_response['success'] .= "Our association was formed to help families gain access to quality legal and financial services, but at affordable prices. With over 17,000 members, our association has been able to negotiate discounts with service providers such as law firms, notary, and financial services companies.<br>";
    $fc_final_response['success'] .= " ";
    $fc_final_response['success'] .= "NAFS also acts as an advocate for its members. We are here to make sure you are completely satisfied with the services you receive now and in the future. As you know we have designed a simple and easy process to obtain services from our providers, but if you need assistance of any kind, we’re here for you and just a phone call away:<br>";
    $fc_final_response['success'] .= " ";
    $fc_final_response['success'] .= "(800) 585-3550<br>";
    $fc_final_response['success'] .= "Do not be afraid to call us about anything!<br>";
    echo json_encode($fc_final_response);
    die();
    //$redirect = get_site_url() . '/application-and-payment-submitted//';
    //wp_redirect( $redirect);

   

}

//handle_payment_submission()
//{
    //PaySafeAPI.Sale()
    //IF SUCCESS
        //AuthorizeAPI.Sale();
            //IF SUCCESS - go to success page
            //IF FAIL - PaySafeAPI.Void() then Go to Failure Page
    //IF FAIL
        //Go to Failure Page
//}
function handle_payment_submission($credit_card_number, $authorize_exp, $paysafe_exp, $security_code, $email_address, $additional_properties_num, $full_name, $primary_phone_number, $customer, $url, $customer_email, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number) {
    
    
    /*
    date
    cc_name
    cc_number
    cc_exp
    cc_code
    */
    $cc_name = $full_name;
    // $date = $_POST['date'];
    $cc_number = $credit_card_number;
    // 2020-09
    $cc_exp = $authorize_exp;
    $cc_code = $security_code;
    $customer_email = $email_address;
    $customer_properties = $additional_properties_num;
    
    //$paysafe_exp = $paysafe_expiration_date;

    //testEcho($paysafe_exp);
    
    // PAYSAFE.SALE()
    $gw = new gwapi;
    // production key: 6D7H9shyxe5ZjXgB73T5vF6sqZaFhTD4
    // test key: 	6457Thfj624V5r7WUwc5v6a68Zsd6YEm
    /*
        test cards:
        Visa:	4111111111111111
        MasterCard:	5431111111111111
        Discover:	6011601160116611
        American Express:	341111111111111
        Diner's Club:	30205252489926
        JCB:	3541963594572595
        Maestro:	6799990100000000019
        Credit Card Expiration:	10/25
        account (ACH):	123123123
        routing (ACH):	123123123
    */
    // paysafe keys were here need to update once done.

    

    // Parse Paysafe Response which is a query string that looks like this: 
    //  response=1&responsetext=SUCCESS&authcode=123456&transactionid=5621491495&avsresponse=&cvvresponse=M
    //  &orderid=&type=sale&response_code=100&cc_number=4xxxxxxxxxxx1111&customer_vault_id= SUCCESS
    
    $paysafeSaleResponse = $gw->doSale("349",$cc_number,$paysafe_exp,$cc_code, $customer);
    
    parse_str($paysafeSaleResponse, $output);
    $paysafeSaleResponseCode = $output['response'];
    $paysafeSaleResponseText = $output['responsetext'];
    $paysafeSaleTransactionId = $output['transactionid'];

    if(strcmp($paysafeSaleResponseCode, '1') == 0)// && (strcmp($paysafeSaleResponseText, 'SUCCESS') == 0) || (strcmp($paysafeSaleResponseText, 'Approval') == 0))
    {
        testEcho('PaySafe SALE Transaction Success');
        testEcho('<br>');
        testEcho('<br>');
        $subscriber_response = $gw->doAddSubscriber($plan_id, $cc_number, $paysafe_exp, $cc_code);
        parse_str($subscriber_response, $subscribe_output);

        $subscribeResponseCode = $subscribe_output['response'];
        $subscribeResponseText = $subscribe_output['responsetext'];
        $subscribeTransactionId = $subscribe_output['transactionid'];
         // response=1&responsetext=Subscription added&authcode=&transactionid=5630139560&avsresponse=&cvvresponse=&orderid=&type=&response_code=100&cc_number=4xxxxxxxxxxx1111&customer_vault_id=
        if (strcmp($subscribeResponseCode, '1') == 0) { // && strcmp($subscribeResponseText, 'Subscription added') == 0) {
            // AUTHORIZE_NET.SALE()
            $additional_charge = 100 * $customer_properties;

            // To turn on additional property charges simply delete the semicolon and uncomment the below line.
            $total_lawyer_charge = 250; //+ $additional_charge;
            $authorizeNetResponse = authorizeChargeCreditCard($total_lawyer_charge, $cc_name, $cc_number, $cc_exp, $cc_code, $customer);
            // Success - go to success page
            // Fail - PAYSAFE.VOID(), reroute to Payment Failed page?

            if ($authorizeNetResponse != null) {
                // Check to see if the API request was successfully received and acted upon
                if ($authorizeNetResponse->getMessages()->getResultCode() == "Ok") 
                {
                    // Since the API request was successful, look for a transaction response
                    // and parse it to display the results of authorizing the card
                    $transactionResponse = $authorizeNetResponse->getTransactionResponse();
                    
                    if ($transactionResponse != null && $transactionResponse->getMessages() != null) 
                    {
                        testEcho( " Authorize.net Successfully created transaction with Transaction ID: " . $transactionResponse->getTransId()); 
                        testEcho( '<br>');
                        testEcho( " Transaction Response Code: " . $transactionResponse->getResponseCode()); 
                        testEcho( '<br>');
                        testEcho( " Message Code: " . $transactionResponse->getMessages()[0]->getCode()); 
                        testEcho( '<br>');
                        testEcho( " Auth Code: " . $transactionResponse->getAuthCode()); 
                        testEcho( '<br>');
                        testEcho( " Description: " . $transactionResponse->getMessages()[0]->getDescription()); 
                        testEcho( '<br>');

                        
                        // email to customer
                        //$to      = $customer_email;
                        //$subject = 'Membership Application and Plan Attorney Payment Confirmation';
                        
                        //$message = "Thank you for attending our webinar on how to obtain a living trust for $599. 
                        //\n\nThis is a confirmation of your recent payment. A charge of $349 was made to National Association of Family Services and a separate charge of \$" . 
                        //$total_lawyer_charge . " was made to Beneficial Legal Services. 
                        //\n\nIf you have any questions, or need further assistance, please call our customer service department at: (800) 585-3550. ";
                        
                        //$headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
                         //   'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
                         //   'X-Mailer: PHP/' . phpversion();

                        //mail($to, $subject, $message, $headers);

                        // first email to NAFS
                        // $to      = 'kristina.brown@nafsbenefits.com';
                        // $subject = 'A New Customer Submitted Payment';
                        // $message = "You have a new customer payment of $349. \n Name: " . $cc_name . "\n Email: " . $customer_email . "\n Phone Number: " . $customer_phone_number;
                        // $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
                        //     'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
                        //     'X-Mailer: PHP/' . phpversion();

                        // mail($to, $subject, $message, $headers);

                        // second email to NAFS
                        // $to      = 'icrissy@hotmail.com';
                        // $subject = 'A New Customer Submitted Payment';
                        // $message = "You have a new customer payment of $349. \n Name: " . $cc_name . "\n Email: " . $customer_email . "\n Phone Number: " . $customer_phone_number;
                        // $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
                        //     'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
                        //     'X-Mailer: PHP/' . phpversion();

                        // mail($to, $subject, $message, $headers);

                        // email to law firm
                        $to      = 'stever.blegal@gmail.com';// stever.blegal@gmail.com stever.blegal@gmail.com
                        $subject = 'You Have A New Client Payment Through National Association Of Family Services';
                        $message = "You have a new customer payment of ". $total_lawyer_charge . ". \n Name: " . $cc_name . "\n Email: " . $customer_email . "\n Phone Number: " . $customer_phone_number;
                        $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
                            'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();

                        mail($to, $subject, $message, $headers);

                        // $redirect = get_site_url() . '/payment-submission//';
                        // wp_redirect( $redirect);
                    } 
                    else 
                    {
                        testEcho( "Authorize.net Transaction Failed"); 
                        testEcho( '<br>');
                        if ($transactionResponse->getErrors() != null) 
                        {
                            testEcho( " Error Code  : " . $transactionResponse->getErrors()[0]->getErrorCode()); 
                            testEcho( '<br>');
                            testEcho( " Error Message : " . $transactionResponse->getErrors()[0]->getErrorText()); 
                            testEcho( '<br>');
                        }
                        handleErrorMail($transactionResponse, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);
                        handleFailedTransaction($gw, $paysafeSaleTransactionId, $subscribeTransactionId);

                    }
                    // Or, print errors if the API request wasn't successful
                } 
                else 
                {
                    testEcho( "Authorize.net Transaction Failed"); 
                    testEcho( '<br>');
                    $transactionResponse = $authorizeNetResponse->getTransactionResponse();
                
                    if ($transactionResponse != null && $transactionResponse->getErrors() != null) {
                        testEcho( " Error Code  : " . $transactionResponse->getErrors()[0]->getErrorCode()); 
                        testEcho( '<br>');
                        testEcho( " Error Message : " . $transactionResponse->getErrors()[0]->getErrorText()); 
                        testEcho( '<br>');
                        
                        handleErrorMail($transactionResponse, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);
                    } 
                    else 
                    {
                        testEcho( " Error Code  : " . $authorizeNetResponse->getMessages()->getMessage()[0]->getCode()); 
                        testEcho( '<br>');
                        testEcho( " Error Message : " . $authorizeNetResponse->getMessages()->getMessage()[0]->getText()); 
                        testEcho( '<br>');

                        handleErrorMail2($authorizeNetResponse, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);

                    }
                    handleFailedTransaction($gw, $paysafeSaleTransactionId, $subscribeTransactionId);
                }
            } 
            else 
            {
                testEcho(  "Authorize.net No response returned");
                testEcho( '<br>');
                testEcho( '<br>');

                $to      = "";
                $subject = 'NAFS Production Error - Authorize.net no Response';
                $message = "No Response from Authorize.net";
                $headers = 'From: webmaster@example.com' . "\r\n" .
                    'Reply-To: webmaster@example.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);

                handleFailedTransaction($gw, $paysafeSaleTransactionId, $subscribeTransactionId);
            }
        } else {
                testEcho(  "Paysafe subscription Failed");
                testEcho( '<br>');
                testEcho( '<br>');

                handleErrorMail4($subscribeResponseText, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);
                handleFailedTransaction($gw, $paysafeSaleTransactionId, $subscribeTransactionId);

        }
    } 
    else 
    {
        testEcho('PaySafe SALE Transaction failed');
        testEcho( '<br>');
        // Fail - reroute to Payment Failed page?

        handleErrorMail3($paysafeSaleResponseText, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);


        
        $fc_final_response['success'] = "Congratulations and Welcome to National Association of Family Services.<br>";
        $fc_final_response['success'] .= "You have taken the first steps in securing your family's legacy.<br>";
        $fc_final_response['success'] .= " <br>";
        $fc_final_response['success'] .= "Our association was formed to help families gain access to quality legal and financial services, but at affordable prices. With over 17,000 members, our association has been able to negotiate discounts with service providers such as law firms, notary, and financial services companies.<br>";
        $fc_final_response['success'] .= " ";
        $fc_final_response['success'] .= "NAFS also acts as an advocate for its members. We are here to make sure you are completely satisfied with the services you receive now and in the future. As you know we have designed a simple and easy process to obtain services from our providers, but if you need assistance of any kind, we’re here for you and just a phone call away:<br>";
        $fc_final_response['success'] .= " ";
        $fc_final_response['success'] .= "(800) 585-3550<br>";
        $fc_final_response['success'] .= "Do not be afraid to call us about anything!<br>";
        echo json_encode($fc_final_response);
        die();
        //$redirect = get_site_url() . '/application-and-payment-submitted//';
        //wp_redirect( $redirect);
        

    }
}

add_action( 'admin_post_nopriv_epibuild_payment_form', 'handle_payment_submission' );
add_action('admin_post_epibuild_payment_form', 'handle_payment_submission');
add_action('formcraft_before_save', 'handle_form_submission', 10, 4);

// activate
register_activation_hook(__FILE__, array($epibuildPaymentProcessing, 'activate'));

// deactivate
register_deactivation_hook(__FILE__, array($epibuildPaymentProcessing, 'deactivate'));

// uninstall
global $fc_final_response;
class Person {
    public $first_name;
    public $last_name;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $phone;
    public $email;
}

function get_numeric($number_string) {
    $actual_number = 0;
    switch ($number_string) {
        case "one":
            $actual_number = 1;
            break;
        case "two":
            $actual_number = 2;
            break;
        case "three":
            $actual_number = 3;
            break;
        case "four":
            $actual_number = 4;
            break;
        case "five":
            $actual_number = 5;
            break;
        case "six":
            $actual_number = 6;
            break;
        case "seven":
            $actual_number = 7;
            break;
        case "eight":
            $actual_number = 8;
            break;
        case "nine":
            $actual_number = 9;
            break;
        case "ten":
            $actual_number = 10;
            break;
        case "eleven":
            $actual_number = 11;
            break;
        case "twelve":
            $actual_number = 12;
            break;
        case "thirteen":
            $actual_number = 13;
            break;
        case "fourteen":
            $actual_number = 14;
            break;
        case "fifteen":
            $actual_number = 15;
            break;
        case "sixteen":
            $actual_number = 16;
            break;
        case "seventeen":
            $actual_number = 17;
            break;
        case "eighteen":
            $actual_number = 18;
            break;
        case "nineteen":
            $actual_number = 19;
            break;
        case "twenty":
            $actual_number = 20;
            break;
        case "twentyone":
            $actual_number = 21;
            break;
        case "twentytwo":
            $actual_number = 22;
            break;
        case "twentythree":
            $actual_number = 23;
            break;
        case "twentyfour":
            $actual_number = 24;
            break;
        case "twentyfive":
            $actual_number = 25;
            break;
        default:
            $actual_number = 0;

    }

    return $actual_number;
}

// form submission
function handle_form_submission($content, $meta, $raw_content, $integrations) {
    $form_id = $content['Form ID'];
    
    
    // production form id 9, 49, 89
    // testing form id 31, 87 (current)
	
	if (strcmp($form_id, "87") == 0) {
		
		$cardholder = "";
		//$customer_first_name = "";
		//$customer_last_name = "";
		//$customer_phone_number "";
		$email_address = "";
        $member_first_name = "";
        $member_last_name = "";
        $street_address = "";
        $city = "";
        $state = "";
        $zip_code = "";
        $primary_phone_number = "";
        $additional_properties = 0;
        $credit_card_number = "";
        $expiration_date = "";
        $security_code = "";
		$cardholder_first_name = "";
		$cardholder_last_name = "";
        $spouse_first_name = "";
        $primary_phone_number_type = "";
        $alternate_phone_number = "";
        $member_marital_status = "";
        $are_you_a_us_citizen = "";
        $is_your_spouse_a_us_citizen = "";
        $children_current_marriage = "";
        $children_previous_marriage = "";
        $questions_notes_for_attorney = "";
        $representative_name = "";
        $how_did_you_hear_about_us = "";
        $url = $_SERVER['HTTP_REFERER'];
        $cardholder_street_address = "";
        $cardholder_city = "";
        $cardholder_state = "";
        $cardholder_zip_code = "";
        $cardholder_primary_phone_number = "";
		
		foreach ($raw_content as $value) {

            $label_name = $value['label'];
            $field_value = $value['value'];
            switch ($label_name) {
				case "Is the Cardholder Name Different Than the Member or Spouse?":
					$cardholder = $field_value;
					break;
                case "Email Address":
                    $email_address = $field_value;
                    break;
                case "Member First Name":
                    $member_first_name = $field_value;
                    break;
                case "Member Last Name":
                    $member_last_name = $field_value;
                    break;
                case "Street Address":
                    $street_address = $field_value;
                    break;
                case "City":
                    $city = $field_value;
                    break;
                case "State":
                    $state = $field_value;
                    break;
                case "Zip Code":
                    $zip_code = $field_value;
                    break;
                case "Primary Phone #":
                    $primary_phone_number = $field_value;
                    break;
                case "Additional Properties?":
                    $additional_properties = $field_value;
                    break;
                case "Credit Card Number":
                    $credit_card_number = $field_value;
                    break;
                case "Expiration Date":
                    $expiration_date = $field_value;
                    break;
                case "Security Code":
                    $security_code = $field_value;
                    break;
                case "Spouse First Name":
                    $spouse_first_name = $field_value;
                    break;
                case "Primary Phone # Type":
                    $primary_phone_number_type = $field_value;
                    break;
                case "Alternate Phone #":
                    $alternate_phone_number = $field_value;
                    break;
                case "Member Marital Status":
                    $member_marital_status = $field_value;
                    break;
                case "Are You a U.S. Citizen?":
                    $are_you_a_us_citizen = $field_value;
                    break;
                case "Is Your Spouse a U.S. Citizen?":
                    $is_your_spouse_a_us_citizen = $field_value;
                    break;
                case "Children (Excluding Children of a Previous Marriage)":
                    $children_current_marriage = $field_value;
                    break;
                case "Children of Previous Marriage(s) (If Applicable)":
                    $children_previous_marriage = $field_value;
                    break;
                case "Questions and/or Notes for Plan Attorney":
                    $questions_notes_for_attorney = $field_value;
                    break;
                case "Representative Name":
                    $representative_name = $field_value;
                    break;
                case "How Did You Hear About Us?":
                    $how_did_you_hear_about_us = $field_value;
                    break;
                case "Credit Card Street Address":
                    $cardholder_street_address = $field_value;
                    break;
                case "Credit Card City":
                    $cardholder_city = $field_value;
                    break;
                case "Credit Card State":
                    $cardholder_state = $field_value;
                    break;
                case "Credit Card Zip Code":
                    $cardholder_zip_code = $field_value ;
                    break;
                case "Cardholders Phone Number":
                    $cardholder_primary_phone_number = $field_value;
                    break;
            }
        }
		
		if (strcmp($cardholder, "Yes") == 0)
		{
			foreach ($raw_content as $value) {

				$label_name = $value['label'];
				$field_value = $value['value'];
				switch ($label_name) {
					case "Cardholder's First Name":
						$cardholder_first_name = $field_value;
						break;
					case "Cardholder's Last Name":
						$cardholder_last_name = $field_value;
						break;
					case "Credit Card Street Address":
						$street_address = $field_value;
						break;
					case "Credit Card City":
						$city = $field_value;
						break;
					case "Credit Card State":
						$state = $field_value;
						break;
					case "Credit Card Zip Code":
						$zip_code = $field_value;
						break;
					case "Cardholder's Phone Number":
						$cardholder_primary_phone_number = $field_value;
						break;
				
				}
			}
			
			
		}
		
		$additional_properties_num = get_numeric($additional_properties);
        
        // formcraft captures as MM/YYYY
        // authorize expdate is YYYY-MM
        // paysafe is MMYY
        $exp_parts = explode("/", $expiration_date);
        $month_part = $exp_parts[0];
        $year_part = $exp_parts[1];
        $authorize_exp = $year_part . "-" . $month_part;
        $paysafe_year = substr($year_part, -2);
        $paysafe_exp = $month_part . $paysafe_year;

        // formcraft has one==1
        

        $customer = new Person;
        $customer->first_name = $member_first_name;
        $customer->last_name = $member_last_name;
        $customer->street_address = $street_address;
        $customer->city = $city;
        $customer->state = $state;
        $customer->zip_code = $zip_code;
        $customer->phone = $primary_phone_number;
        $customer->email = $email_address;
        
        $full_name = $customer->first_name . " " . $customer->last_name;

        handle_payment_submission($credit_card_number, $authorize_exp, $paysafe_exp, $security_code, $email_address, $additional_properties_num, $full_name, $primary_phone_number, $customer, $url, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);
	
	}
	
    
    if (strcmp($form_id, "9") == 0 || strcmp($form_id, "49") == 0 || strcmp($form_id, "89") == 0 || strcmp($form_id, "90") == 0 || strcmp($form_id, "91") == 0 || strcmp($form_id, "92") == 0) {

		$cardholder = "";
		//$customer_first_name = "";
		//$customer_last_name = "";
		//$customer_phone_number "";
		$email_address = "";
        $member_first_name = "";
        $member_last_name = "";
        $street_address = "";
        $city = "";
        $state = "";
        $zip_code = "";
        $primary_phone_number = "";
        $additional_properties = 0;
        $credit_card_number = "";
        $expiration_date = "";
        $security_code = "";
		$cardholder_first_name = "";
		$cardholder_last_name = "";
        $spouse_first_name = "";
        $primary_phone_number_type = "";
        $alternate_phone_number = "";
        $member_marital_status = "";
        $are_you_a_us_citizen = "";
        $is_your_spouse_a_us_citizen = "";
        $children_current_marriage = "";
        $children_previous_marriage = "";
        $questions_notes_for_attorney = "";
        $representative_name = "";
        $how_did_you_hear_about_us = "";
        $url = $_SERVER['HTTP_REFERER'];
        $cardholder_street_address = "";
        $cardholder_city = "";
        $cardholder_state = "";
        $cardholder_zip_code = "";
        $cardholder_primary_phone_number = "";
		
		foreach ($raw_content as $value) {

            $label_name = $value['label'];
            $field_value = $value['value'];
            switch ($label_name) {
				case "Is the Cardholder Name Different Than the Member or Spouse?":
					$cardholder = $field_value;
					break;
                case "Email Address":
                    $email_address = $field_value;
                    break;
                case "Member First Name":
                    $member_first_name = $field_value;
                    break;
                case "Member Last Name":
                    $member_last_name = $field_value;
                    break;
                case "Street Address":
                    $street_address = $field_value;
                    break;
                case "City":
                    $city = $field_value;
                    break;
                case "State":
                    $state = $field_value;
                    break;
                case "Zip Code":
                    $zip_code = $field_value;
                    break;
                case "Primary Phone #":
                    $primary_phone_number = $field_value;
                    break;
                case "Additional Properties?":
                    $additional_properties = $field_value;
                    break;
                case "Credit Card Number":
                    $credit_card_number = $field_value;
                    break;
                case "Expiration Date":
                    $expiration_date = $field_value;
                    break;
                case "Security Code":
                    $security_code = $field_value;
                    break;
                case "Spouse First Name":
                    $spouse_first_name = $field_value;
                    break;
                case "Primary Phone # Type":
                    $primary_phone_number_type = $field_value;
                    break;
                case "Alternate Phone #":
                    $alternate_phone_number = $field_value;
                    break;
                case "Member Marital Status":
                    $member_marital_status = $field_value;
                    break;
                case "Are You a U.S. Citizen?":
                    $are_you_a_us_citizen = $field_value;
                    break;
                case "Is Your Spouse a U.S. Citizen?":
                    $is_your_spouse_a_us_citizen = $field_value;
                    break;
                case "Children (Excluding Children of a Previous Marriage)":
                    $children_current_marriage = $field_value;
                    break;
                case "Children of Previous Marriage(s) (If Applicable)":
                    $children_previous_marriage = $field_value;
                    break;
                case "Questions and/or Notes for Plan Attorney":
                    $questions_notes_for_attorney = $field_value;
                    break;
                case "Representative Name":
                    $representative_name = $field_value;
                    break;
                case "How Did You Hear About Us?":
                    $how_did_you_hear_about_us = $field_value;
                    break;
                case "Credit Card Street Address":
                    $cardholder_street_address = $field_value;
                    break;
                case "Credit Card City":
                    $cardholder_city = $field_value;
                    break;
                case "Credit Card State":
                    $cardholder_state = $field_value;
                    break;
                case "Credit Card Zip Code":
                    $cardholder_zip_code = $field_value ;
                    break;
                case "Cardholders Phone Number":
                    $cardholder_primary_phone_number = $field_value;
                    break;
            }
        }
		
		if (strcmp($cardholder, "Yes") == 0)
		{
			foreach ($raw_content as $value) {

				$label_name = $value['label'];
				$field_value = $value['value'];
				switch ($label_name) {
					case "Cardholder's First Name":
						$cardholder_first_name = $field_value;
						break;
					case "Cardholder's Last Name":
						$cardholder_last_name = $field_value;
						break;
					case "Credit Card Street Address":
						$street_address = $field_value;
						break;
					case "Credit Card City":
						$city = $field_value;
						break;
					case "Credit Card State":
						$state = $field_value;
						break;
					case "Credit Card Zip Code":
						$zip_code = $field_value;
						break;
					case "Cardholder's Phone Number":
						$cardholder_primary_phone_number = $field_value;
						break;
				
				}
			}
			
			
		}
		
		$additional_properties_num = get_numeric($additional_properties);
        
        // formcraft captures as MM/YYYY
        // authorize expdate is YYYY-MM
        // paysafe is MMYY
        $exp_parts = explode("/", $expiration_date);
        $month_part = $exp_parts[0];
        $year_part = $exp_parts[1];
        $authorize_exp = $year_part . "-" . $month_part;
        $paysafe_year = substr($year_part, -2);
        $paysafe_exp = $month_part . $paysafe_year;

        // formcraft has one==1
        

        $customer = new Person;
        $customer->first_name = $member_first_name;
        $customer->last_name = $member_last_name;
        $customer->street_address = $street_address;
        $customer->city = $city;
        $customer->state = $state;
        $customer->zip_code = $zip_code;
        $customer->phone = $primary_phone_number;
        $customer->email = $email_address;
        
        $full_name = $customer->first_name . " " . $customer->last_name;

        handle_payment_submission($credit_card_number, $authorize_exp, $paysafe_exp, $security_code, $email_address, $additional_properties_num, $full_name, $primary_phone_number, $customer, $url, $customer_email, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number);
    }

    
}

//handle email of payment failures
function handleErrorMail($transactionResponse, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number) {
    $to      = "applications@nafsbenefits.com";
    $subject = 'CC Failed - New Application';
    $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
        'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $message = "Error Message: " . $transactionResponse->getErrors()[0]->getErrorText() . "\r\n";
    $message .= "\r\n";
    $message .= "Payment Failed!" . "\r\n";
    $message .= "Refer: " . $url . "\r\n";
    $message .= "Email: " . $customer_email . "\r\n";
    $message .= "Member Name: " . $full_name . "\r\n";
    $message .= "Spouse First Name: " . $spouse_first_name . "\r\n";
    $message .= "Address: " . $street_address . "\r\n";
    $message .= "City: " . $city . "\r\n";
    $message .= "State: " . $state . "\r\n";
    $message .= "Zip Code: " . $zip_code . "\r\n";
    $message .= "Primary Phone: " . $primary_phone_number . "\r\n";
    $message .= "Primary Phone Type: " . $primary_phone_number_type . "\r\n";
    $message .= "Alternate Phone Number: " . $alternate_phone_number . "\r\n";
    $message .= "Member Marital Status: " . $member_marital_status . "\r\n";
    $message .= "Are you a U.S. Citizen?: " . $are_you_a_us_citizen . "\r\n";
    $message .= "Is your spouse a U.S. Citizen?: " . $is_your_spouse_a_us_citizen . "\r\n";
    $message .= "Children of Current Marriage: " . $children_current_marriage . "\r\n";
    $message .= "Children of Previous Marriage: " . $children_previous_marriage . "\r\n";
    $message .= "Questions Notes for Plan Attorney: " . $questions_notes_for_attorney . "\r\n";
    $message .= "Representative Name: " . $representative_name . "\r\n";
    $message .= "How Did You Hear About Us?: " . $how_did_you_hear_about_us . "\r\n";
    $message .= "\r\n";
    $message .= "Is the Cardholder Name Different Than the Member or Spouse?: " . $cardholder . "\r\n";
    $message .= "Cardholder First Name: " . $cardholder_first_name . "\r\n";
    $message .= "Cardholder Last Name: " . $cardholder_last_name . "\r\n";
    $message .= "Cardholder Address: " . $cardholder_street_address . "\r\n";
    $message .= "Cardholder City: " . $cardholder_city . "\r\n";
    $message .= "Cardholder State: " . $cardholder_state . "\r\n";
    $message .= "Cardholder Zip Code: " . $cardholder_zip_code . "\r\n";
    $message .= "Cardholder Phone Number: " . $cardholder_primary_phone_number . "\r\n";
        
        mail($to, $subject, $message, $headers);

}

function handleErrorMail2($authorizeNetResponse, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number) {
    $to      = "applications@nafsbenefits.com";
    $subject = 'CC Failed - New Application';
    // To send HTML mail, the Content-type header must be set
    $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
        'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $message = "Error Message: " . $authorizeNetResponse->getErrors()[0]->getErrorText() . "\r\n";
    $message .= "\r\n";
    $message .= "Payment Failed!" . "\r\n";
    $message .= "Refer: " . $url . "\r\n";
    $message .= "Email: " . $customer_email . "\r\n";
    $message .= "Member Name: " . $full_name . "\r\n";
    $message .= "Spouse First Name: " . $spouse_first_name . "\r\n";
    $message .= "Address: " . $street_address . "\r\n";
    $message .= "City: " . $city . "\r\n";
    $message .= "State: " . $state . "\r\n";
    $message .= "Zip Code: " . $zip_code . "\r\n";
    $message .= "Primary Phone: " . $primary_phone_number . "\r\n";
    $message .= "Primary Phone Type: " . $primary_phone_number_type . "\r\n";
    $message .= "Alternate Phone Number: " . $alternate_phone_number . "\r\n";
    $message .= "Member Marital Status: " . $member_marital_status . "\r\n";
    $message .= "Are you a U.S. Citizen?: " . $are_you_a_us_citizen . "\r\n";
    $message .= "Is your spouse a U.S. Citizen?: " . $is_your_spouse_a_us_citizen . "\r\n";
    $message .= "Children of Current Marriage: " . $children_current_marriage . "\r\n";
    $message .= "Children of Previous Marriage: " . $children_previous_marriage . "\r\n";
    $message .= "Questions Notes for Plan Attorney: " . $questions_notes_for_attorney . "\r\n";
    $message .= "Representative Name: " . $representative_name . "\r\n";
    $message .= "How Did You Hear About Us?: " . $how_did_you_hear_about_us . "\r\n";
    $message .= "\r\n";
    $message .= "Is the Cardholder Name Different Than the Member or Spouse?: " . $cardholder . "\r\n";
    $message .= "Cardholder First Name: " . $cardholder_first_name . "\r\n";
    $message .= "Cardholder Last Name: " . $cardholder_last_name . "\r\n";
    $message .= "Cardholder Address: " . $cardholder_street_address . "\r\n";
    $message .= "Cardholder City: " . $cardholder_city . "\r\n";
    $message .= "Cardholder State: " . $cardholder_state . "\r\n";
    $message .= "Cardholder Zip Code: " . $cardholder_zip_code . "\r\n";
    $message .= "Cardholder Phone Number: " . $cardholder_primary_phone_number . "\r\n";
    
        mail($to, $subject, $message, $headers);

}

function handleErrorMail3($paysafeSaleResponseText, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number) {
    $to      = "applications@nafsbenefits.com";
    $subject = 'CC Failed - New Application';
    $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
        'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $message = "PaySafe Response: " . $paysafeSaleResponseText . "\r\n";
    $message .= "\r\n";
    $message .= "Payment Failed!" . "\r\n";
    $message .= "Refer: " . $url . "\r\n";
    $message .= "Email: " . $customer_email . "\r\n";
    $message .= "Member Name: " . $full_name . "\r\n";
    $message .= "Spouse First Name: " . $spouse_first_name . "\r\n";
    $message .= "Address: " . $street_address . "\r\n";
    $message .= "City: " . $city . "\r\n";
    $message .= "State: " . $state . "\r\n";
    $message .= "Zip Code: " . $zip_code . "\r\n";
    $message .= "Primary Phone: " . $primary_phone_number . "\r\n";
    $message .= "Primary Phone Type: " . $primary_phone_number_type . "\r\n";
    $message .= "Alternate Phone Number: " . $alternate_phone_number . "\r\n";
    $message .= "Member Marital Status: " . $member_marital_status . "\r\n";
    $message .= "Are you a U.S. Citizen?: " . $are_you_a_us_citizen . "\r\n";
    $message .= "Is your spouse a U.S. Citizen?: " . $is_your_spouse_a_us_citizen . "\r\n";
    $message .= "Children of Current Marriage: " . $children_current_marriage . "\r\n";
    $message .= "Children of Previous Marriage: " . $children_previous_marriage . "\r\n";
    $message .= "Questions Notes for Plan Attorney: " . $questions_notes_for_attorney . "\r\n";
    $message .= "Representative Name: " . $representative_name . "\r\n";
    $message .= "How Did You Hear About Us?: " . $how_did_you_hear_about_us . "\r\n";
    $message .= "\r\n";
    $message .= "Is the Cardholder Name Different Than the Member or Spouse?: " . $cardholder . "\r\n";
    $message .= "Cardholder First Name: " . $cardholder_first_name . "\r\n";
    $message .= "Cardholder Last Name: " . $cardholder_last_name . "\r\n";
    $message .= "Cardholder Address: " . $cardholder_street_address . "\r\n";
    $message .= "Cardholder City: " . $cardholder_city . "\r\n";
    $message .= "Cardholder State: " . $cardholder_state . "\r\n";
    $message .= "Cardholder Zip Code: " . $cardholder_zip_code . "\r\n";
    $message .= "Cardholder Phone Number: " . $cardholder_primary_phone_number . "\r\n";
    
        mail($to, $subject, $message, $headers);

}

function handleErrorMail4($subscribeResponseText, $url, $customer_email, $full_name, $spouse_first_name, $street_address, $city, $state, $zip_code, $primary_phone_number, $primary_phone_number_type, $alternate_phone_number, $member_marital_status, $are_you_a_us_citizen, $is_your_spouse_a_us_citizen, $children_current_marriage, $children_previous_marriage, $questions_notes_for_attorney, $representative_name, $how_did_you_hear_about_us, $cardholder, $cardholder_first_name, $cardholder_last_name, $cardholder_street_address, $cardholder_city, $cardholder_state, $cardholder_zip_code, $cardholder_primary_phone_number) {
    $to      = "applications@nafsbenefits.com";
    $subject = 'CC Failed - New Application';
    $headers = 'From: noreply@nationalfamilyservices.com' . "\r\n" .
        'Reply-To: noreply@nationalfamilyservices.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $message = "Suscriber Error Response: " . $subscribeResponseText . "\r\n";
    $message .= "\r\n";
    $message .= "Payment Failed!" . "\r\n";
    $message .= "Refer: " . $url . "\r\n";
    $message .= "Email: " . $customer_email . "\r\n";
    $message .= "Member Name: " . $full_name . "\r\n";
    $message .= "Spouse First Name: " . $spouse_first_name . "\r\n";
    $message .= "Address: " . $street_address . "\r\n";
    $message .= "City: " . $city . "\r\n";
    $message .= "State: " . $state . "\r\n";
    $message .= "Zip Code: " . $zip_code . "\r\n";
    $message .= "Primary Phone: " . $primary_phone_number . "\r\n";
    $message .= "Primary Phone Type: " . $primary_phone_number_type . "\r\n";
    $message .= "Alternate Phone Number: " . $alternate_phone_number . "\r\n";
    $message .= "Member Marital Status: " . $member_marital_status . "\r\n";
    $message .= "Are you a U.S. Citizen?: " . $are_you_a_us_citizen . "\r\n";
    $message .= "Is your spouse a U.S. Citizen?: " . $is_your_spouse_a_us_citizen . "\r\n";
    $message .= "Children of Current Marriage: " . $children_current_marriage . "\r\n";
    $message .= "Children of Previous Marriage: " . $children_previous_marriage . "\r\n";
    $message .= "Questions Notes for Plan Attorney: " . $questions_notes_for_attorney . "\r\n";
    $message .= "Representative Name: " . $representative_name . "\r\n";
    $message .= "How Did You Hear About Us?: " . $how_did_you_hear_about_us . "\r\n";
    $message .= "\r\n";
    $message .= "Is the Cardholder Name Different Than the Member or Spouse?: " . $cardholder . "\r\n";
    $message .= "Cardholder First Name: " . $cardholder_first_name . "\r\n";
    $message .= "Cardholder Last Name: " . $cardholder_last_name . "\r\n";
    $message .= "Cardholder Address: " . $cardholder_street_address . "\r\n";
    $message .= "Cardholder City: " . $cardholder_city . "\r\n";
    $message .= "Cardholder State: " . $cardholder_state . "\r\n";
    $message .= "Cardholder Zip Code: " . $cardholder_zip_code . "\r\n";
    $message .= "Cardholder Phone Number: " . $cardholder_primary_phone_number . "\r\n";
    
        mail($to, $subject, $message, $headers);

}

// paysafe
define("APPROVED", 1);
define("DECLINED", 2);
define("ERROR", 3);

class gwapi {

// Initial Setting Functions

  function setLogin($security_key) {
    $this->login['security_key'] = $security_key;
  }

  function setOrder($orderid,
        $orderdescription,
        $tax,
        $shipping,
        $ponumber,
        $ipaddress) {
    $this->order['orderid']          = $orderid;
    $this->order['orderdescription'] = $orderdescription;
    $this->order['tax']              = $tax;
    $this->order['shipping']         = $shipping;
    $this->order['ponumber']         = $ponumber;
    $this->order['ipaddress']        = $ipaddress;
  }

  function setBilling($firstname,
        $lastname,
        $company,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $country,
        $phone,
        $fax,
        $email,
        $website) {
    $this->billing['firstname'] = $firstname;
    $this->billing['lastname']  = $lastname;
    $this->billing['company']   = $company;
    $this->billing['address1']  = $address1;
    $this->billing['address2']  = $address2;
    $this->billing['city']      = $city;
    $this->billing['state']     = $state;
    $this->billing['zip']       = $zip;
    $this->billing['country']   = $country;
    $this->billing['phone']     = $phone;
    $this->billing['fax']       = $fax;
    $this->billing['email']     = $email;
    $this->billing['website']   = $website;
  }

  function setShipping($firstname,
        $lastname,
        $company,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $country,
        $email) {
    $this->shipping['firstname'] = $firstname;
    $this->shipping['lastname']  = $lastname;
    $this->shipping['company']   = $company;
    $this->shipping['address1']  = $address1;
    $this->shipping['address2']  = $address2;
    $this->shipping['city']      = $city;
    $this->shipping['state']     = $state;
    $this->shipping['zip']       = $zip;
    $this->shipping['country']   = $country;
    $this->shipping['email']     = $email;
  }

  // Transaction Functions

  function doSale($amount, $ccnumber, $ccexp, $cvv, $customer) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Sales Information
    $query .= "ccnumber=" . urlencode($ccnumber) . "&";
    $query .= "ccexp=" . urlencode($ccexp) . "&";
    $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    $query .= "cvv=" . urlencode($cvv) . "&";
    // Order Information
    $query .= "ipaddress=" . urlencode($this->order['ipaddress']) . "&";
    $query .= "orderid=" . urlencode($this->order['orderid']) . "&";
    $query .= "orderdescription=" . urlencode($this->order['orderdescription']) . "&";
    $query .= "tax=" . urlencode(number_format($this->order['tax'],2,".","")) . "&";
    $query .= "shipping=" . urlencode(number_format($this->order['shipping'],2,".","")) . "&";
    $query .= "ponumber=" . urlencode($this->order['ponumber']) . "&";
    // Billing Information
    $query .= "firstname=" . urlencode($customer->first_name) . "&";
    $query .= "lastname=" . urlencode($customer->last_name) . "&";
    $query .= "company=" . urlencode($this->billing['company']) . "&";
    $query .= "address1=" . urlencode($customer->street_address) . "&";
    $query .= "address2=" . urlencode($this->billing['address2']) . "&";
    $query .= "city=" . urlencode($customer->city) . "&";
    $query .= "state=" . urlencode($customer->state) . "&";
    $query .= "zip=" . urlencode($customer->zip_code) . "&";
    $query .= "country=" . urlencode("United States of America") . "&";
    $query .= "phone=" . urlencode($customer->phone_number) . "&";
    $query .= "fax=" . urlencode($this->billing['fax']) . "&";
    $query .= "email=" . urlencode($customer->email) . "&";
    $query .= "website=" . urlencode($this->billing['website']) . "&";
    // Shipping Information
    $query .= "shipping_firstname=" . urlencode($this->shipping['firstname']) . "&";
    $query .= "shipping_lastname=" . urlencode($this->shipping['lastname']) . "&";
    $query .= "shipping_company=" . urlencode($this->shipping['company']) . "&";
    $query .= "shipping_address1=" . urlencode($this->shipping['address1']) . "&";
    $query .= "shipping_address2=" . urlencode($this->shipping['address2']) . "&";
    $query .= "shipping_city=" . urlencode($this->shipping['city']) . "&";
    $query .= "shipping_state=" . urlencode($this->shipping['state']) . "&";
    $query .= "shipping_zip=" . urlencode($this->shipping['zip']) . "&";
    $query .= "shipping_country=" . urlencode($this->shipping['country']) . "&";
    $query .= "shipping_email=" . urlencode($this->shipping['email']) . "&";
    $query .= "type=sale";
    return $this->_doPost($query);
  }

  function doAuth($amount, $ccnumber, $ccexp, $cvv="") {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Sales Information
    $query .= "ccnumber=" . urlencode($ccnumber) . "&";
    $query .= "ccexp=" . urlencode($ccexp) . "&";
    $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    $query .= "cvv=" . urlencode($cvv) . "&";
    // Order Information
    $query .= "ipaddress=" . urlencode($this->order['ipaddress']) . "&";
    $query .= "orderid=" . urlencode($this->order['orderid']) . "&";
    $query .= "orderdescription=" . urlencode($this->order['orderdescription']) . "&";
    $query .= "tax=" . urlencode(number_format($this->order['tax'],2,".","")) . "&";
    $query .= "shipping=" . urlencode(number_format($this->order['shipping'],2,".","")) . "&";
    $query .= "ponumber=" . urlencode($this->order['ponumber']) . "&";
    // Billing Information
    $query .= "firstname=" . urlencode($this->billing['firstname']) . "&";
    $query .= "lastname=" . urlencode($this->billing['lastname']) . "&";
    $query .= "company=" . urlencode($this->billing['company']) . "&";
    $query .= "address1=" . urlencode($this->billing['address1']) . "&";
    $query .= "address2=" . urlencode($this->billing['address2']) . "&";
    $query .= "city=" . urlencode($this->billing['city']) . "&";
    $query .= "state=" . urlencode($this->billing['state']) . "&";
    $query .= "zip=" . urlencode($this->billing['zip']) . "&";
    $query .= "country=" . urlencode($this->billing['country']) . "&";
    $query .= "phone=" . urlencode($this->billing['phone']) . "&";
    $query .= "fax=" . urlencode($this->billing['fax']) . "&";
    $query .= "email=" . urlencode($this->billing['email']) . "&";
    $query .= "website=" . urlencode($this->billing['website']) . "&";
    // Shipping Information
    $query .= "shipping_firstname=" . urlencode($this->shipping['firstname']) . "&";
    $query .= "shipping_lastname=" . urlencode($this->shipping['lastname']) . "&";
    $query .= "shipping_company=" . urlencode($this->shipping['company']) . "&";
    $query .= "shipping_address1=" . urlencode($this->shipping['address1']) . "&";
    $query .= "shipping_address2=" . urlencode($this->shipping['address2']) . "&";
    $query .= "shipping_city=" . urlencode($this->shipping['city']) . "&";
    $query .= "shipping_state=" . urlencode($this->shipping['state']) . "&";
    $query .= "shipping_zip=" . urlencode($this->shipping['zip']) . "&";
    $query .= "shipping_country=" . urlencode($this->shipping['country']) . "&";
    $query .= "shipping_email=" . urlencode($this->shipping['email']) . "&";
    $query .= "type=auth";
    return $this->_doPost($query);
  }

  function doCredit($amount, $ccnumber, $ccexp) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Sales Information
    $query .= "ccnumber=" . urlencode($ccnumber) . "&";
    $query .= "ccexp=" . urlencode($ccexp) . "&";
    $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    // Order Information
    $query .= "ipaddress=" . urlencode($this->order['ipaddress']) . "&";
    $query .= "orderid=" . urlencode($this->order['orderid']) . "&";
    $query .= "orderdescription=" . urlencode($this->order['orderdescription']) . "&";
    $query .= "tax=" . urlencode(number_format($this->order['tax'],2,".","")) . "&";
    $query .= "shipping=" . urlencode(number_format($this->order['shipping'],2,".","")) . "&";
    $query .= "ponumber=" . urlencode($this->order['ponumber']) . "&";
    // Billing Information
    $query .= "firstname=" . urlencode($this->billing['firstname']) . "&";
    $query .= "lastname=" . urlencode($this->billing['lastname']) . "&";
    $query .= "company=" . urlencode($this->billing['company']) . "&";
    $query .= "address1=" . urlencode($this->billing['address1']) . "&";
    $query .= "address2=" . urlencode($this->billing['address2']) . "&";
    $query .= "city=" . urlencode($this->billing['city']) . "&";
    $query .= "state=" . urlencode($this->billing['state']) . "&";
    $query .= "zip=" . urlencode($this->billing['zip']) . "&";
    $query .= "country=" . urlencode($this->billing['country']) . "&";
    $query .= "phone=" . urlencode($this->billing['phone']) . "&";
    $query .= "fax=" . urlencode($this->billing['fax']) . "&";
    $query .= "email=" . urlencode($this->billing['email']) . "&";
    $query .= "website=" . urlencode($this->billing['website']) . "&";
    $query .= "type=credit";
    return $this->_doPost($query);
  }

  function doOffline($authorizationcode, $amount, $ccnumber, $ccexp) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Sales Information
    $query .= "ccnumber=" . urlencode($ccnumber) . "&";
    $query .= "ccexp=" . urlencode($ccexp) . "&";
    $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    $query .= "authorizationcode=" . urlencode($authorizationcode) . "&";
    // Order Information
    $query .= "ipaddress=" . urlencode($this->order['ipaddress']) . "&";
    $query .= "orderid=" . urlencode($this->order['orderid']) . "&";
    $query .= "orderdescription=" . urlencode($this->order['orderdescription']) . "&";
    $query .= "tax=" . urlencode(number_format($this->order['tax'],2,".","")) . "&";
    $query .= "shipping=" . urlencode(number_format($this->order['shipping'],2,".","")) . "&";
    $query .= "ponumber=" . urlencode($this->order['ponumber']) . "&";
    // Billing Information
    $query .= "firstname=" . urlencode($this->billing['firstname']) . "&";
    $query .= "lastname=" . urlencode($this->billing['lastname']) . "&";
    $query .= "company=" . urlencode($this->billing['company']) . "&";
    $query .= "address1=" . urlencode($this->billing['address1']) . "&";
    $query .= "address2=" . urlencode($this->billing['address2']) . "&";
    $query .= "city=" . urlencode($this->billing['city']) . "&";
    $query .= "state=" . urlencode($this->billing['state']) . "&";
    $query .= "zip=" . urlencode($this->billing['zip']) . "&";
    $query .= "country=" . urlencode($this->billing['country']) . "&";
    $query .= "phone=" . urlencode($this->billing['phone']) . "&";
    $query .= "fax=" . urlencode($this->billing['fax']) . "&";
    $query .= "email=" . urlencode($this->billing['email']) . "&";
    $query .= "website=" . urlencode($this->billing['website']) . "&";
    // Shipping Information
    $query .= "shipping_firstname=" . urlencode($this->shipping['firstname']) . "&";
    $query .= "shipping_lastname=" . urlencode($this->shipping['lastname']) . "&";
    $query .= "shipping_company=" . urlencode($this->shipping['company']) . "&";
    $query .= "shipping_address1=" . urlencode($this->shipping['address1']) . "&";
    $query .= "shipping_address2=" . urlencode($this->shipping['address2']) . "&";
    $query .= "shipping_city=" . urlencode($this->shipping['city']) . "&";
    $query .= "shipping_state=" . urlencode($this->shipping['state']) . "&";
    $query .= "shipping_zip=" . urlencode($this->shipping['zip']) . "&";
    $query .= "shipping_country=" . urlencode($this->shipping['country']) . "&";
    $query .= "shipping_email=" . urlencode($this->shipping['email']) . "&";
    $query .= "type=offline";
    return $this->_doPost($query);
  }

  function doCapture($transactionid, $amount =0) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Transaction Information
    $query .= "transactionid=" . urlencode($transactionid) . "&";
    if ($amount>0) {
        $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    }
    $query .= "type=capture";
    return $this->_doPost($query);
  }

  function doVoid($transactionid) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Transaction Information
    $query .= "transactionid=" . urlencode($transactionid) . "&";
    $query .= "type=void";
    return $this->_doPost($query);
  }

  function doRefund($transactionid, $amount = 0) {

    $query  = "";
    // Login Information
    $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
    // Transaction Information
    $query .= "transactionid=" . urlencode($transactionid) . "&";
    if ($amount>0) {
        $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
    }
    $query .= "type=refund";
    return $this->_doPost($query);
  }

  function doAddPlan($plan_id) {
      $query = "";
      $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
      $query .= "recurring=add_plan" . "&";
      $query .= "plan_payments=" . urlencode(0) . "&";
      $query .= "plan_amount=" . urlencode(19.99) . "&";
      $query .= "plan_name=" . urlencode("NAFS Subscription Plan") . "&";
      $query .= "plan_id=" . urlencode($plan_id) . "&";
      $query .= "month_frequency=" . urlencode(3) . "&";
      $query .= "day_of_month=" . urlencode(1);
      return $this->_doPost($query);
  }

  function doAddSubscriber($plan_id, $cc_number, $cc_exp, $cc_code) {
    $current_month = date("n");
    $current_year = date("Y");
    
    $month_to_charge = $current_month + 7;
    if ($month_to_charge > 12) {
        $month_to_charge -= 12;
        $current_year += 1;
    }

    $month_string = $month_to_charge;
    if ($month_to_charge < 10) {
        $month_string = "0" . $month_to_charge;
    }
    $start_date = $current_year . $month_string . "01";

      $query="";
      $query .= "security_key=" . urlencode($this->login['security_key']) . "&";
      $query .= "recurring=add_subscription" . "&";
      $query .= "plan_id=" . urlencode($plan_id) . "&";
      $query .= "start_date=" . urlencode($start_date) . "&";
      $query .= "ccnumber=" . urlencode($cc_number) . "&";
      $query .= "ccexp=" . urlencode($cc_exp);
      return $this->_doPost($query);

  }

  function _doPost($query) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://secure.paysafepaymentgateway.com/api/transact.php");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_POST, 1);

    if (!($data = curl_exec($ch))) {
        return ERROR;
    }
    curl_close($ch);
    unset($ch);
    // print "\n$data\n";
    // $data = explode("&",$data);
    // for($i=0;$i<count($data);$i++) {
    //     $rdata = explode("=",$data[$i]);
    //     $this->responses[$rdata[0]] = $rdata[1];
    // }
    //return $this->responses['response'];

    return $data;
  }
}


//     // production key: 6D7H9shyxe5ZjXgB73T5vF6sqZaFhTD4
//     /*
//         test cards:
//         Visa:	4111111111111111
//         MasterCard:	5431111111111111
//         Discover:	6011601160116611
//         American Express:	341111111111111
//         Diner's Club:	30205252489926
//         JCB:	3541963594572595
//         Maestro:	6799990100000000019
//         Credit Card Expiration:	10/25
//         account (ACH):	123123123
//         routing (ACH):	123123123
//     */

function generateRandomString($length = 255) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// authorize.net code
function authorizeChargeCreditCard($amount, $cc_name, $cc_number, $cc_exp, $cc_code, $customer)
{
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    // production name:
    // production key: 
    // Test name: 
    // Test Key: 
    $MERCHANT_LOGIN_ID = "5KP3u95bQpv";
    $MERCHANT_TRANSACTION_KEY = "346HZ32z3fP4hTG2";

    $MERCHANT_LOGIN_ID = "98SbDu6gAjS";
    $MERCHANT_TRANSACTION_KEY = "2Pj4uYD2E97Qn5wd";
    $merchantAuthentication->setName($MERCHANT_LOGIN_ID);
    $merchantAuthentication->setTransactionKey($MERCHANT_TRANSACTION_KEY);
    
    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($cc_number);
    // yyyy-mm
    $creditCard->setExpirationDate($cc_exp);
    $creditCard->setCardCode($cc_code);

    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create order information
    $order = new AnetAPI\OrderType();
    // $order->setInvoiceNumber("10101");
    // $order->setDescription("Golf Shirts");

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
     $customerAddress->setFirstName($customer->first_name);
     $customerAddress->setLastName($customer->last_name);
    // $customerAddress->setCompany("Souveniropolis");
     $customerAddress->setAddress($customer->street_address);
     $customerAddress->setCity($customer->city);
     $customerAddress->setState($customer->state);
     $customerAddress->setZip($customer->zip_code);
     $customerAddress->setCountry("USA");

    // Set the customer's identifying information
    $customerData = new AnetAPI\CustomerDataType();
    // $customerData->setType("individual");
    // $customerData->setId("99999456654");
    // $customerData->setEmail("EllenJohnson@example.com");

    // Add values for transaction settings
    $duplicateWindowSetting = new AnetAPI\SettingType();
    $duplicateWindowSetting->setSettingName("duplicateWindow");
    $duplicateWindowSetting->setSettingValue("60");

    // Add some merchant defined fields. These fields won't be stored with the transaction,
    // but will be echoed back in the response.
    // $merchantDefinedField1 = new AnetAPI\UserFieldType();
    // $merchantDefinedField1->setName("customerLoyaltyNum");
    // $merchantDefinedField1->setValue("1128836273");

    // $merchantDefinedField2 = new AnetAPI\UserFieldType();
    // $merchantDefinedField2->setName("favoriteColor");
    // $merchantDefinedField2->setValue("blue");

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount($amount);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setBillTo($customerAddress);
    $transactionRequestType->setCustomer($customerData);
    $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
    // $transactionRequestType->addToUserFields($merchantDefinedField1);
    // $transactionRequestType->addToUserFields($merchantDefinedField2);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    //$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION); // ::PRODUCTION  ::SANDBOX
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX); // ::PRODUCTION  ::SANDBOX

    return $response;
}
