<?php
/**
 * token2 API CLASS
 *
 * Handles token2 API requests in a WordPress way.
 *
 * @package token2
 * @since 1.0.0
 */

class token2_API {
  /**
   * Class variables
   */

  // Oh look, a singleton
  private static $__instance = null;

  // token2 API
  protected $api_key = null;
  protected $api_endpoint = null;

  /**
   * Singleton implementation
   *
   * @uses this::setup
   * @return object
   */
  public static function instance( $api_key, $api_endpoint ) {
    if ( ! is_a( self::$__instance, 'token2_API' ) ) {
      if ( is_null( $api_key ) || is_null( $api_endpoint ) )
        return null;

      self::$__instance = new token2_API;

      self::$__instance->api_key = $api_key;
      self::$__instance->api_endpoint = $api_endpoint;

      self::$__instance->setup();
    }

    return self::$__instance;
  }

  /**
   * Silence is golden.
   */
  private function __construct() {}

  /**
   * Really, silence is golden.
   */
  private function setup() {}

  /**
   * Attempt to retrieve an token2 ID for a given request
   *
   * @param string $email
   * @param string $phone
   * @param string $pin_code
   * @uses sanitize_email, add_query_arg, wp_remote_post, wp_remote_retrieve_response_code, wp_remote_retrieve_body
   * @return mixed
   */
  public function register_user( $email, $phone, $pin_code, $type ) {
    // Sanitize arguments
    $email = sanitize_email( $email );
    $phone = preg_replace( '#[^\d]#', '', $phone );
    $pin_code = preg_replace( '#[^\d\+]#', '', $pin_code );

    // Build API endpoint
    $endpoint = sprintf( '%s/createuser', $this->api_endpoint );
    $endpoint = add_query_arg( array(
      'api_key' => $this->api_key,
      'email' => rawurlencode($email),
      'phone' => rawurlencode($phone),
	  'type' => intval($type),
	  'format' => "1",//JSON FORMAT
      'pin' => rawurlencode($pin_code)
    ), $endpoint );

    // Make API request and parse response
    $response = wp_remote_post( $endpoint );
    $status_code = wp_remote_retrieve_response_code( $response );

    $body = wp_remote_retrieve_body( $response );

    if ( ! empty( $body ) ) {
	 
      $body = json_decode( $body );
   
      return $body;
    }

    return false;
  }

  /**
   * Validate a given token and token2 ID
   *
   * @param int $id
   * @param string $token
   * @uses add_query_arg, wp_remote_head, wp_remote_retrieve_response_code
   * @return mixed
   */
  public function check_token( $id, $token ) {
   //Check OTP entered against local SESSION value
    
   
  }

  /**
  * Request a valid token via SMS
  * @param string $id
  * @return mixed
  */

  public function request_sms($id, $force) {
    $endpoint = sprintf( '%s/protected/json/sms/%d', $this->api_endpoint, $id );
    $arguments = array('api_key' => rawurlencode($this->api_key));

    if ($force == true) {
      $arguments['force'] = 'true';
    }

    $endpoint = add_query_arg( $arguments, $endpoint);
    $response = wp_remote_get($endpoint);
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body);

    if ( $status_code == 200 ) {
      return __( 'SMS token was sent. Please allow at least 1 minute for the text to arrive.', 'token2' );
    }

    return __( $body->message, 'token2' );
  }

  /**
  * Get application details
  * @return array
  */
  public function application_details() {
    
    return array();
  }

  /**
  * Verify if the given signature is valid.
  * @return boolean
  */
  public function verify_signature($user_data, $signature) {
    if(!isset($user_data['token2_signature'])  || !isset($user_data['signed_at']) ) {
      return false;
    }

    if((time() - $user_data['signed_at']) <= 300 && $user_data['token2_signature'] === $signature ) {
      return true;
    }

    return false;
  }

  /**
  * Generates a signature
  * @return string
  */
  public function generate_signature() {
    return wp_generate_password(64, false, false);
  }

  /**
  * Verify SSL certificates
  *
  * @return mixed
  */
  public function curl_ca_certificates() {
   //Disable now, later review

    return true;
  }
}