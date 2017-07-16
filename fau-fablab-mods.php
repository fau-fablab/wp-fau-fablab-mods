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
?>
<strong class="fablab_doorstate"></strong>
<style>
  .fablab_doorstate.opened {
    color: #209365;
  }
  .fablab_doorstate.closed {
    color: #C32027;
  }
  .fablab_doorstate.outdated {
    color: #153a63;
  }
</style>
<script>
function setDoorState(state, text) {
  jQuery(".fablab_doorstate").each(function(){
    var element = jQuery(this);
    if (element.text() != text || !element.hasClass(state)) {
      element.fadeOut("slow", function() {
	element.removeClass("opened closed outdated").addClass(state).text(text).fadeIn();
      });
    }
  });
}
function updateDoorState() {
  jQuery.getJSON("/spaceapi/door/", function(data) {
    var outdated = (new Date() / 1000 - data.time) > (60 * 60 * 24 * 7);
    // new Date() / 1000: get current timestamp in sec instead of msec
    // the info is outdated if it is older than one week
    setDoorState(
      outdated ? "outdated" : data.state,
      data.text + (outdated ? " (Diese Information ist evtl. veraltet.) " : "")
    );
  });
}
jQuery(document).ready(function() {
  updateDoorState();
  window.setInterval(updateDoorState, 60 * 1000);
});
</script>
<?php
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
