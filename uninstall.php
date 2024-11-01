<?php
    require_once 'SimplePrivacy.php';

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

    delete_option(SimplePrivacy::opt_name);

?>
