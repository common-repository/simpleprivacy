<?php
/*
Plugin Name: Simple Privacy
Plugin URI: http://sidecar.tv/simple-privacy/
Description: A simple way to create a private Wordpress blog
Version: 1.2
Author: SideCar Apps
Author URI: http://sidecar.tv/
License: Commerical
*/

class SimplePrivacy {
   const opt_name = 'simpleprivacy_options';

   static function log_it($msg){
       //error_log(date("m.d.y H:i:s")." - ".$msg."\n", 3, "/tmp/wp_log.log");
   }
    
  static function protect()  {
    
    self::log_it('staring protect');
    self::log_it('SERVER '.print_r($_SERVER, 1));

    $page_type = null;

    //login page is considered part of the admin_protect
    //which is different than is_blog_admin would return
    if(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))==basename(wp_login_url())){
      $page_type = 'admin';
    }
    if($page_type==null){
        if(is_blog_admin()){
          $page_type = 'admin';
        }
        else{
          $page_type = 'user';
        }
    }

    //load up our settings
    $simple = unserialize(get_option( self::opt_name ));
    
    if($simple['user_protect']=='1' && $page_type=='user'){
        if (!( ($_SERVER['PHP_AUTH_USER']==$simple['user_login'] && $_SERVER['PHP_AUTH_PW']==$simple['user_password'])
                || ($_SERVER['PHP_AUTH_USER']==$simple['admin_login'] && $_SERVER['PHP_AUTH_PW']==$simple['admin_password'])
            )) {
            header('WWW-Authenticate: Basic realm="'.$simple['user_realm'].'"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Please contact blog owner for login information.';
            exit;
        } 
    } elseif($simple['admin_protect']=='1' && $page_type=='admin'){
        if ($_SERVER['PHP_AUTH_USER']!=$simple['admin_login']|| $_SERVER['PHP_AUTH_PW']!=$simple['admin_password']) {
            header('WWW-Authenticate: Basic realm="'.$simple['user_realm'].'"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Please contact blog owner for login information.';
            exit;
        } 
    }

      return;
  }
  
  //add settings menu to WP settings page
   static  function settings_menu() {
        add_options_page('Simple Privacy Options', 'Simple Privacy', 'manage_options', 'simplePrivacy-menu-identifier', array('SimplePrivacy', 'settings_menu_options'));
    }

//print & process settings option
   static function settings_menu_options() {
        if (!current_user_can('manage_options'))  {
                wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        // variables for the field and option names 
        $user_protect_user_name = 'simpleprivacy_user_name';
        $user_protect_password_name = 'simpleprivacy_user_password';
        
        $hidden_field_name = 'mt_submit_hidden';

        // Read in existing option value from database
        $simple = unserialize(get_option( self::opt_name ));

        // See if the user has posted us some information
        // If they did, this hidden field will be set to 'Y'
        if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
            // Read their posted value
            //$user_protect_val = $_POST[ $user_protect_name ];
            
            $simple['user_protect'] = $_POST['user_protect'];
            $simple['admin_protect'] = $_POST['admin_protect'];
            $simple['user_password'] = $_POST['user_password'];
            $simple['user_login'] = $_POST['user_login'];
            $simple['admin_login'] = $_POST['admin_login'];
            $simple['admin_password'] = $_POST['admin_password'];
            $simple['user_realm'] = $_POST['user_realm'];

            
            
            // Save the posted value in the database
            update_option( self::opt_name, serialize($simple) );

            // Put an settings updated message on the screen
            ?>
            <div class="updated"><p><strong>Settings Saved</strong></p></div>
            <?php

        } ?>
        <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div><h2>Simple Privacy Settings</h2> 
        <p>Configure <a href=""http://en.wikipedia.org/wiki/Basic_access_authentication">http auth</a> 
            protection of your user or admin facing pages with a simple login & password. The WP login page is considered part of the admin section.
        </p>
        <form name="form1" method="post" action="">
            <input type="hidden" name="<?php print $hidden_field_name; ?>" value="Y">

        <h3>User Protection</h3>
        <P>Setup protection for user pages</p>
        <table class="form-table"> 
        <tr valign="top"> 
            <th scope="row">Protection</th> 
            <td><input type="radio" name="user_protect" value="1" <?php if ($simple['user_protect']=='1') print 'checked';  ?>> On 
                <input type="radio" name="user_protect" value="0"  <?php if ($simple['user_protect']!='1') print 'checked';  ?>> Off <br>
            </td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Login</th> 
            <td><input type="text" name="user_login" value="<?php print $simple['user_login']; ?>" size="20"></td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Password</th> 
            <td><input type="text" name="user_password" value="<?php print $simple['user_password']; ?>" size="20"></td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Password Prompt/Realm</th> 
            <td><input type="text" name="user_realm" value="<?php print $simple['user_realm']; ?>" size="20"></td>
        </tr>        
        </table>

        <h3>Admin Protection</h3>
        <P>Setup protection for blog admin pages</p>
        <table class="form-table"> 
        <tr valign="top"> 
            <th scope="row">Protection</th> 
            <td><input type="radio" name="admin_protect" value="1" <?php if ($simple['admin_protect']=='1') print 'checked';  ?>> On 
                <input type="radio" name="admin_protect" value="0"  <?php if ($simple['admin_protect']!='1') print 'checked';  ?>> Off <br>
            </td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Login</th> 
            <td><input type="text" name="admin_login" value="<?php print $simple['admin_login']; ?>" size="20"></td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Password</th> 
            <td><input type="text" name="admin_password" value="<?php print $simple['admin_password']; ?>" size="20"></td>
        </tr>       
        </table>
        
        
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="Save Changes" /></p>
        </form>

        </div>
            <?php
    }

}
//add_action('send_headers', array('SimplePrivacy', 'protect'));
add_action('init', array('SimplePrivacy', 'protect'));
add_action('admin_menu', array('SimplePrivacy', 'settings_menu'));
?>