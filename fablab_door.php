<?php
// Logic to display the fablab doorstate from our space api.

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

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
