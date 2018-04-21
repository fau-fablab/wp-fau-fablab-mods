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

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

include 'admin_notices.php';
include 'registration_form_extras.php';
include 'login_form_style.php';
include 'profile_image.php';
include 'custom_user_fields.php';
include 'liste_der_aktiven.php';
include 'fablab_door.php';
include 'privacy.php';
