<?php
// Disable some wordpress features, that could leak private information of our user.

defined( 'ABSPATH' ) or die( "[!] This script must be executed by a wordpress instance!\r\n" );

/**
 * Use browser built-in emojis instead of SVGs from w.org CDN.
 * See https://kinsta.com/knowledgebase/disable-emojis-wordpress/#disable-emojis-code
 */
function faufablab_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'faufablab_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'faufablab_disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'faufablab_disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param array $plugins
 * @return array Difference betwen the two arrays
 */
function faufablab_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function faufablab_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' == $relation_type ) {
		/** This filter is documented in wp-includes/formatting.php */
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

		$urls = array_diff( $urls, array( $emoji_svg_url ) );
	}

	return $urls;
}

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
add_filter( 'user_profile_picture_description', function ($desc) { return "";} );
add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );
