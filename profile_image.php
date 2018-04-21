<?php
// logic to store profile images (for Liste der Aktiven) locally and hidden.

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

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
		<th><label for="profile_image_btn">Profilbild</label></th>
		<td>
			<div>
				<input type="file" id="faufablab_profile_image_file" name="faufablab_profile_image" hidden>
				<button id="profile_image_btn" type="button" class="button" id="faufablab_profile_image_preview"
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
