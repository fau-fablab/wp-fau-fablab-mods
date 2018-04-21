<?php
// Add some extra fields to the registration to prevent spam.
// See https://codex.wordpress.org/Customizing_the_Registration_Form

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

// 1. Add a new form element...
function faufablab_register_form() {
	$first_name = ( ! empty( $_POST['first_name'] ) ) ? sanitize_text_field( $_POST['first_name'] ) : '';
	$description = ( ! empty( $_POST['description'] ) ) ? sanitize_text_field( $_POST['description'] ) : '';

	?>
		<p>
			<label for="first_name">
				Vorname<br>
				<input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( $first_name ); ?>" size="25" />
			</label>
		</p>
		<p>
			<label for="description">
				Biografische Angaben. (Schreib ein paar Worte über dich, damit wir dich besser kennen lernen können.)<br>
				<textarea name="description" id="description" class="input" rows="5"><?php echo esc_attr( $description ); ?></textarea>
			</label>
		</p>
		<p>
			<label for="faufablab_captcha">
				In welcher Stadt steht das FAU FabLab? (Frage, um Spam zu vermeiden)<br>
				<input type="text" name="faufablab_captcha" id="faufablab_captcha" class="input" />
			</label>
		</p>
	<?php
}
add_action( 'register_form', 'faufablab_register_form' );

// 2. Add validation. In this case, we make sure first_name is required.
function faufablab_registration_errors( $errors, $sanitized_user_login, $user_email ) {
	if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
		$errors->add( 'first_name_error', sprintf( '<strong>%s</strong>: %s', 'FEHLER', 'Damit wir besser kennen lernen, musst du einen Vornamen angeben.' ) );
	}

	if ( empty( $_POST['faufablab_captcha'] ) || ! empty( $_POST['faufablab_captcha'] ) && trim( $_POST['faufablab_captcha'] ) == '' ) {
		$errors->add( 'faufablab_captcha_error', sprintf( '<strong>%s</strong>: %s', 'FEHLER', 'Du musst diese Frage beantworten.' ) );
	} else if ( sanitize_text_field( $_POST['faufablab_captcha'] ) != FABLAB_CAPTCHA_SOLUTION ) {
		$errors->add( 'faufablab_captcha_error', sprintf( '<strong>%s</strong>: %s', 'FEHLER', 'Die Antwort ist falsch. Falls du nicht drauf kommst, melde dich bei den Administratoren.' ) );
	}

	if ( empty( $_POST['description'] ) || ! empty( $_POST['description'] ) && trim( $_POST['description'] ) == '' ) {
		$errors->add( 'description_error', sprintf( '<strong>%s</strong>: %s', 'FEHLER', 'Schreibe kurz, was du im FabLab machst/machen willst, damit wir wissen, dass du einer von uns bist ;).' ) );
	} else if ( strlen( sanitize_text_field( $_POST['description'] ) ) < 25 ) {
		$errors->add( 'description_error', sprintf( '<strong>%s</strong>: %s', 'FEHLER', 'Bitte schreibe mehr als 25 Zeichen über dich. Aktuell sind es ' . strlen( sanitize_text_field( $_POST['description'] ) ) ) );
	}

	return $errors;
}
add_filter( 'registration_errors', 'faufablab_registration_errors', 10, 3 );

// 3. Finally, save our extra registration user meta.
function faufablab_user_register( $user_id ) {
	if ( ! empty( $_POST['first_name'] ) ) {
		update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
	}

	if ( ! empty( $_POST['description'] ) ) {
		update_user_meta( $user_id, 'description', sanitize_text_field( $_POST['description'] ) );
	}
}
add_action( 'user_register', 'faufablab_user_register' );
