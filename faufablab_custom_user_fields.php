<?php
// add custom user fields (mobile number, FAU ID and door permission) and add button to export FAU IDs as CSV.

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

/**
 * Add Mobile number and FAU Card ID Field to contact methods
 */
function faufablab_custom_contact_methods( $fields ) {
	$fields['mobile'] = __( 'Mobile' );
	$fields['fau_id'] = __( 'FAU Card ID' );
	return $fields;
}
add_filter( 'user_contactmethods', 'faufablab_custom_contact_methods' );

/**
 * Add custom form for door permission (must not be edited by the user itself but only by admins).
 */
function faufablab_door_permission_add_user_fields( $user ) {
        $door_permission_until = get_user_meta( $user->ID, "faufablab_door_permission_until", true );
        $is_admin = current_user_can( 'edit_users' );
        ?>
<table class="form-table">
        <tr>
                <th><label for="door_permission_until">Schließberechtigung bis</label></th>
                <td>
                        <div>
                                <input
                                        type="date"
                                        id="faufablab_door_permission_until"
                                        name="faufablab_door_permission_until"
                                        value="<?= $door_permission_until ?>"
                                        <?php if ( ! $is_admin ) { echo "readonly"; } ?>
                                >
                                <p>
                                        <span class="description">
                                                Dieses Feld kann nur von Administratoren bearbeitet werden. Es sollte aber nur vom Schließberechtigungsadmin bearbeitet werden.
                                        </span>
                                </p>
                        </div>
                </td>
        </tr>
</table>
        <?php
}
add_action( 'show_user_profile', 'faufablab_door_permission_add_user_fields' );
add_action( 'edit_user_profile', 'faufablab_door_permission_add_user_fields' );

/**
 * Save door permission.
 */
function faufablab_door_permission_save_user_fields( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        $current_user = wp_get_current_user();

        $old_door_permission_until = get_user_meta( $user->ID, "faufablab_door_permission_until", true );
        $door_permission_until = $_POST['faufablab_door_permission_until'];
        // ignore, when there are no changes
        if ( $door_permission_until === $old_door_permission_until ) {
                return false;
        }

        if ( ! current_user_can( 'edit_users' ) ) {
                FAUFabLabAdminNotice::displayError(__('You are not allowed to edit the door permission of this user!'));
                return false;
        }

        // validation
        $match = preg_match( '/^(\d\d\d\d-\d\d-\d\d|)$/', $door_permission_until );
        if ( ! $match ) {
                FAUFabLabAdminNotice::displayError(__('Invalid characters for door_permission.'));
                return false;
        }

        // save
        update_user_meta( $user_id, 'faufablab_door_permission_until', $door_permission_until );

}
add_action( 'personal_options_update', 'faufablab_door_permission_save_user_fields' );
add_action( 'edit_user_profile_update', 'faufablab_door_permission_save_user_fields' );


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
               var csv_text = '"id"\t"name"\t"FAU Card ID"\t"door permission until"\n';
               <?php
               $wp_users = get_users( array( 'role_in' => array( 'editor' ) ) );
               foreach( $wp_users as $wp_user ) {
                       ?>
                       var user_id = <?= $wp_user->id ?>;
                       var user_display_name = "<?= str_replace('"', '\\\"', $wp_user->display_name) ?>";
                       var user_fau_id = "<?= str_replace('"', '\\\"', $wp_user->fau_id) ?>";
                       var door_permission_until = "<?= get_user_meta( $wp_user->id, 'faufablab_door_permission_until' , true ) ?>";
                       csv_text += `${user_id}\t"${user_display_name}"\t"${user_fau_id}"\t${door_permission_until}\n`;
                       <?php
               }
               ?>
               location.href = "data:text/csv;charset=utf-8," + encodeURIComponent(csv_text);
       }
       </script>
       <?php
}
