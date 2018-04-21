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

/**
 * Add Mobile number and FAU Card ID Field to contact methods
 */
function faufablab_custom_contact_methods( $fields ) {
	$fields['mobile'] = __( 'Mobile' );
	$fields['fau_id'] = __( 'FAU Card ID' );
	return $fields;
}
add_filter( 'user_contactmethods', 'faufablab_custom_contact_methods' );

define( 'FAUFABLAB_SUPPORTED_PROFILE_IMAGE_EXTENSIONS', array( 'image/jpeg' => 'jpg', 'image/png' => 'png' ) );
define( 'FAUFABLAB_PROFILE_IMAGE_BASE_DIR', wp_upload_dir()['basedir'] .
		"/faufablab_profile_images_" .
		FABLAB_PROFILE_IMAGE_UPLOAD_DIR_SECRET .
		"/"
);
define( 'FAUFABLAB_PROFILE_IMAGE_BASE_URL',  wp_upload_dir()['baseurl'] .
		"/faufablab_profile_images_" .
		FABLAB_PROFILE_IMAGE_UPLOAD_DIR_SECRET .
		"/"
);
define( 'FAUFABLAB_DEFAULT_IMAGE_FILE_NAME', plugin_dir_path( __FILE__ ) . '/avatar.jpg' );
define( 'FAUFABLAB_DEFAULT_IMAGE_URL', plugin_dir_url( __FILE__ ) . '/avatar.jpg' );
define( 'FAUFABLAB_PROFILE_IMAGE_MAX_SIZE', 1024 * 1024 );

/**
 * Return the file name of the profile image for $user relative to faufablab_profile_image_base_dir or rather faufablab_profile_image_base_url.
 * If $file_extension is given, it won't be checked if the file exists (for image upload).
 * If $file_extension is not given, this function will try all supported extensions in order and return that file name that exists or '' otherwise.
 */
function faufablab_profile_image_filename( $user, $file_extension = "" ) {
	if ( ! is_dir( FAUFABLAB_PROFILE_IMAGE_BASE_DIR ) ) {
		// create directory and disable caches by writing .htaccess
		wp_mkdir_p( FAUFABLAB_PROFILE_IMAGE_BASE_DIR );
		fwrite( fopen( FAUFABLAB_PROFILE_IMAGE_BASE_DIR . '/.htaccess', 'w' ), '
<filesMatch "\.(jpg|png)$">
  FileETag None
  <ifModule mod_headers.c>
     Header unset ETag
     Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
     Header set Pragma "no-cache"
     Header set Expires "Wed, 11 Jan 1984 05:00:00 GMT"
  </ifModule>
</filesMatch>
' );
	}
	$fn_hash = hash( "sha256", $user->user_nicename . FABLAB_PROFILE_IMAGE_UPLOAD_DIR_SECRET );
	if ( $file_extension != '' ) {
		return $fn_hash . '.' . $file_extension;
	} else {
		foreach ( array_values( FAUFABLAB_SUPPORTED_PROFILE_IMAGE_EXTENSIONS ) as $extension ) {
			if ( is_file( FAUFABLAB_PROFILE_IMAGE_BASE_DIR . '/' . $fn_hash . '.' . $extension ) ) {
				return $fn_hash . '.' . $extension;
			}
		}
		// no profile image:
		return '';
	}
}

/**
 * Return the full image path for the profile image of $user. See faufablab_profile_image_filename.
 */
function faufablab_profile_image_path( $user, $file_extension = '' ) {
	$file_name = faufablab_profile_image_filename( $user, $file_extension );
	if ( $file_name == '' ) {
		return '';
	} else {
		return FAUFABLAB_PROFILE_IMAGE_BASE_DIR . $file_name;
	}
}

/**
 * Return the full image url for the profile image of $user or a default url if there is no image. See faufablab_profile_image_filename.
 */
function faufablab_profile_image_url( $user ) {
	$file_name = faufablab_profile_image_filename( $user );
	if ( $file_name == '' ) {
		return FAUFABLAB_DEFAULT_IMAGE_URL;
	} else {
		return FAUFABLAB_PROFILE_IMAGE_BASE_URL . $file_name;
	}
}

/**
 * Add custom profile image upload form.
 */
function faufablab_add_user_fields( $user ) {
	?>
<table class="form-table">
	<tr>
		<th><label for="dropdown">Profilbild</label></th>
		<td>
			<div>
				<input type="file" id="faufablab_profile_image_file" name="faufablab_profile_image" hidden>
				<button type="button" class="button" id="faufablab_profile_image_preview"
				 style="width:100px;height:100px;padding:1px;background-image:url('<?= faufablab_profile_image_url( $user ) ?>');background-size:cover;background-repeat:no-repeat;background-position:50% 50%;"
				 onclick="document.getElementById('faufablab_profile_image_file').click();">
				</button>
				<script>
jQuery("#faufablab_profile_image_file").change(function() {
	if (this.files && this.files[0]) {
		var reader = new FileReader();
		reader.onload = function(e) {
			jQuery('#faufablab_profile_image_preview').css('background-image', `url("${e.target.result}")`);
		}
		reader.readAsDataURL(this.files[0]);
	}
});
				</script>
				<p>
					<span class="description">
						Dein Profilbild für die <a href="/aktive/">List der Aktiven</a>.
					</span>
				</p>
			</div>
		</td>
	</tr>
</table>
	<?php
}
add_action( 'show_user_profile', 'faufablab_add_user_fields' );
add_action( 'edit_user_profile', 'faufablab_add_user_fields' );

/**
 * Set the attribute enctype to multipart for the default profile editing form to support image upload.
 */
function faufablab_enctype_multipart( ) {
   echo ' enctype="multipart/form-data"';
}
add_action( 'user_edit_form_tag' , 'faufablab_enctype_multipart' );

/**
 * Save custom profile image.
 */
function faufablab_save_user_fields( $user_id ) {
	$user = get_user_by( 'id', $user_id );

	if ( !current_user_can( 'edit_user', $user ) ) {
		FAUFabLabAdminNotice::displayError(__('You are not allowed to edit this user!'));
		return false;
	}

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$uploaded_file = $_FILES['faufablab_profile_image'];

	if ( $uploaded_file['size'] == 0 ) {
		// keep old file
		return true;
	} else if ( $uploaded_file['size'] > FAUFABLAB_PROFILE_IMAGE_MAX_SIZE ) {
		FAUFabLabAdminNotice::displayError(__('Profile image is too large! It must be smaller than ' . FAUFABLAB_PROFILE_IMAGE_MAX_SIZE/1024 . ' KiB' ));
		return false;
	}

	// upload
	$upload_overrides = array( 'test_form' => false );
	$upload_result = wp_handle_upload( $uploaded_file, $upload_overrides );

	if ( $upload_result && ! isset( $upload_result['error'] ) ) {
		if ( ! in_array( $upload_result['type'], array_keys( FAUFABLAB_SUPPORTED_PROFILE_IMAGE_EXTENSIONS ))) {
			FAUFabLabAdminNotice::displayError( __( 'Unsupported mime type!' ) );
			unlink( $upload_result['file'] );
			return false;
		}
		$extension = FAUFABLAB_SUPPORTED_PROFILE_IMAGE_EXTENSIONS[$upload_result['type']];
		$filename = faufablab_profile_image_path( $user, $extension );

		// delete old image
		$old_image = faufablab_profile_image_path( $user );
		if ( $old_image != '' ) {
			unlink( $old_image );
		}

		// rename new image
		if ( ! rename( $upload_result['file'], $filename ) ) {
			FAUFabLabAdminNotice::displayError(__('Could not move file! Please contact the admin.'));
			unlink( $upload_result['file'] );
			return false;
		}

		// success
		FAUFabLabAdminNotice::displaySuccess(__('Successfully uploaded profile image.'));
		return true;
	} else {
		FAUFabLabAdminNotice::displayError( $upload_result['error'] );
		return false;
	}
}
add_action( 'personal_options_update', 'faufablab_save_user_fields' );
add_action( 'edit_user_profile_update', 'faufablab_save_user_fields' );

/**
 * export a CSV file including the name and their FAU Card ID of all FabLab Betreuer.
 */
add_action('restrict_manage_users', 'fablab_export_fauids' );
function fablab_export_fauids( $args ) {
       ?>
       <button class="button" title="FAU IDs als CSV exportieren" type="button" onclick="export_fauids_csv()">
               FAU IDs exportieren
       </button>
       <script>
       function export_fauids_csv() {
               var csv_text = '"id"\t"name"\t"FAU Card ID"\n';
               <?php
               $wp_users = get_users( array( 'role_in' => array( 'editor' ) ) );
               foreach( $wp_users as $wp_user ) {
                       ?>
                       var user_id = <?= $wp_user->id ?>;
                       var user_display_name = "<?= str_replace('"', '\\\"', $wp_user->display_name) ?>";
                       var user_fau_id = "<?= str_replace('"', '\\\"', $wp_user->fau_id) ?>";
                       csv_text += `${user_id}\t"${user_display_name}"\t"${user_fau_id}"\n`;
                       <?php
               }
               ?>
               location.href = "data:text/csv;charset=utf-8," + encodeURIComponent(csv_text);
       }
       </script>
       <?php
}

/**
 * Display a list of all active members (>= editor wordpress role) for all active members whenever the shortcode [ aktivenliste ] is used.
 */
function faufablab_print_listederaktiven( $attrs ) {
	if ( ! current_user_can( "level_3" ) ) {
		return "<h2>Diese Seite dürfen nur Betreuer und Aktive sehen<br><small>(WordPress Rolle >= Redakteur)</smal></h2>";
	// } else if ( current_user_can( "level_10" ) ) {
	// 	fablab_export_fauids();
	}

	$users = get_users( array(
		'role_in' => array( 'editor' )
	) );

	$ret = '
		<table id="listederaktiven">
			<thead>
				<tr>
					<th>Bild</th>
					<th>Name</th>
					<th>E-Mail</th>
					<th>Mobile</th>
					<th>VCard</th>
				</tr>
			</thead>';

	$image_type = array( 'jpg' => 'JPEG', 'png' => 'PNG' );
	foreach ( $users as &$user ) {
		// $user->user_nicename,
		$avatar_url = faufablab_profile_image_url( $user );
		$avatar_file_path = faufablab_profile_image_path( $user );
		$avatar_base64 = '';
		try {
			$avatar_file = fopen( $avatar_file_path, 'r' );
			$avatar_content = fread( $avatar_file, filesize( $avatar_file_path ) );
			fclose( $avatar_file );
			$avatar_base64 = base64_encode( $avatar_content );
		} catch (Exception $exc) {}


		// mimetype wird im Zweifelsfall jpeg sein...
		$avatar_type = "JPEG";
		try {
			// try catch, weil das zu 90% schief geht
			$tmp = explode( ".", $avatar_url );
			$avatar_type = $image_type[end( $tmp )];
			if ( $avatar_type == '' ) {
				// wird schon jpeg sein
				$avatar_type = 'JPEG';
			}
		} catch (Exception $exc) {}

		$ret = $ret . '
			<tbody>
				<tr>
					<script>
var faufablab_vcard_' . $user->ID . ' = `BEGIN:VCARD
VERSION:2.1
FN:' . $user->display_name . '
ORG:FAU FabLab
PHOTO;ENCODING=BASE64;' . $avatar_type . ':' . $avatar_base64 . '
TEL;CELL:' . $user->mobile . '
EMAIL;WORK:' . $user->user_email . '
REV:' . date("Ymd\THis\Z") . '
END:VCARD
`;
					</script>
					<td>
						<div style="
							width:100px;
							height:100px;
							padding:1px;
							background-image:url(' . $avatar_url . ');
							background-size:cover;
							background-repeat:no-repeat;
							background-position:50% 50%;
						"></div>
					</td>
					<td>' . $user->display_name . '</td>
					<td><a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a></td>
					<td><a href="tel:' . $user->mobile . '">' . $user->mobile . '</a></td>
					<td>
						<button class="faufablab_vcard_export" type="button"
							onclick="location.href=\'data:text/vcard;charset=utf-8,\' + encodeURIComponent(faufablab_vcard_' . $user->ID . ');">
							⬇
						</button>
					</td>
				</tr>
			</tbody>
		';
	}

	$ret = $ret . "</table>";
	return $ret;
}
add_shortcode( 'aktivenliste', 'faufablab_print_listederaktiven' );

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
