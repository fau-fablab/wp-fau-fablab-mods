<?php
/**
 * Plugin Name: FAU FabLab Mods
 * Plugin URI: https://github.com/fau-fablab/wp-fau-fablab-mods/
 * Description: WordPress modifications by the FAU FabLab which are not worth for a own plugin
 * Version: 0.0.1
 * Author: fau-fablab
 * Author URI: https://github.com/fau-fablab/
 * Network: false
 * License: CC BY 4.0
 */

/**
 * custom form field validation for [UltimateMember](https://github.com/ultimatemember/ultimatemember/)
 * docs: http://docs.ultimatemember.com/article/94-apply-custom-validation-to-a-field
 * define `FABLAB_CAPTCHA_SOLUTION` in wp-config.php
 */
add_action('um_submit_form_errors_hook_', 'um_custom_validate_captcha', 999, 1);
function um_custom_validate_captcha( $args ) {
	global $ultimatemember;
	$fablab_captcha_name = 'fablab_captcha';

	if ( !isset( $args[$fablab_captcha_name] ) || $args[$fablab_captcha_name] !== FABLAB_CAPTCHA_SOLUTION) {
		$ultimatemember->form->add_error( $fablab_captcha_name, 'Diese Antwort ist leider falsch.' );
	}
}
