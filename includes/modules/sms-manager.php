<?php
/**
 * Functions for managing sms via brevo api
 *
 * @package RawConscious 
 */

/**
 * Send SMS
 */
function rc_wcpos_send_otp( $customer_id, $generated_otp){

    if ( ! defined( 'RC_LEGIT_SMS_API_KEY' ) ){
        return;
    }
    global $wpdb;
    $customer_table = $wpdb->prefix . 'rc_wcpos_customer';
    $customer_phone = $wpdb->get_var( "SELECT customer_phone FROM $customer_table WHERE customer_id = '$customer_id'" );
    // Configure API key authorization: api-key
    $config = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', RC_LEGIT_SMS_API_KEY);

    $apiInstance = new Brevo\Client\Api\TransactionalSMSApi(
        new GuzzleHttp\Client(),
        $config
    );
    $sendTransacSms = new \Brevo\Client\Model\SendTransacSms();

    $sendTransacSms['sender'] = 'LegitFoods';
    $sendTransacSms['recipient'] = '+91' . $customer_phone;
    $sendTransacSms['content'] = 'Hello. Greetings from Legit Foods. Your one time coupon is ' . $generated_otp;
    $sendTransacSms['type'] = 'transactional';

    $apiInstance->sendTransacSms($sendTransacSms);

}
?>