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

/**
 * custom form field validation for [UltimateMember](https://github.com/ultimatemember/ultimatemember/)
 * docs: http://docs.ultimatemember.com/article/94-apply-custom-validation-to-a-field
 * instructions: define `FABLAB_CAPTCHA_SOLUTION` in wp-config.php
 */
add_action('um_submit_form_errors_hook_', 'fablab_um_custom_validate_captcha', 999, 1);
function fablab_um_custom_validate_captcha( $args ) {
	global $ultimatemember;
	$fablab_captcha_name = 'fablab_captcha';

	if ( isset( $args[$fablab_captcha_name] ) && $args[$fablab_captcha_name] !== FABLAB_CAPTCHA_SOLUTION ) {
		$ultimatemember->form->add_error( $fablab_captcha_name, 'Diese Antwort ist leider falsch.' );
	}
}

/**
 * export a CSV file including the name, their FAU Card ID and locking permission dates of all FabLab Betreuer.
 */
add_action('restrict_manage_users', 'fablab_export_schliessberechtigungen' );
function fablab_export_schliessberechtigungen( $args ) {
       ?>
       <button class="button" title="Schließberechtigungen als CSV exportieren" type="button" onclick="export_schliessberechtigung_csv()">
               Schließberechtigungen exportieren
       </button>
       <script>
       function export_schliessberechtigung_csv() {
               var csv_text = '"id"\t"name"\t"FAU Card ID"\t"Schliessberechtigung bis"\n';
               <?php
               $wp_users = get_users( array( 'role__not_in' => array( 'abonnent' ), 'fields' => array( 'ID' ) ) );
               foreach( $wp_users as $wp_user ) {
                       um_fetch_user( $wp_user->ID );
                       $user_id = $wp_user->ID;
                       $user_first_name = um_user('first_name');
                       $user_last_name = um_user('last_name');
                       $user_display_name = um_user('display_name');
                       $user_fau_id = um_user('fau_id');
                       $user_schliessberechtigung_bis = date( 'Y-m-d', strtotime( um_user( 'schliessberechtigung_bis' ) ) );
                       ?>
                       var user_id = <?= $user_id ?>;
                       var user_first_name = "<?= str_replace('"', '\\\"', $user_first_name) ?>";
                       var user_last_name = "<?= str_replace('"', '\\\"', $user_last_name) ?>";
                       var user_display_name = "<?= str_replace('"', '\\\"', $user_display_name) ?>";
                       var user_fau_id = "<?= str_replace('"', '\\\"', $user_fau_id) ?>";
                       var user_schliessberechtigung_bis = "<?= str_replace('"', '\\\"', $user_schliessberechtigung_bis) ?>";
                       var user_name = `${user_first_name} ${user_last_name}`.trim() || user_display_name.trim();
                       csv_text += `${user_id}\t"${user_name}"\t"${user_fau_id}"\t"${user_schliessberechtigung_bis}"\n`;
                       <?php
               }
               ?>
               location.href = "data:text/csv;charset=utf-8," + encodeURIComponent(csv_text);
       }
       </script>
       <?php
}

/**
 * DoorState Widget
 * docs: https://codex.wordpress.org/Widgets_API
 */
class DoorStateWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'fau_fablab_doorstate_widget',
			'description' => 'Display the current FAU FabLab door state',
		);
		parent::__construct( 'fau_fablab_doorstate_widget', 'Türstatus', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		echo '<strong class="fablab_doorstate_widget"></strong>';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
?>
			<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

add_action( 'widgets_init', function(){
	register_widget( 'DoorStateWidget' );
});

/**
 * Add custom style and javascript. This is used for the widget and other customizations.
 */
function faufablab_enqueue_styles_and_scripts() {
	wp_enqueue_style(
		'faufablab_style',
		plugins_url('style.css', __FILE__)
	);
	wp_enqueue_script('jquery');
	wp_enqueue_script(
		'faufablab_script',
		plugins_url('script.js', __FILE__)
	);
}
add_action('wp_enqueue_scripts', 'faufablab_enqueue_styles_and_scripts');

/**
 * Use browser built-in emojis instead of SVGs from w.org CDN.
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/**
 * Use local anonymous avatar instead of gravatar.
 */
function faufablab_avatar($content, $id_or_email){
       $GRAVATAR_REGEX = "/https?:\/\/secure\.gravatar\.com\/avatar\/[\w]+/";
       if(preg_match($GRAVATAR_REGEX, $content)) {
               return preg_replace($GRAVATAR_REGEX, plugins_url('avatar.jpg', __FILE__), $content);
       }
       return $content;
}

function faufablab_bp_avatar( $content, $params ){
       if(is_array($params) && $params['object'] == 'user'){
               return faufablab_avatar($content, $params['item_id']);
       }
       return $content;
}

add_filter('get_avatar', 'faufablab_avatar', 1, 5);
add_filter('bp_core_fetch_avatar', 'faufablab_bp_avatar', 1, 2);
add_filter('bp_core_fetch_avatar_url', 'faufablab_bp_avatar', 1, 2);
add_filter('user_profile_picture_description', create_function('$desc', 'return "";'));
add_filter('bp_core_fetch_avatar_no_grav', '__return_true');
