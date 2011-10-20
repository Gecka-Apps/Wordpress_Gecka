<?php
// checks for gecka library on activation
function myplugin_activation_check(){
    if (function_exists('gk_version')) {
        if (version_compare(gk_version(), '0.1', '>=')) {
            return;
        }
    }
    deactivate_plugins( basename(__FILE__) ); // Deactivate ourself
    wp_die( __('The Gecka Library plugin must be activated before this plugin will run.') );
}
register_activation_hook(__FILE__, 'myplugin_activation_check');
