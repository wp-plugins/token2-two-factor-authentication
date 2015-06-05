<?php

/**
* Header for token2 pages
*/
function token2_header( $step = '' )  
{ 
 
wp_register_style( 'token2login', admin_url( 'css/login.css' ) );
wp_register_style( 'token2admincss', admin_url( 'css/wp-admin.css' ) );
wp_register_style( 'token2logincss', admin_url( 'css/login.min.css' ) );
wp_register_style( 'formtoken2',  'https://token2.com/form.token2.min.css'  );
wp_register_script( 'jqueryutils',  admin_url( 'load-scripts.php?c=1&load=jquery,utils' ) );

wp_register_style( 'token2css', plugins_url( 'assets/token2.css', __FILE__ ) );


function scripts_function() 
{
 
wp_register_script( 'scripttoken2',  'https://token2.com/form.token2.min.js' );
}



?>
  <head>
    <?php
		 
add_action('wp_enqueue_scripts', 'scripts_function');

      global $wp_version;
      if ( version_compare( $wp_version, '3.3', '<=' ) ) {?>
       <?php  wp_enqueue_style("token2login"); ?>
	   
       
        <?php
      } else {
        ?>
        <?php  wp_enqueue_style('token2admincss'); ?>
          <?php  wp_enqueue_style("token2buttons"); ?>
		  <?php  wp_enqueue_style("token2logincss"); ?>
      
        <?php
      }
    ?>
      <?php  wp_enqueue_style('formtoken2'); ?>
     <?php  wp_enqueue_script('scripttoken2'); ?>
	 
    <?php 

if ( $step == 'verify_installation' ) { 

?>
        <?php  wp_enqueue_style("token2css"); ?>
        <script type="text/javascript">
        /* <![CDATA[ */
        var token2Ajax = {"ajaxurl":"<?php echo admin_url( 'admin-ajax.php' ); ?>"};
        /* ]]> */
        </script>
            <?php  wp_enqueue_script('jqueryutils'); ?>
       
    <?php 

} 
	
	wp_head();
	
	?>
  </head>
<?php 
}

/**
 * Generate the token2 token form
 * @param string $username
 * @param array $user_data
 * @param array $user_signature
 * @return string
 */

function token2_token_form( $username, $user_data, $user_signature, $redirect, $remember_me, $api, $api_url ) {

//Will check if this is an SMS user, then send SMS, if not normal OTP
	  
	 
	//  Raw API request -- sorry no time for nice things :)
	$rawbody=file_get_contents($api_url."/validate?send=1&format=1&api=".rawurlencode($api)."&userid=".$user_data["token2_id"]);
	 
	 $body=json_decode($rawbody,true);
  
 
   
  if ($body["siteid"]>1 && $body["usertype"]!=1  && $body["sms_restore_mode"]=="1"  ) {
  
  $link="https://token2.com/restore?siteid=".$body["siteid"]."&userid=".$body["userid"];
  
  }
	   
	  
?>
  <html>
    <?php echo token2_header(); ?>
   
    <body class="login login-action-login wp-core-ui" >
      <div id="login"  class=token2highlight  >
        <h1   >
          <?php echo get_bloginfo( 'name' ); ?> 
        </h1>
      
		 
		<p class=highlight  >
		<?php _e($body["response"], 'token2'); ?>
		</p>
 <br>
        <p class="token2message">
          <?php _e( "Enter the OTP code provided by Token2. You can get this code from the Token2 mobile app. ", 'token2' ); ?>
		  <?php if ($body["usertype"]==1) { ?>
		  <?php _e( "An SMS with the code will also be sent to:",'token2' ); ?>
          <strong>
            <?php
              $cellphone = normalize_cellphone( $user_data['phone'] );
              $cellphone = preg_replace( "/^\d{1,3}\-/", 'XXX-', $cellphone );
              $cellphone = preg_replace( "/\-\d{1,3}\-/", '-XXX-', $cellphone );

              echo esc_attr( $cellphone );
			  
			  }
            ?>
          </strong>
        </p>
 
        <form method="POST" id="token2" action="wp-login.php">
           
            <h5><?php _e( 'Enter Token2 OTP code', 'token2' ); ?></h5>
             
            <input type="text" name="token2_token" id="token2-token" class="input" value="" size="20" autofocus="true" /> 
			<input type="submit" value="<?php echo esc_attr_e( 'Login', 'token2' ) ?>" id="wp_submit" class="button button-primary button-large" />
		 <div style="clear:both"><br></div>
          <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>"/>
          <input type="hidden" name="username" value="<?php echo esc_attr( $username ); ?>"/>
          <input type="hidden" name="rememberme" value="<?php echo esc_attr( $remember_me ); ?>"/>
          <?php if ( isset( $user_signature['token2_signature'] ) && isset( $user_signature['signed_at'] ) ) { ?>
            <input type="hidden" name="token2_signature" value="<?php echo esc_attr( $user_signature['token2_signature'] ); ?>"/>
          <?php } ?>
           
		  <?php if ($link!="") { ?><hr>
		  <?php _e( 'Problems with the Mobile App? You can restore the settings by clicking the button below:', 'token2' ); ?><br><br>
		  <a  id="token2-restore"  target=_blank   class="search-field"  href=<?=$link?> ><?php _e( 'Restore Token2 OTP', 'token2' ); ?></a> 
		  <?php 
 }
 ?>
		  
        </form>
		
		  <h3  >
		<?php _e( 'Protected by Token2 | Hosted two factor authentication', 'token2' ); ?>
		</h3>
		
      </div>
    </body>
  </html>
<?php
 }

/**
* Enable token2 page
*
* @param mixed $user
* @return string
*/
function render_enable_token2_page( $user, $signature, $errors = array() , $disable_sms) {?>
  <html>
    <?php echo token2_header(); ?>
    <body class='login wp-core-ui'>
      <div id="login">
        <h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo get_bloginfo( 'name' ); ?></a></h1>
        <h3 style="text-align: center; margin-bottom:10px;">Enable token2 Two-Factor Authentication</h3>
        <?php
          if ( !empty( $errors ) ) {
            $message = '';
			
            foreach ( $errors as $msg ) {
              $message .= '<strong>ERROR: </strong>' . $msg . '<br>';
            }
            ?><div id="login_error"><?php echo _e( $message, 'token2' ); ?></div><?php
          }
        ?>
        <p class="message"><?php _e( 'Your administrator has requested that you add Token2 two-factor authentication to your account, please enter requested information below to enable.', 'token2' ); ?></p>
        <form method="POST" id="token2" action="wp-login.php">
		<label for="token2_type">Token type</label><br>
		 <?php if ($disable_sms!="true") { ?> <input type="radio" style="-webkit-appearance: radio" name="token2_type" value="1"><?php _e( 'SMS', 'token2' ); ?> &nbsp; <?php  } ?>
<input type="radio" name="token2_type" style="-webkit-appearance: radio" checked value="0"><?php _e( 'Mobile App', 'token2' ); ?><br><br> 

          <label for="token2_user[pin_code]"><?php _e( 'Pin code', 'token2' ); ?></label>
          <input type="text" name="token2_user[pin_code]" id="token2-pin" class="input" />
          <label for="token2_user[cellphone]"><?php _e( 'Cellphone number', 'token2' ); ?></label>
          <input type="tel" name="token2_user[cellphone]" id="token2-cellphone" class="input" />
          <input type="hidden" name="username" value="<?php echo esc_attr( $user->user_login ); ?>"/>
          <input type="hidden" name="step" value="enable_token2"/>
          <input type="hidden" name="token2_signature" value="<?php echo esc_attr( $signature ); ?>"/>

          <p class="submit">
            <input type="submit" value="<?php echo esc_attr_e( 'Enable', 'token2' ) ?>" id="wp_submit" class="button button-primary button-large">
          </p>
        </form>
      </div>
    </body>
  </html>
<?php 
}

/**
 * Form enable token2 on profile
 * @param string $users_key
 * @param array $user_datas
 * @return string
 */
function register_form_on_profile( $users_key, $user_data ) {?>
  <table class="form-table" id="<?php echo esc_attr( $users_key ); ?>">
    <tr>
      <th><label for="phone"><?php _e( 'Pin', 'token2' ); ?></label></th>
      <td>
        <input type="text" id="token2-pin" class="small-text" name="<?php echo esc_attr( $users_key ); ?>[pin_code]" value="<?php echo esc_attr( $user_data['pin_code'] ); ?>" />
      </td>
    </tr>
    <tr>
      <th><label for="phone"><?php _e( 'Cellphone number', 'token2' ); ?></label></th>
      <td>
        <input type="tel" id="token2-cellphone" class="regular-text" name="<?php echo esc_attr( $users_key ); ?>[phone]" value="<?php echo esc_attr( $user_data['phone'] ); ?>" />

        <?php wp_nonce_field( $users_key . 'edit_own', $users_key . '[nonce]' ); ?>
      </td>
    </tr>
  </table>
<?php
 }

/**
 * Form disable token2 on profile
 * @return string
 */
function disable_form_on_profile( $users_key ) {?>
  <table class="form-table" id="<?php echo esc_attr( $users_key ); ?>">
    <tr>
      <th><label for="<?php echo esc_attr( $users_key ); ?>_disable"><?php _e( 'Disable Two Factor Authentication?', 'token2' ); ?></label></th>
      <td>
        <input type="checkbox" id="<?php echo esc_attr( $users_key ); ?>_disable" name="<?php echo esc_attr( $users_key ); ?>[disable_own]" value="1" />
        <label for="<?php echo esc_attr( $users_key ); ?>_disable"><?php _e( 'Yes, disable token2 for your account.', 'token2' ); ?></label>

        <?php wp_nonce_field( $users_key . 'disable_own', $users_key . '[nonce]' ); ?>
      </td>
    </tr>
  </table>
<?php 
}

/**
 * Form verify token2 installation
 * @return string
 */
function token2_installation_form( $user, $user_data, $user_signature, $errors, $token2id , $api, $qr, $api_url ) {

//  Raw API request -- sorry no time for nice things :)
if (intval($_REQUEST["token2-token"])==0) {
	$rawbody=file_get_contents($api_url."/validate?send=1&format=1&api=".rawurlencode($api)."&userid=".$token2id); 
	}	 
	
	
	
	 
	 $body=json_decode($rawbody,true);
	 

?>
  <html>
    <?php echo token2_header( 'verify_installation' ); ?>
    <body class='login wp-core-ui'>
      <div id="token2-verify">
        <h1><a href="http://wordpress.org/" title="Powered by WordPress"><?php echo get_bloginfo( 'name' ); ?></a></h1>
        <?php if ( !empty( $errors ) ) {
		
		
	 
		
		?>
            <div id="login_error"><strong><?php echo esc_attr_e( 'ERROR: ', 'token2' ); ?></strong><?php echo esc_attr_e( $errors, 'token2' ); ?></div>
        <?php } ?>
        <form method="POST" id="token2" action="wp-login.php">
          <p><?php echo esc_attr_e( 'To activate your account you need to setup token2 Two-Factor Authentication.', 'token2' ); ?></p>

          <div class='step'>
            <div class='description-step' style="width:250px">
              <span class='number'>1.</span>
              <span>Install the Token2 Mobile application on your phone (from Appstore or Play), as described on <a target=_blank href=https://www.token2.com/?content=mobileapp>https://www.token2.com/?content=mobileapp</a> </span>
            </div>
            <img src="<?php echo plugins_url( '/assets/images/step1-image.png', __FILE__ ); ?>" alt='installation' />
          </div>

          <div class='step'>
            <div class='description-step' style="width:100%" >
              <span class='number'>2.</span>
              <span><?php _e( 'Open the App and create a new account by scanning the QR code shown below','token2'); ?></span>
            </div>
            <img src="<?php echo $qr; ?>" id=qrimage alt='QR'   /> <br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" onClick="getHASH()" value="<?php echo esc_attr_e( 'Can\'t scan?', 'token2' ) ?>" id="wp_gethash" class="button button-primary">
          </div>
<script>
function getHASH() {
var url= document.getElementById('qrimage').src;
var res = url.split("?secret");
 
prompt("Enter the string below manually",res[1].replace(/\W/g, ''));

}
</script>
          <p class='italic-text'>
            <?php echo esc_attr_e( 'If you have selected SMS as authentication method, you will receive an SMS soon ', 'token2' ); ?>
          </p>

		  <div class='step'>
            <div class='description-step' style="width:100%" >
              <span class='number'>3.</span>
			  <span>Enter the generated or received Token2 code below </span>
			  </div>
		  </div>
          <input type="text" name="token2_token" id="token2-token" class="input" value="" size="20" />
          <input type="hidden" name="username" value="<?php echo esc_attr( $user->user_login ); ?>"/>
          <input type="hidden" name="step" value="verify_installation"/>
          <?php if ( isset( $user_signature ) ) { ?>
            <input type="hidden" name="token2_signature" value="<?php echo esc_attr( $user_signature ); ?>"/>
          <?php } ?>

          <input type="submit" value="<?php echo esc_attr_e( 'Verify Token', 'token2' ) ?>" id="wp_submit" class="button button-primary">
           
        </form>
      </div>
	  
	  <center><h3>powered by Token2<br><a href=http://www.token2.com target=_blank><img   src=http://www.token2.com/images/icon.png></a></h3>
	  </center>
    </body>
  </html>
<?php }

/**
 * Form for enable token2 with JS
 * @return string
 */
function form_enable_on_modal( $users_key, $username, $token2_data, $errors, $disable_sms ) {?>
  <p><?php printf( __( 'token2 is not yet configured for your the <strong>%s</strong> account.', 'token2' ), $username ); ?></p>

  <p><?php _e( 'To enable token2 for this account, complete the form below, then click <em>Continue</em>.', 'token2' ); ?></p>

  <?php if ( !empty($errors) ) { 
  
   
  
  ?>
    <div class='error'>
      <?php
	   
        foreach ($errors->code as $key => $value) {
          if ($value == '22') { ?>
            <p><strong>PIN code</strong> is not valid.</p>
          <?php 
		  
        }

if ($value == '23') { ?>
            <p><strong>Phone number</strong> is not valid.</p>
          <?php 
		  
        }
		

		}
      ?>
	  
	</div>
	<p><a class="button button-primary" href="#" onClick="self.parent.tb_remove();return false;"><?php _e( 'Return to your profile', 'token2' ); ?></a></p>
    
	<p></p>
	
  <?php } 
if (  empty($errors) ) { ?>
  <table class="form-table" id="<?php echo esc_attr( $users_key ); ?>-ajax">


      <tr>
      <th><label for="phone"><?php _e( 'Authentication type', 'token2' ); ?>
	    
	  </label></th>
      <td>
     <?php if ($disable_sms!="true") { ?> <input type="radio" name="token2_type" value="1"><?php _e( 'SMS', 'token2' ); ?> &nbsp; <?php } ?>
<input type="radio" name="token2_type" checked value="0"><?php _e( 'Mobile App', 'token2' ); ?>
      </td>
    </tr>
	
  <tr >
      <th><label for="phone"><?php _e( 'PIN', 'token2' ); ?></label></th>
      <td>
        <input type="text" id="token2-pin_code" class="small-text" name="token2_pin_code" value="<?php echo esc_attr( $token2_data['pin_code'] ); ?>" required />
     <small><?php _e( 'Required for Mobile App option', 'token2' ); ?></small>
	  </td>
    </tr>
    <tr>
      <th><label for="phone"><?php _e( 'Cellphone number', 'token2' ); ?></label></th>
      <td>
        <input type="tel" id="token2-cellphone" class="regular-text" name="token2_phone" value="<?php echo esc_attr( $token2_data['phone'] ); ?>" style="width:140px;" />
      </td>
    </tr>
  </table>

  <input type="hidden" name="token2_step" value="" />
  <?php wp_nonce_field( $users_key . '_ajax_check' ); ?>

  <p class="submit">
    <input name="Continue" type="submit" value="<?php esc_attr_e( 'Continue' );?>" class="button-primary">
  </p>
<?php }

}

/**
 * Checkbox for admin disable token2 to the user
 * @return string
 */
function checkbox_for_admin_disable_token2( $users_key ) { ?>
  <tr>
      <th><label for="<?php echo esc_attr( $users_key ); ?>"><?php _e( 'Two Factor Authentication', 'token2' ); ?></label></th>
      <td>
          <input type="checkbox" id="<?php echo esc_attr( $users_key ); ?>" name="<?php echo esc_attr( $users_key ); ?>" value="1" checked/>
      </td>
  </tr>
<?php }

/**
 * Render the form to enable token2 by Admin user
 * @return string
 */
function render_admin_form_enable_token2( $users_key, $token2_data ) { ?>
  <tr>
      <p><?php _e( 'To enable token2 enter a PIN code and the cellphone number of the person who is going to use this account.', 'token2' )?></p>
      <th><label for="phone"><?php _e( 'PIN', 'token2' ); ?></label></th>
      <td>
          <input type="text" id="token2-pin" class="small-text" name="<?php echo esc_attr( $users_key ); ?>[pin_code]" value="<?php echo esc_attr( $token2_data['pin_code'] ); ?>" /> 
      </td>
  </tr>
  <tr>
      <th><label for="phone"><?php _e( 'Cellphone number', 'token2' ); ?></label></th>
      <td>
          <input type="tel" class="regular-text" id="token2-cellphone" name="<?php echo esc_attr( $users_key ); ?>[phone]" value="<?php echo esc_attr( $token2_data['phone'] ); ?>" />
      </td>
      <?php wp_nonce_field( $users_key . '_edit', "_{$users_key}_wpnonce" ); ?>
  </tr>
  <tr>
      <th><?php _e( 'Force enable token2', 'token2' ); ?></th>
      <td>
          <label for="force-enable">
              <input name="<?php echo esc_attr( $users_key ); ?>[force_enable_token2]" type="checkbox" value="true" <?php if ($token2_data['force_by_admin'] == 'true') echo 'checked="checked"'; ?> />
              <?php _e( 'Force this user to enable token2 Two-Factor Authentication on the next login.', 'token2' ); ?>
          </label>
      </td>
  </tr>
<?php }

/**
 * Input for user disable token2 on modal
 * @return string
 */
function render_disable_token2_on_modal( $users_key, $username ) { ?>
  <p><?php _e( 'token2 is enabled for this account.', 'token2' ); ?></p>
  <p><?php printf( __( 'Click the button below to disable two-factor authentication for <strong>%s</strong>', 'token2' ), $username ); ?></p>
<p><b><?php _e( 'Please note that you will not be able to re-enable token2, your token2 account needs to be reset using token2 administrative panel. This is a security related measure.', 'token2' ); ?></b></p>
  <p class="submit">
      <input name="Disable" type="submit" value="<?php esc_attr_e( 'Disable token2' );?>" class="button-primary">
  </p>
  <input type="hidden" name="token2_step" value="disable" />

  <?php wp_nonce_field( $users_key . '_ajax_disable' );
}

/**
 * Confirmation when the user enables token2.
 * @return string
 */
function render_confirmation_token2_enabled( $token2_id, $username, $cellphone, $ajax_url , $qrcode , $hash) {
  if ( $token2_id ) : ?>
    <p>
      <?php printf( __( 'Congratulations, token2 is now configured for <strong>%s</strong> user account.', 'token2' ), $username ); ?>
    </p>
    <p><img align=right src="<?=$qrcode?>">
      <?php _e( "For SMS option, codes will be sent to", 'token2' ); ?>
         <strong>+<?php echo esc_attr( $cellphone ); ?></strong> 
      <hr>
    
      <?php _e( "For Mobile App, scan the QR Code on the right, to create a profile or enter the hash below manually ", 'token2' ); ?>
	   
	 <br><b><h3><?=$hash?></h3></b>
    </p>
	
    <p><a class="button button-primary" href="#" onClick="self.parent.tb_remove();return false;"><?php _e( 'Return to your profile', 'token2' ); ?></a></p>
  <?php else : ?>
  
    <p><?php printf( __( 'token2 could not be activated for the <strong>%s</strong> user account.', 'token2' ), $username ); ?></p>
    <p><?php _e( 'Please try again later.', 'token2' ); ?></p>
    <p>
      <a class="button button-primary" href="<?php echo esc_url( $ajax_url ); ?>"><?php _e( 'Try again', 'token2' ); ?></a>
    </p>
  <?php endif;
}

/**
 * Confirmation when the user disables token2.
 */
function render_confirmation_token2_disabled(  ) { ?>
  <p><?php echo esc_attr_e( 'token2 was disabled', 'token2' );?></p>
  <p>
      <a class="button button-primary" href="#" onClick="self.parent.tb_remove();return false;">
          <?php _e( 'Return to your profile', 'token2' ); ?>
      </a>
  </p>
<?php }

/**
 * Normalize cellphone
 * given a cellphone return the normal form
 * 17654305034 -> 765-430-5034
 * normal form: 10 digits, {3}-{3}-{4}
 * @param string $cellphone
 * @return string
 */
function normalize_cellphone( $cellphone ) {
  $cellphone = substr( $cellphone, 0, -4 ) . '-' . substr( $cellphone, -4 );
  if ( strlen( $cellphone ) - 5 > 3 ) {
    $cellphone = substr( $cellphone, 0, -8 ) . '-' . substr( $cellphone, -8 );
  }
  return $cellphone;
}

// closing the last tag is not recommended: http://php.net/basic-syntax.instruction-separation
