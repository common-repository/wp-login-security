<?php

/*  Copyright 2010  Joshua Scott  (email : joshua@joshuascott.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
add_action( 'admin_init' , 'wpls_options_init' );
add_action( 'admin_menu' , 'wpls_options_add_page');


// Init plugin options to white list our options
function wpls_options_init(){
	register_setting( 'wploginsecurity_options', 'wploginsecurity', 'wpls_options_validate');
}

// Add menu page
function wpls_options_add_page() {
	add_options_page('WP Login Security Options', 'WP Login Security', 'manage_options', 'wploginsecurity', 'wpls_options_do_page');
}

// Draw the menu page itself
function wpls_options_do_page() {
	global $wpls_options;
	?>
	<div class="wrap">
		<div class="icon32" id="icon-users"></div>
		<h2>WP Login Security</h2>
		<p>WP Login Security allows each user to maintain a whitelist of IP addresses allowed to login to the site.</p>
		<form method="post" action="options.php">
			<?php settings_fields('wploginsecurity_options'); ?>
			<?php $options = get_option('wploginsecurity'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						Enable WP Login Security?
					</th>
					<td>
						<input name="wploginsecurity[enabled]" type="checkbox" value="1" <?php checked('1', $options['enabled']); ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						Notify Both Blog Admin & User?
					</th>
					<td>
						<input name="wploginsecurity[notify_both]" type="checkbox" value="1" "<?php checked('1', $options['notify_both']); ?>" />
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<h3>Whitelisted IP Addresses</h3>
		<?php $whitelist = get_option($wpls_options['whitelist']); ?>
		<table class="widefat">
			<thead>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Date Activated</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Date Activated</th>
			</tr>
			</tfoot>
			<tbody>
			<?php
				foreach ($whitelist as $user => $arr1) { foreach ($arr1 as $ip => $arr2) {
					if ($arr2['activated'] == true) {
						echo "<tr><td>$user</td><td>$ip</td>";
						echo "<td>{$arr2['date_activated']}</td></tr>";
					}
				}}
			?>
			</tbody>
		</table>
		<h3>Outstanding IP Activations</h3>
		<table class="widefat">
			<thead>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Request Date</th>
				<th>Activation Key</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Request Date</th>
				<th>Activation Key</th>
			</tr>
			</tfoot>
			<tbody>

			<?php
				foreach ($whitelist as $user => $arr1) { foreach ($arr1 as $ip => $arr2) { if ($arr2['activated'] == false) {
						echo "<tr><td>$user</td><td>$ip</td><td>{$arr2['date_requested']}</td><td>{$arr2['ipkey']}</td></tr>";
				}}}
			?>

			</tbody>
		</table>
	</div>
	<?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpls_options_validate($input) {
	// Both options values are checkboxes: 0 (False) or 1 (True)
	$input['enabled'] = ( $input['enabled'] == 1 ? 1 : 0 );
	$input['notify_both'] = ( $input['notify_both'] == 1 ? 1 : 0 );
	return $input;
}


