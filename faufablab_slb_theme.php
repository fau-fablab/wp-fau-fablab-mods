<?php
// Simple Lightbox Theme for FAU FabLab (primarily to disable google fonts).
// See https://archetyped.com/know/slb-theme-anatomy/

defined( 'ABSPATH' ) or die( "[!] This script must be executed by a wordpress instance!\r\n" );

function faufablab_slb_theme_init( $themes ) {
	$properties = array(
		'name' => __( 'FAU FabLab', 'faufablab' ),
		'parent' => 'slb_baseline',
		// 'layout' => './faufablab_slb_theme_layout.html',
		// 'scripts' => array(),
		// 'styles' => array(),
	);
	$themes->add( 'faufablab_slb_theme', $properties );
}
add_action( 'slb_themes_init', 'faufablab_slb_theme_init' );
