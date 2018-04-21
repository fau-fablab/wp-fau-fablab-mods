<?php
// Disable some wordpress features, that could leak private information of our user.

defined( 'ABSPATH' ) or die( "[!] This script must be executed by a wordpress instance!\r\n" );

/**
 * Use browser built-in emojis instead of SVGs from w.org CDN.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Use local anonymous avatar instead of gravatar.
 */
function faufablab_avatar( $content, $id_or_email ) {
       $GRAVATAR_REGEX = "/https?:\/\/secure\.gravatar\.com\/avatar\/[\w]+/";
       if( preg_match( $GRAVATAR_REGEX, $content ) ) {
               return preg_replace( $GRAVATAR_REGEX, plugins_url( 'avatar.jpg', __FILE__ ), $content );
       }
       return $content;
}

function faufablab_bp_avatar( $content, $params ) {
       if ( is_array( $params ) && $params['object'] == 'user' ) {
               return faufablab_avatar( $content, $params['item_id'] );
       }
       return $content;
}

add_filter( 'get_avatar', 'faufablab_avatar', 1, 5 );
add_filter( 'bp_core_fetch_avatar', 'faufablab_bp_avatar', 1, 2 );
add_filter( 'bp_core_fetch_avatar_url', 'faufablab_bp_avatar', 1, 2 );
add_filter( 'user_profile_picture_description', create_function( '$desc', 'return "";' ) );
add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );
