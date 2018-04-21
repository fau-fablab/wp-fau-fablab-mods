<?php
// add style to the login page

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

function faufablab_login_logo() {
	?>
		<style type="text/css">
			body.login div#login h1 a {
				background-image: url(<?= plugin_dir_url( __FILE__ ) ?>/logo.png);
			}
			#login {
				padding-top: 2% !important;
			}
		</style>
	<?php
 }
add_action( 'login_enqueue_scripts', 'faufablab_login_logo' );

function faufablab_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'faufablab_login_logo_url' );

function faufablab_login_logo_url_title() {
	?>
		<div id="header" class="header" style="padding-top: 5em;">
			<h1><?php echo bloginfo( 'name' ) ?></h1>
		</div>
	<?php
}
add_filter( 'login_headertitle', 'faufablab_login_logo_url_title' );
