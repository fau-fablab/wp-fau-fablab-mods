<?php
// Liste der Aktiven: Add a list of members that is visible for all active members

defined('ABSPATH') or die("[!] This script must be executed by a wordpress instance!\r\n");

/**
 * Display a list of all active members (>= editor wordpress role) for all active members whenever the shortcode [ aktivenliste ] is used.
 */
function faufablab_print_listederaktiven( $attrs ) {
	if ( ! current_user_can( "level_3" ) ) {
		return "<h2>Diese Seite dürfen nur Betreuer und Aktive sehen<br><small>(WordPress Rolle >= Redakteur)</smal></h2>";
	// } else if ( current_user_can( "level_10" ) ) {
	// 	fablab_export_fauids();
	}

	// FabLab Aktive == editor or admin.
	$users = get_users( array(
		'role__in' => array( 'editor', 'administrator' )
	) );

	// sort people by first name.
	usort( $users, function( $user1, $user2 ) {
		return $user1->user_firstname > $user2->user_firstname;
	} );

	// group people by door permission date
	$grouped = array();
	foreach ( $users as $user ) {
		$door_perm_until = get_user_meta( $user->ID, "faufablab_door_permission_until", true );
		$grouped[$door_perm_until][] = $user;
	}

	// sort door permission groups descending. No door permission goes last.
	uksort( $grouped, function( $group1, $group2 ) {
		if ( ! $group1 ) {
			return +1;
		} else if ( ! $group2 ) {
			return -1;
		} else {
			return ( $group1 > $group2 ) ? -1 : +1;
		}
	} );

	// iterate over all door permission groups and their users and generate a table with contact data.
	$ret = '';
	foreach ( $grouped as $door_perm_until => $gusers ) {
		$title = ( $door_perm_until ? "Schließberechtigung bis " . $door_perm_until : "Keine Schließberechtigung" );
		$ret = $ret . '<h2>' . $title . '</h2>';

		$ret = $ret . '
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
		foreach ( $gusers as &$user ) {
			// $user->user_nicename,
			$avatar_url = faufablab_profile_image_url( $user );
			$avatar_file_path = faufablab_profile_image_path( $user );

			// construct image for vcard:
			$avatar_vcard_line = 'PHOTO;ENCODING=BASE64;';
			// determine image type
			try {
				// try catch, weil das zu 90% schief geht
				$tmp = explode( ".", $avatar_url );
				$avatar_type = $image_type[end( $tmp )];
				if ( $avatar_type == '' ) {
					throw new Exception('Could not determine image type');
				}
				$avatar_vcard_line .= $avatar_type;
			} catch (Exception $exc) {
				// wird schon jpeg sein
				$avatar_vcard_line .= 'JPEG';
			}
			$avatar_vcard_line .= ':';

			try {
				if ( $avatar_file_path == '' ) {
					throw new Exception('User has no avatar.');
				}
				$avatar_file = fopen( $avatar_file_path, 'r' );
				$avatar_content = fread( $avatar_file, filesize( $avatar_file_path ) );
				fclose( $avatar_file );
				$avatar_base64 = base64_encode( $avatar_content );
				$avatar_vcard_line .= $avatar_base64;
			} catch (Exception $exc) {
				// don't include image in vcard
				$avatar_vcard_line = '';
			}

			$ret = $ret . '
				<tbody>
					<tr>
						<script>
var faufablab_vcard_' . $user->ID . ' = `BEGIN:VCARD
VERSION:2.1
N:' . $user->user_lastname . ';' . $user->user_firstname . ';;;
FN:' . $user->display_name . '
NICKNAME:' . $user->nickname . '
ORG:FAU FabLab
' . $avatar_vcard_line . '
TEL;;CELL:' . $user->mobile . '
EMAIL;WORK;INTERNET:' . $user->user_email . '
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
						<td>' . $user->user_firstname . ' ' . $user->user_lastname . '<br><small>' . $user->nickname . '</small></td>
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
	}

	return $ret;
}
add_shortcode( 'aktivenliste', 'faufablab_print_listederaktiven' );
