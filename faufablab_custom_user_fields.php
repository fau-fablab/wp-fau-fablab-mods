<?php
// add custom user fields (mobile number and FAU ID) and add button to export FAU IDs as CSV.

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
