<?php
/*
Plugin Name: Google Maps by BestWebSoft
Plugin URI: http://bestwebsoft.com/products
Description: Easy to set up and insert Google Maps to your website.
Author: BestWebSoft
Version: 1.3.0
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
* Function to display admin menu.
*/
if ( ! function_exists( 'gglmps_admin_menu' ) ) {
	function gglmps_admin_menu() {
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', __( 'Google Maps Settings', 'gglmps' ), 'Google Maps', 'manage_options', 'bws-google-maps.php', 'gglmps_settings_page' );
		$hook = add_menu_page( 'Google Maps', 'Google Maps', 'edit_posts', 'gglmps_manager', 'gglmps_manager_page', plugins_url( "bws_menu/images/px.png", __FILE__ ), '54.1' );
		add_submenu_page( 'gglmps_manager', __( 'Google Maps Editor', 'gglmps' ), __( 'Add New', 'gglmps' ), 'manage_options', 'gglmps_editor', 'gglmps_editor_page' );
		add_action( "load-$hook", 'gglmps_screen_options' );
	}
}

/*
* Function to add localization to the plugin.
*/
if ( ! function_exists ( 'gglmps_init' ) ) {
	function gglmps_init() {
		global $gglmps_plugin_info;
		/* Internationalization. */
		load_plugin_textdomain( 'gglmps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		
		if ( empty( $gglmps_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$gglmps_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $gglmps_plugin_info, '3.3' );

		if ( ! is_admin() || isset( $_GET['page'] ) && ( $_GET['page'] == 'bws-google-maps.php' || $_GET['page'] == 'gglmps_manager' || $_GET['page'] == 'gglmps_editor' ) ) {
			gglmps_default_options();
		}
	}
}

/*
* Function to add plugin version.
*/
if ( ! function_exists ( 'gglmps_admin_init' ) ) {
	function gglmps_admin_init() {
		global $bws_plugin_info, $gglmps_plugin_info;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '124', 'version' => $gglmps_plugin_info['Version'] );
	}
}

/*
* Function to set up default options.
*/
if ( ! function_exists ( 'gglmps_default_options' ) ) {
	function gglmps_default_options() {
		global $gglmps_options, $gglmps_default_options, $gglmps_maps, $gglmps_plugin_info;

		$gglmps_default_options = array(
			'plugin_option_version' => $gglmps_plugin_info['Version'],
			'api_key'               => '',
			'language'              => 'en',
			'additional_options'    => 0,
			'basic'                 => array(
				'width'     => 300,
				'height'    => 300,
				'alignment' => 'left',
				'map_type'  => 'roadmap',
				'tilt45'    => 1,
				'zoom'      => 3
			),
			'controls'              => array(
				'map_type'            => 1,
				'pan'                 => 1,
				'rotate'              => 1,
				'zoom'                => 1,
				'scale'               => 1
			)
		);
		if ( ! get_option( 'gglmps_options' ) )
			add_option( 'gglmps_options', $gglmps_default_options );

		$gglmps_options = get_option( 'gglmps_options' );

		if ( ! get_option( 'gglmps_maps' ) )
			add_option( 'gglmps_maps', array() );

		$gglmps_maps = get_option( 'gglmps_maps' );

		if ( ! isset( $gglmps_options['plugin_option_version'] ) || $gglmps_options['plugin_option_version'] != $gglmps_plugin_info['Version'] ) {
			$gglmps_options = array_merge( $gglmps_default_options, $gglmps_options );
			$gglmps_options['plugin_option_version'] = $gglmps_plugin_info['Version'];
			update_option( 'gglmps_options', $gglmps_options );
		}
	}
}


/*
* Function to display plugin main settings page.
*/
if ( ! function_exists( 'gglmps_settings_page' ) ) {
	function gglmps_settings_page() {
		global $gglmps_options, $gglmps_default_options, $gglmps_plugin_info, $wp_version;
		$plugin_basename = plugin_basename( __FILE__ );
		$gglmps_lang_codes = array(
			'ar' => 'Arabic', 'eu' => 'Basque', 'bn' => 'Bengali', 'bg' => 'Bilgarian', 'ca' => 'Catalan', 'zh-CN' => 'Chinese (Simplified)', 'zh-TW' => 'Chinese (Traditional)',
			'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch', 'en' => 'English', 'en-AU' => 'English (Australian)', 'en-GB' => 'English (Great Britain)',
			'fa' => 'Farsi', 'fil' => 'Filipino', 'fi' => 'Finnish', 'fr' => 'French', 'gl' => 'Galician', 'de' => 'German', 'el' => 'Greek', 'gu' => 'Gujarati', 'iw' => 'Hebrew',
			'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'kn' => 'Kannada', 'ko' => 'Korean', 'lv' => 'Latvian',
			'lt' => 'Lithuanian', 'ml' => 'Malayalam', 'mr' => 'Marthi', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese (Brazil)',
			'pt-PT' => 'Portuguese (Portugal)', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'sv' => 'Swedish',
			'tl' => 'Tagalog', 'ta' => 'Tamil', 'te' => 'Telugu', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'vi' => 'Vietnamese'
		);
		$error = $message = "";

		if ( isset( $_REQUEST['gglmps_settings_submit'] ) && check_admin_referer( $plugin_basename ) ) {
			$gglmps_options = array(
				'api_key'            => isset( $_REQUEST['gglmps_main_api_key'] ) ? trim( stripslashes( esc_html( $_REQUEST['gglmps_main_api_key'] ) ) ) : $gglmps_default_options['api_key'],
				'language'           => isset( $_REQUEST['gglmps_main_language'] ) ? $_REQUEST['gglmps_main_language'] : $gglmps_default_options['language'],
				'additional_options' => isset( $_REQUEST['gglmps_settings_additional_options'] ) ? 1 : 0,
				'basic'              => array(
					'width'     => isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) > 150 ? intval( $_REQUEST['gglmps_basic_width'] ) : 150,
					'height'    => isset( $_REQUEST['gglmps_basic_height'] ) && intval( $_REQUEST['gglmps_basic_height'] ) > 150 ? intval( $_REQUEST['gglmps_basic_height'] ) : 150,
					'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) ? $_REQUEST['gglmps_basic_alignment'] : $gglmps_default_options['basic']['alignment'],
					'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) ? $_REQUEST['gglmps_basic_map_type'] : $gglmps_default_options['basic']['map_type'],
					'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
					'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) && is_numeric( intval( $_REQUEST['gglmps_basic_zoom'] ) ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $gglmps_default_options['basic']['zoom']
				),
				'controls'           => array(
					'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
					'pan'                 => isset( $_REQUEST['gglmps_control_pan'] ) ? 1 : 0,
					'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
					'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
					'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
				)
			);
			$message = __( "Settings saved.", 'gglmps' );
			update_option( 'gglmps_options', $gglmps_options );
		}

		/*## Add restore function */
		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$gglmps_options = $gglmps_default_options;
			update_option( 'gglmps_options', $gglmps_options );			
			$message = __( 'All plugin settings were restored.', 'gglmps' );
		}		
		/* end ##*/

		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
		} ?>
		<div id="gglmps_settings_wrap" class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2 class="gglmps_settings_title"><?php _e( 'Google Maps Settings', 'gglmps' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>"  href="admin.php?page=bws-google-maps.php"><?php _e( 'Settings', 'gglmps' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/bws-google-maps/faq" target="_blank"><?php _e( 'FAQ', 'gglmps' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=bws-google-maps.php&amp;action=go_pro"><?php _e( 'Go PRO', 'gglmps' ); ?></a>
			</h2>
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'gglmps' ),
							__( 'Google Maps only works with JavaScript enabled', 'gglmps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<div class="updated fade"<?php if ( '' == $message || "" != $error ) echo " style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
			<div id="gglmps_update_notice" class="updated fade">
				<p><?php _e( "The plugin's settings have been changed. Please, don't forget to click the 'Save Settings' button.", 'gglmps' ); ?></p>
			</div><!-- #ggglmps_update_notice -->
			<?php if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { ?>
					<div id="gglmps_settings_notice" class="updated">
						<p>
							<?php _e( 'These settings are used as default when you create a new map.', 'gglmps' ); ?><br />
							<?php printf(
								'%1$s <a href="admin.php?page=gglmps_editor">%2$s</a> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s',
								__( 'In the', 'gglmps' ),
								__( 'Google Maps editor', 'gglmps' ),
								__( 'you can create a new map and in the', 'gglmps' ),
								__( 'Google Maps manager', 'gglmps' ),
								__( 'you can find maps that have been previously saved.', 'gglmps' )
							 ); ?><br />
							<?php printf(
								'%1$s [bws_googlemaps id=*], %2$s',
								__( 'If you want to insert the map in any place on the site, please use the shortcode', 'gglmps' ),
								__( 'where * stands for map ID.', 'gglmps' )
							); ?><br />
						</p>
					</div><!-- #gglmps_settings_notice -->
					<form id="gglmps_settings_form" name="gglmps_settings_form" method="post" action="admin.php?page=bws-google-maps.php">
						<table class="gglmps_settings_table form-table">
							<tbody>
								<tr valign="middle">
									<th><?php _e( 'API Key', 'gglmps' ); ?></th>
									<td>
										<div style="max-width: 600px;">
											<input id="gglmps_main_api_key" name="gglmps_main_api_key" type="text" maxlength='250' value="<?php echo $gglmps_options['api_key']; ?>">
											<span class="gglmps_settings_tooltip">
												<?php printf(
													'%1$s <a href="https://developers.google.com/maps/documentation/javascript/usage#usage_limits" target="_blank">%2$s</a>, %3$s <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key" target="_blank">%4$s</a>.',
													__( 'Using an API key enables you to monitor your application Maps API usage, and ensures that Google can contact you about your application if necessary. If your application Maps API usage exceeds the', 'gglmps' ),
													__( 'Usage Limits', 'gglmps' ),
													__( 'you must load the Maps API using an API key in order to purchase additional quota. How to create a API key you can find', 'gglmps' ),
													__( 'here', 'gglmps' )
												); ?>
											</span>
										</div>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Language', 'gglmps' ); ?></th>
									<td>
										<select id="gglmps_main_language" name="gglmps_main_language">
											<?php foreach ( $gglmps_lang_codes as $key => $lang ) {
												printf(
													'<option value="%1$s" %2$s>%3$s</option>',
													$key,
													$gglmps_options['language'] == $key ? 'selected="selected"' : '',
													$lang
												);
											} ?>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Width (px)', 'gglmps' ); ?></th>
									<td><input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="150" max="10000" value="<?php echo $gglmps_options['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'gglmps' ); ?>"></td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_height"><?php _e( 'Height (px)', 'gglmps' ); ?></label></th>
									<td>
										<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="10000" value="<?php echo $gglmps_options['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'gglmps' ); ?>">
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_alignment"><?php _e( 'Alignment', 'gglmps' ); ?></label></th>
									<td>
										<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
											<option value="left" <?php if ( $gglmps_options['basic']['alignment'] == 'left' ) echo 'selected'; ?>><?php _e( 'Left', 'gglmps' ); ?></option>
											<option value="center" <?php if ( $gglmps_options['basic']['alignment'] == 'center' ) echo 'selected'; ?>><?php _e( 'Center', 'gglmps' ); ?></option>
											<option value="right" <?php if ( $gglmps_options['basic']['alignment'] == 'right' ) echo 'selected'; ?>><?php _e( 'Right', 'gglmps' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_map_type"><?php _e( 'Type', 'gglmps' ); ?></label></th>
									<td>
										<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
											<option value="roadmap" <?php if ( $gglmps_options['basic']['map_type'] == 'roadmap' ) echo 'selected'; ?>><?php _e( 'Roadmap', 'gglmps' ); ?></option>
											<option value="terrain" <?php if ( $gglmps_options['basic']['map_type'] == 'terrain' ) echo 'selected'; ?>><?php _e( 'Terrain', 'gglmps' ); ?></option>
											<option value="satellite" <?php if ( $gglmps_options['basic']['map_type'] == 'satellite' ) echo 'selected'; ?>><?php _e( 'Satellite', 'gglmps' ); ?></option>
											<option value="hybrid" <?php if ( $gglmps_options['basic']['map_type'] == 'hybrid' ) echo 'selected'; ?>><?php _e( 'Hybrid', 'gglmps' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_tilt45"><?php _e( 'View', 'gglmps' ); ?>&nbsp;45&deg;</label></th>
									<td>
										<input id="gglmps_basic_tilt45" name="gglmps_basic_tilt45" type="checkbox" <?php if ( $gglmps_options['basic']['tilt45'] == 1 ) echo 'checked="checked"'; ?> />
										<span class="gglmps_settings_tooltip"><?php _e( 'This option is only available for the types of map Satellite and Hybrid (if such snapshots are available).', 'gglmps' ); ?></span>
									</td>
								</tr>
								<tr valign="middle">
									<th><label for="gglmps_basic_auto_zoom"><?php _e( 'Zoom', 'gglmps' ); ?></label></th>
									<td>
										<div id="gglmps_zoom_wrap">
											<div id="gglmps_zoom_slider"></div>
											<span id="gglmps_zoom_value"></span>
										</div>
										<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $gglmps_options['basic']['zoom']; ?>">
									</td>
								</tr>
								<tr valign="middle">
									<th>
										<input id="gglmps_settings_additional_options" name="gglmps_settings_additional_options" type="checkbox" <?php if ( $gglmps_options['additional_options'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_settings_additional_options"><?php _e( 'Controls options', 'gglmps' ); ?></label>
									</th>
									<td>
										<span class="gglmps_settings_tooltip"><?php _e( 'Visibility and actions controls of the map.', 'gglmps' ); ?></span>
									</td>
								</tr>
								<tr class="gglmps_settings_additional_options" valign="middle">
									<th>&nbsp;</th>
									<td>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_map_type" name="gglmps_control_map_type" type="checkbox" <?php if ( $gglmps_options['controls']['map_type'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_map_type"><?php _e( 'Type', 'gglmps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_pan" name="gglmps_control_pan" type="checkbox" <?php if ( $gglmps_options['controls']['pan'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_pan"><?php _e( 'Pan', 'gglmps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_rotate" name="gglmps_control_rotate" type="checkbox" <?php if ( $gglmps_options['controls']['rotate'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_rotate"><?php _e( 'Rotate', 'gglmps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_zoom" name="gglmps_control_zoom" type="checkbox" <?php if ( $gglmps_options['controls']['zoom'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_zoom"><?php _e( 'Zoom', 'gglmps' ); ?></label>
										</p>
										<p class="gglmps_settings_additional_option">
											<input id="gglmps_control_scale" name="gglmps_control_scale" type="checkbox" <?php if ( $gglmps_options['controls']['scale'] == 1 ) echo 'checked="checked"'; ?> />
											<label for="gglmps_control_scale"><?php _e( 'Scale', 'gglmps' ); ?></label>
										</p>
									</td>
								</tr>
							</tbody>
						</table><!-- .gglmps_settings_table -->
						<div class="bws_pro_version_bloc">
							<div class="bws_pro_version_table_bloc">
								<div class="bws_table_bg"></div>
								<table class="form-table bws_pro_version">
									<tr valign="middle">
										<th><?php _e( 'Zoom', 'gglmps' ); ?></th>
										<td>
											<p class="gglmps-zoom-container">
												<input disabled="disabled" name="gglmpspr_basic_auto_zoom" type="checkbox" />
												<label><?php _e( 'Auto', 'gglmps' ); ?></label>
												<span class="gglmps_settings_tooltip"><?php _e( 'The map will be scaled to display all markers.', 'gglmps' ); ?></span>
											</p>
										</td>
									</tr>
									<tr valign="middle">
										<th><?php _e( 'Controls options', 'gglmps' ); ?></th>
										<td>
											<p class="gglmps_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_street_view" type="checkbox" />
												<label><?php _e( 'Street View', 'gglmps' ); ?></label>
											</p>
											<p class="gglmpspr_settings_additional_option">
												<input disabled="disabled" name="gglmpspr_control_overview_map" type="checkbox" />
												<label><?php _e( 'Overview Map', 'gglmps' ); ?></label>
											</p>
											<p class="gglmpspr_settings_additional_option">
												<input disabled="disabled" name="gglmpspr_control_overview_map_opened" type="checkbox" />
												<label><?php _e( 'Overview Map Opened', 'gglmps' ); ?></label>
											</p>
											<p class="gglmpspr_settings_additional_option">
												<input disabled="disabled" name="gglmpspr_control_map_draggable" type="checkbox" />
												<label><?php _e( 'Draggable', 'gglmps' ); ?></label>
											</p>
											<p class="gglmpspr_settings_additional_option">
												<input disabled="disabled" name="gglmpspr_control_double_click" type="checkbox" />
												<label><?php _e( 'Double Click', 'gglmps' ); ?></label>
											</p>
											<p class="gglmpspr_settings_additional_option">
												<input disabled="disabled" name="gglmpspr_control_scroll_wheel" type="checkbox" />
												<label><?php _e( 'Scroll Wheel', 'gglmps' ); ?></label>
											</p>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row" colspan="2">
											* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'gglmps' ); ?>
										</th>
									</tr>
								</table>
							</div>
							<div class="bws_pro_version_tooltip">
								<div class="bws_info">
									<?php _e( 'Unlock premium options by upgrading to PRO version.', 'gglmps' ); ?>
									<a target="_blank" href="http://bestwebsoft.com/products/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'gglmps' ); ?></a>
								</div>
								<a class="bws_button" href="http://bestwebsoft.com/products/bws-google-maps/buy/?k=5ae35807d562bf6b5c67db88fefece60&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Maps Pro">
									<?php _e( 'Go', 'gglmps' ); ?> <strong>PRO</strong>
								</a>
								<div class="clear"></div>
							</div>
						</div>
						<p>
							<?php wp_nonce_field( $plugin_basename ); ?>
							<input class="button-primary" id="gglmps_settings_submit" name="gglmps_settings_submit" type="submit" value="<?php _e( 'Save Settings', 'gglmps' ) ?>" />
						</p>
					</form><!-- #gglmps_settings_form -->
					<?php bws_form_restore_default_settings( $plugin_basename );
					bws_plugin_reviews_block( $gglmps_plugin_info['Name'], 'bws-google-maps' );
				}
			} elseif ( 'go_pro' == $_GET['action'] ) { 
				bws_go_pro_tab( $gglmps_plugin_info, $plugin_basename, 'bws-google-maps.php', 'bws-google-maps-pro.php', 'bws-google-maps-pro/bws-google-maps-pro.php', 'bws-google-maps', 'f546edd672c2e16f8359dcb48f9d2fff', '124', isset( $go_pro_result['pro_plugin_is_activated'] ) ); 
			} ?>
		</div><!-- #gglmps_settings_wrap -->
	<?php }
}

/*
* Function to display plugin manager page.
*/
if ( ! function_exists( 'gglmps_manager_page' ) ) {
	function gglmps_manager_page() {
		global $gglmps_maps;
		$gglmps_manager = new Gglmps_Manager();
		if ( $gglmps_manager->current_action() ) {
			$gglmps_manager_action = $gglmps_manager->current_action();
		} else {
			$gglmps_manager_action = isset( $_REQUEST['gglmps_manager_action'] ) ? $_REQUEST['gglmps_manager_action'] : '';
		}
		$gglmps_manager_mapid = isset( $_REQUEST['gglmps_manager_mapid'] ) ? $_REQUEST['gglmps_manager_mapid'] : 0;
		switch( $gglmps_manager_action ) {
			case 'delete':
				$gglmps_mapids = is_array( $gglmps_manager_mapid ) ? $gglmps_manager_mapid : array( $gglmps_manager_mapid );
				foreach ( $gglmps_mapids as $gglmps_mapid ) {
					if ( isset( $gglmps_maps[ $gglmps_mapid ] ) ) {
						$gglmps_maps[ $gglmps_mapid ] = NULL;
					}
				}
				update_option( 'gglmps_maps', $gglmps_maps );
				break;
			default:
				break;
		}
		krsort( $gglmps_maps );
		$gglmps_result = array();
		foreach ( $gglmps_maps as $key => $gglmps_map ) {
			if ( isset( $gglmps_map ) ) {
				$gglmps_result[ $key ] = array(
					'gglmps-id' => $key,
					'title'     => sprintf( '<a class="row-title" href="admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=%1$d">%2$s</a>', $key, $gglmps_map['title'] ),
					'shortcode' => sprintf( '[bws_googlemaps id=%d]', $key ),
					'date'      => $gglmps_map['date']
				);
			}
		}
		$gglmps_manager->gglmps_table_data = $gglmps_result;
		$gglmps_manager->prepare_items(); ?>
		<div class="wrap">
			<div class="icon32 icon32-gglmps" id="icon-options-general"></div>
			<h2 class="gglmps_manager_title">
				<?php _e( 'Google Maps', 'gglmps' ); ?>
				<a class="add-new-h2" href="admin.php?page=gglmps_editor"><?php _e( 'Add New', 'gglmps' )?></a>
			</h2>
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'gglmps' ),
							__( 'Google Maps only works with JavaScript enabled', 'gglmps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<form method="get">
				<?php $gglmps_manager->display(); ?>
				<input type="hidden" name="page" value="gglmps_manager"/>
				<!-- <input type="hidden" name="gglmps_manager_action" value="delete"/> -->
			</form>
		</div><!-- .wrap -->
	<?php }
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) )
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/*
* Built-in WP class WP_List_Table.
*/
if ( class_exists( 'WP_List_Table' ) ) {
	class Gglmps_Manager extends WP_List_Table {
		public $gglmps_table_data;

		/*
		* Constructor of class.
		*/
		function __construct() {
			global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'map', 'gglmps' ),
					'plural'    => __( 'maps', 'gglmps' ),
					'ajax'      => false
				)
			);
		}

		/*
		* Function to label the columns.
		*/
		function get_columns() {
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'gglmps-id'	=> __( 'ID', 'gglmps' ),
				'title'     => __( 'Title', 'gglmps' ),
				'shortcode' => __( 'Shortcode', 'gglmps' ),
				'date'      => __( 'Date', 'gglmps' )
			);
			return $columns;
		}

		/*
		* Function to display data in columns.
		*/
		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'gglmps-id':
				case 'title':
				case 'shortcode':
				case 'date':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/*
		* Function to add checkboxes in the column to the items.
		*/
		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="gglmps_manager_mapid[]" value="%d" />', $item['gglmps-id'] );
		}

		/*
		* Function to add advanced menus for items.
		*/
		function column_title( $item ) {
			$gglmps_manager_paged = isset( $_GET['paged'] ) ? '&paged=' . $_GET['paged'] : '';
			$actions = array(
				'edit'   => sprintf( '<a href="admin.php?page=gglmps_editor&gglmps_editor_action=%1$s&gglmps_editor_mapid=%2$d">%3$s</a>', 'edit', $item['gglmps-id'],  __( 'Edit', 'gglmps' ) ),
				'delete' => sprintf( '<a href="admin.php?page=gglmps_manager&gglmps_manager_action=%1$s&gglmps_manager_mapid=%2$d%3$s">%4$s</a>', 'delete', $item['gglmps-id'], $gglmps_manager_paged, __( 'Delete', 'gglmps' ) )
			);
			return sprintf( '%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
		}

		/*
		* Function to display message if items not found.
		*/
		function no_items() {
			printf('<i>%s</i>', __( 'Maps not found.', 'gglmps' ) );
		}

		/*
		* Function for prepare items to display.
		*/
		function prepare_items() {
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				array()
			);
			$user = get_current_user_id();
			$screen = get_current_screen();
			$option = $screen->get_option('per_page', 'option');
			$per_page = get_user_meta($user, $option, true);
			if ( empty ( $per_page) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
			$current_page = $this->get_pagenum();
			$total_items = count( $this->gglmps_table_data );
			$this->items = array_slice( $this->gglmps_table_data, ( ( $current_page - 1 ) * $per_page ), $per_page );
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page
			) );
		}

		/*
		* Function to add support for group actions.
		*/
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'gglmps' )
			);
			return $actions;
		}
	}
}

/*
* Function to display plugin editor page.
*/
if ( ! function_exists( 'gglmps_editor_page' ) ) {
	function gglmps_editor_page() {
		global $gglmps_options, $gglmps_maps, $gglmps_plugin_info, $wp_version;
		$gglmps_editor_submit = array(
			'add'    => __( 'Save Map', 'gglmps' ),
			'edit'   => __( 'Update Map', 'gglmps' )
		);
		$gglmps_editor_action = isset( $_REQUEST['gglmps_editor_action'] ) ? $_REQUEST['gglmps_editor_action'] : 'new';
		$gglmps_editor_mapid = isset( $_REQUEST['gglmps_editor_mapid'] ) ? $_REQUEST['gglmps_editor_mapid'] : '';
		$gglmps_editor_shortcode = 0;
		$gglmps_editor_status = 0;
		switch ( $gglmps_editor_action ) {
			case 'new':
				$gglmps_map_title = '';
				$gglmps_map_data = array(
					'additional_options' => $gglmps_options['additional_options'],
					'basic'              => array(
						'width'     => $gglmps_options['basic']['width'],
						'height'    => $gglmps_options['basic']['height'],
						'alignment' => $gglmps_options['basic']['alignment'],
						'map_type'  => $gglmps_options['basic']['map_type'],
						'tilt45'    => $gglmps_options['basic']['tilt45'],
						'zoom'      => $gglmps_options['basic']['zoom']
					),
					'controls'           => array(
						'map_type'            => $gglmps_options['controls']['map_type'],
						'pan'                 => $gglmps_options['controls']['pan'],
						'rotate'              => $gglmps_options['controls']['rotate'],
						'zoom'                => $gglmps_options['controls']['zoom'],
						'scale'               => $gglmps_options['controls']['scale']
					),
					'markers' => array()
				);
				$gglmps_editor_action = 'add';
				$gglmps_editor_form_action = 'admin.php?page=gglmps_editor&noheader=true';
				break;
			case 'add':
				if ( isset( $_REQUEST['gglmps_editor_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ) ) ) {
 					$gglmps_map_title = isset( $_REQUEST['gglmps_map_title'] ) && ! empty( $_REQUEST['gglmps_map_title'] ) ? trim( stripslashes( esc_html( $_REQUEST['gglmps_map_title'] ) ) ) : __( 'No title', 'gglmps' );
					$gglmps_map_data = array(
						'additional_options' => isset( $_REQUEST['gglmps_editor_additional_options'] ) ? 1 : 0,
						'basic'              => array(
							'width'     => isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) > 150 ? intval( $_REQUEST['gglmps_basic_width'] ) : 150,
							'height'    => isset( $_REQUEST['gglmps_basic_height'] ) && intval( $_REQUEST['gglmps_basic_height'] ) > 150 ? intval( $_REQUEST['gglmps_basic_height'] ) : 150,
							'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) ? $_REQUEST['gglmps_basic_alignment'] : 'left',
							'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) ? $_REQUEST['gglmps_basic_map_type'] : 'roadmap',
							'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
							'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) && is_numeric( intval( $_REQUEST['gglmps_basic_zoom'] ) ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $gglmps_options['basic']['zoom']
						),
						'controls'           => array(
							'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
							'pan'                 => isset( $_REQUEST['gglmps_control_pan'] ) ? 1 : 0,
							'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
							'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
							'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
						),
						'markers' => array()
					);
					if ( isset( $_REQUEST['gglmps_list_marker_latlng'] ) && isset( $_REQUEST['gglmps_list_marker_location'] ) ) {
						$gglmps_marker_latlng = $_REQUEST['gglmps_list_marker_latlng'];
						$gglmps_marker_location = $_REQUEST['gglmps_list_marker_location'];
						$gglmps_marker_tooltip = $_REQUEST['gglmps_list_marker_tooltip'];
						foreach ( $gglmps_marker_location as $key => $value ) {
                            $gglmps_marker_location[ $key ] = stripslashes( esc_html( $value ) );
                            $gglmps_marker_latlng[ $key ] = stripslashes( esc_html( $gglmps_marker_latlng[ $key ] ) );
                            $gglmps_marker_tooltip[ $key ] = stripslashes( esc_html( $gglmps_marker_tooltip[ $key ] ) );
                        }
						$gglmps_map_data['markers'] = array_map( null, $gglmps_marker_latlng, $gglmps_marker_location, $gglmps_marker_tooltip );
					}
					if ( count( $gglmps_maps ) == 0 ) {
						$gglmps_editor_mapid = 1;
					} else {
						end( $gglmps_maps );
						$gglmps_editor_mapid = key( $gglmps_maps ) + 1;
					}
					$gglmps_maps[ $gglmps_editor_mapid ] = array(
						'title' => $gglmps_map_title,
						'data'  => $gglmps_map_data,
						'date'  => date( 'Y/m/d' )
					);
					update_option( 'gglmps_maps', $gglmps_maps );
					header( 'Location: admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=' . $gglmps_editor_mapid );
					exit;
				}
				break;
			case 'edit':
				if ( isset( $_REQUEST['gglmps_editor_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ) ) ) {
 					$gglmps_map_title = isset( $_REQUEST['gglmps_map_title'] ) && ! empty( $_REQUEST['gglmps_map_title'] ) ? trim( stripslashes( esc_html( $_REQUEST['gglmps_map_title'] ) ) ) : __( 'No title', 'gglmps' );
					$gglmps_map_data = array(
						'additional_options' => isset( $_REQUEST['gglmps_editor_additional_options'] ) ? 1 : 0,
						'basic'              => array(
							'width'     => isset( $_REQUEST['gglmps_basic_width'] ) && intval( $_REQUEST['gglmps_basic_width'] ) > 150 ? intval( $_REQUEST['gglmps_basic_width'] ) : 150,
							'height'    => isset( $_REQUEST['gglmps_basic_height'] ) && intval( $_REQUEST['gglmps_basic_height'] ) > 150 ? intval( $_REQUEST['gglmps_basic_height'] ) : 150,
							'alignment' => isset( $_REQUEST['gglmps_basic_alignment'] ) ? $_REQUEST['gglmps_basic_alignment'] : 'left',
							'map_type'  => isset( $_REQUEST['gglmps_basic_map_type'] ) ? $_REQUEST['gglmps_basic_map_type'] : 'roadmap',
							'tilt45'    => isset( $_REQUEST['gglmps_basic_tilt45'] ) ? 1 : 0,
							'zoom'      => isset( $_REQUEST['gglmps_basic_zoom'] ) && is_numeric( intval( $_REQUEST['gglmps_basic_zoom'] ) ) ? intval( $_REQUEST['gglmps_basic_zoom'] ) : $gglmps_options['basic']['zoom']
						),
						'controls'           => array(
							'map_type'            => isset( $_REQUEST['gglmps_control_map_type'] ) ? 1 : 0,
							'pan'                 => isset( $_REQUEST['gglmps_control_pan'] ) ? 1 : 0,
							'rotate'              => isset( $_REQUEST['gglmps_control_rotate'] ) ? 1 : 0,
							'zoom'                => isset( $_REQUEST['gglmps_control_zoom'] ) ? 1 : 0,
							'scale'               => isset( $_REQUEST['gglmps_control_scale'] ) ? 1 : 0
						),
						'markers' => array()
					);
					if ( isset( $_REQUEST['gglmps_list_marker_latlng'] ) && isset( $_REQUEST['gglmps_list_marker_location'] ) ) {
						$gglmps_marker_latlng = $_REQUEST['gglmps_list_marker_latlng'];
						$gglmps_marker_location = $_REQUEST['gglmps_list_marker_location'];
						$gglmps_marker_tooltip = $_REQUEST['gglmps_list_marker_tooltip'];
						foreach ( $gglmps_marker_location as $key => $value ) {
                            $gglmps_marker_location[ $key ] = stripslashes( esc_html( $value ) );
                            $gglmps_marker_latlng[ $key ] = stripslashes( esc_html( $gglmps_marker_latlng[ $key ] ) );
                            $gglmps_marker_tooltip[ $key ] = stripslashes( esc_html( $gglmps_marker_tooltip[ $key ] ) );
                        }
						$gglmps_map_data['markers'] = array_map( null, $gglmps_marker_latlng, $gglmps_marker_location, $gglmps_marker_tooltip );
					};
					if ( isset( $gglmps_maps[ $gglmps_editor_mapid ] ) ) {
						$gglmps_maps[ $gglmps_editor_mapid ] = array(
							'title' => $gglmps_map_title,
							'data'  => $gglmps_map_data,
							'date'  => $gglmps_maps[ $gglmps_editor_mapid ]['date']
						);
						update_option( 'gglmps_maps', $gglmps_maps );
						$gglmps_editor_status = 1;
					} else {
						wp_die(
							sprintf(
								'<div class="error"><p>%1$s <strong>ID#%2$s</strong> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s <a href="admin.php?page=gglmps_editor">%6$s</a>.</p></div>',
								__( 'Map with', 'gglmps' ),
								$gglmps_editor_mapid,
								__( 'not found! You can return to the', 'gglmps' ),
								__( 'Google Maps manager', 'gglmps' ),
								__( 'or create new map in the', 'gglmps' ),
								__( 'Google Maps editor', 'gglmps' )
							)
						);
					}
				}
				if ( isset( $gglmps_maps[ $gglmps_editor_mapid ] ) ) {
					$gglmps_map_title = $gglmps_maps[ $gglmps_editor_mapid ]['title'];
					$gglmps_map_data = $gglmps_maps[ $gglmps_editor_mapid ]['data'];
					$gglmps_editor_shortcode = 1;
				} else {
					wp_die(
						sprintf(
							'<div class="error"><p>%1$s <strong>ID#%2$s</strong> %3$s <a href="admin.php?page=gglmps_manager">%4$s</a> %5$s <a href="admin.php?page=gglmps_editor">%6$s</a>.</p></div>',
							__( 'Map with', 'gglmps' ),
							$gglmps_editor_mapid,
							__( 'not found! You can return to the', 'gglmps' ),
							__( 'Google Maps manager', 'gglmps' ),
							__( 'or create new map in the', 'gglmps' ),
							__( 'Google Maps editor', 'gglmps' )
						)
					);
				}
				$gglmps_editor_action = 'edit';
				$gglmps_editor_form_action = 'admin.php?page=gglmps_editor&gglmps_editor_action=edit&gglmps_editor_mapid=' . $gglmps_editor_mapid;
				break;
			default: ?>
				<script type="text/javascript">
					document.location.href="admin.php?page=gglmps_manager";
				</script>
				<?php exit;
				break;
		} ?>
		<div id="gglmps_editor_wrap" class="wrap">
			<noscript>
				<div class="error">
					<p>
						<?php printf(
							'<strong>%1$s</strong> %2$s.',
							__( 'WARNING:', 'gglmps' ),
							__( 'Google Maps only works with JavaScript enabled', 'gglmps' )
						); ?>
					</p>
				</div><!-- .error -->
			</noscript><!-- noscript -->
			<?php if ( $gglmps_editor_status == 1 ) { ?>
				<div class="updated">
					<p>
						<?php _e( 'Map has been updated.', 'gglmps' ); ?>
					</p>
				</div><!-- .updated -->
			<?php } ?>
			<?php if ( $gglmps_editor_shortcode == 1 ) { ?>
				<div id="gglmps_editor_notice" class="updated">
					<p>
						<?php _e( 'To insert this map use a shortcode', 'gglmps' ); ?> <strong>[bws_googlemaps id=<?php echo $gglmps_editor_mapid; ?>]</strong>
					</p>
				</div><!-- #gglmps_editor_notice -->
			<?php } ?>
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2 class="gglmps_editor_title"><?php _e( 'Google Maps Editor', 'gglmps' ); ?></h2>
			<div id="gglmps_editor_settings">
				<form id="gglmps_editor_form" name="gglmps_editor_form" method="post" action="<?php echo $gglmps_editor_form_action; ?>">
					<table class="gglmps_editor_table form-table">
						<tbody>
							<tr valign="middle">
								<th><label for="gglmps_map_title"><?php _e( 'Map Title', 'gglmps' ); ?></label></th>
								<td>
									<input id="gglmps_map_title" name="gglmps_map_title" type="text" maxlength="64" value="<?php echo $gglmps_map_title; ?>" placeholder="<?php _e( 'Enter title', 'gglmps' ); ?>" />
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><label for="gglmps_marker_location"><?php _e( 'Marker Location', 'gglmps' ); ?></label></th>
								<td>
									<input id="gglmps_marker_location" type="text" placeholder="<?php _e( 'Enter location or coordinates', 'gglmps' ); ?>" />
									<span class="gglmps_editor_tooltip">
										<?php _e( 'You should enter coordinates in decimal degrees with no spaces or you can use a right click on the preview map to get coordinates automatically. Example coordinates: 41.40338,2.17403.', 'gglmps' ); ?>
									</span>
									<input id="gglmps_marker_latlng" type="hidden" />
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><label for="gglmps_marker_tooltip"><?php _e( 'Marker Tooltip', 'gglmps' ); ?></label></th>
								<td>
									<textarea id="gglmps_marker_tooltip" placeholder="<?php _e( 'Enter tooltip', 'gglmps' ); ?>"></textarea>
									<span class="gglmps_editor_tooltip"><?php _e( 'You can use HTML tags and attributes.', 'gglmps' ); ?></span>
									<p>
										<input class="button-secondary" id="gglmps_marker_add" type="button" value="<?php _e( 'Add marker to list', 'gglmps' ); ?>" />
										<input class="button-secondary" id="gglmps_marker_update" type="button" value="<?php _e( 'Update marker', 'gglmps' ); ?>" />
										<input class="button-secondary" id="gglmps_marker_cancel" type="button" value="<?php _e( 'Cancel', 'gglmps' ); ?>" />
									</p>
								</td>
							</tr>
							<tr class="gglmps_markers_wrap" valign="middle">
								<th><?php _e( 'Markers List', 'gglmps' ); ?></th>
								<td>
									<ul id="gglmps_markers_container">
										<?php if ( count( $gglmps_map_data['markers'] ) == 0 ) { ?>
											<li class="gglmps_no_markers">
												<?php _e( 'No markers', 'gglmps' ); ?>
											</li>
										<?php } else {
											foreach ( $gglmps_map_data['markers'] as $key => $gglmps_marker ) { ?>
												<li class="gglmps_marker">
													<div class="gglmps_marker_control">
														<span class="gglmps_marker_delete"><?php _e( 'Delete', 'gglmps' ); ?></span>
														<span class="gglmps_marker_edit"><?php _e( 'Edit', 'gglmps' ); ?></span>
														<span class="gglmps_marker_find"><?php _e( 'Find', 'gglmps' ); ?></span>
														<span class="gglmps_marker_latlng">[<?php echo stripcslashes( $gglmps_marker[0] ); ?>]</span>
													</div>
													<div class="gglmps_marker_data">
														<div class="gglmps_marker_location"><?php echo stripcslashes( $gglmps_marker[1] ); ?></div>
														<xmp class="gglmps_marker_tooltip"><?php echo html_entity_decode( stripcslashes( $gglmps_marker[2] ) ); ?></xmp>
														<input class="gglmps_input_latlng" name="gglmps_list_marker_latlng[]" type="hidden" value="<?php echo $gglmps_marker[0]; ?>" />
														<textarea class="gglmps_textarea_location" name="gglmps_list_marker_location[]"><?php echo stripcslashes( $gglmps_marker[1] ); ?></textarea>
														<textarea class="gglmps_textarea_tooltip" name="gglmps_list_marker_tooltip[]"><?php echo stripcslashes( $gglmps_marker[2] ); ?></textarea>
													</div>
												</li>
											<?php }
										} ?>
									</ul>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_width"><?php _e( 'Width (px)', 'gglmps' ); ?></label></th>
								<td><input id="gglmps_basic_width" name="gglmps_basic_width" type="number" min="150" max="10000" value="<?php echo $gglmps_map_data['basic']['width']; ?>" placeholder="<?php _e( 'Enter width', 'gglmps' ); ?>"></td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_height"><?php _e( 'Height (px)', 'gglmps' ); ?></label></th>
								<td>
									<input id="gglmps_basic_height" name="gglmps_basic_height" type="number" min="150" max="10000" value="<?php echo $gglmps_map_data['basic']['height']; ?>" placeholder="<?php _e( 'Enter height', 'gglmps' ); ?>">
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_alignment"><?php _e( 'Alignment', 'gglmps' ); ?></label></th>
								<td>
									<select id="gglmps_basic_alignment" name="gglmps_basic_alignment">
										<option value="left" <?php if ( $gglmps_map_data['basic']['alignment'] == 'left' ) echo 'selected'; ?>><?php _e( 'Left', 'gglmps' ); ?></option>
										<option value="center" <?php if ( $gglmps_map_data['basic']['alignment'] == 'center' ) echo 'selected'; ?>><?php _e( 'Center', 'gglmps' ); ?></option>
										<option value="right" <?php if ( $gglmps_map_data['basic']['alignment'] == 'right' ) echo 'selected'; ?>><?php _e( 'Right', 'gglmps' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_map_type"><?php _e( 'Type', 'gglmps' ); ?></label></th>
								<td>
									<select id="gglmps_basic_map_type" name="gglmps_basic_map_type">
										<option value="roadmap" <?php if ( $gglmps_map_data['basic']['map_type'] == 'roadmap' ) echo 'selected'; ?>><?php _e( 'Roadmap', 'gglmps' ); ?></option>
										<option value="terrain" <?php if ( $gglmps_map_data['basic']['map_type'] == 'terrain' ) echo 'selected'; ?>><?php _e( 'Terrain', 'gglmps' ); ?></option>
										<option value="satellite" <?php if ( $gglmps_map_data['basic']['map_type'] == 'satellite' ) echo 'selected'; ?>><?php _e( 'Satellite', 'gglmps' ); ?></option>
										<option value="hybrid" <?php if ( $gglmps_map_data['basic']['map_type'] == 'hybrid' ) echo 'selected'; ?>><?php _e( 'Hybrid', 'gglmps' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_tilt45"><?php _e( 'View', 'gglmps' ); ?>&nbsp;45&deg;</label></th>
								<td>
									<input id="gglmps_basic_tilt45" name="gglmps_basic_tilt45" type="checkbox" <?php if ( $gglmps_map_data['basic']['tilt45'] == 1 ) echo 'checked="checked"'; ?> />
									<span class="gglmps_editor_tooltip"><?php _e( 'This option is only available for the types of map Satellite and Hybrid (if such snapshots are available).', 'gglmps' ); ?></span>
								</td>
							</tr>
							<tr valign="middle">
								<th><label for="gglmps_basic_auto_zoom"><?php _e( 'Zoom', 'gglmps' ); ?></label></th>
								<td>
									<div id="gglmps_zoom_wrap">
										<div id="gglmps_zoom_slider"></div>
										<span id="gglmps_zoom_value"></span>
									</div>
									<input id="gglmps_basic_zoom" name="gglmps_basic_zoom" type="number" min='0' max='21' value="<?php echo $gglmps_map_data['basic']['zoom']; ?>">
								</td>
							</tr>
							<tr valign="middle">
								<th>
									<input id="gglmps_editor_additional_options" name="gglmps_editor_additional_options" type="checkbox" <?php if ( $gglmps_map_data['additional_options'] == 1 ) echo 'checked="checked"'; ?> />
									<label for="gglmps_editor_additional_options"><?php _e( 'Controls options', 'gglmps' ); ?></label>
								</th>
								<td>
									<span class="gglmps_editor_tooltip"><?php _e( 'Visibility and actions controls of the map.', 'gglmps' ); ?></span>
								</td>
							</tr>
							<tr class="gglmps_editor_additional_options" valign="middle">
								<th>&nbsp;</th>
								<td>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_map_type" name="gglmps_control_map_type" type="checkbox" <?php if ( $gglmps_map_data['controls']['map_type'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_map_type"><?php _e( 'Type', 'gglmps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_pan" name="gglmps_control_pan" type="checkbox" <?php if ( $gglmps_map_data['controls']['pan'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_pan"><?php _e( 'Pan', 'gglmps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_rotate" name="gglmps_control_rotate" type="checkbox" <?php if ( $gglmps_map_data['controls']['rotate'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_rotate"><?php _e( 'Rotate', 'gglmps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_rotate" name="gglmps_control_rotate" type="checkbox" <?php if ( $gglmps_map_data['controls']['rotate'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_rotate"><?php _e( 'Rotate', 'gglmps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_zoom" name="gglmps_control_zoom" type="checkbox" <?php if ( $gglmps_map_data['controls']['zoom'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_zoom"><?php _e( 'Zoom', 'gglmps' ); ?></label>
									</p>
									<p class="gglmps_editor_additional_option">
										<input id="gglmps_control_scale" name="gglmps_control_scale" type="checkbox" <?php if ( $gglmps_map_data['controls']['scale'] == 1 ) echo 'checked="checked"'; ?> />
										<label for="gglmps_control_scale"><?php _e( 'Scale', 'gglmps' ); ?></label>
									</p>
								</td>
							</tr>
						</tbody>
					</table> <!-- .gglmps_editor_table -->
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">
							<div class="bws_table_bg"></div>
							<table class="form-table bws_pro_version">
								<tr valign="middle">
									<th>
										<label><?php _e( 'Zoom', 'gglmps' ); ?></label>
									</th>
									<td>
										<p class="gglmps-zoom-container">
											<input disabled="disabled" name="gglmpspr_basic_auto_zoom" type="checkbox" />
											<label><?php _e( 'Auto', 'gglmps' ); ?></label>
											<span class="gglmps_settings_tooltip"><?php _e( 'The map will be scaled to display all markers.', 'gglmps' ); ?></span>
										</p>
									</td>
								</tr>
								<tr valign="middle">
									<th><?php _e( 'Controls options', 'gglmps' ); ?></th>
									<td>
										<p class="gglmps_settings_additional_option">
										<input disabled="disabled" name="gglmpspr_control_street_view" type="checkbox" />
											<label><?php _e( 'Street View', 'gglmps' ); ?></label>
										</p>
										<p class="gglmpspr_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_overview_map" type="checkbox" />
											<label><?php _e( 'Overview Map', 'gglmps' ); ?></label>
										</p>
										<p class="gglmpspr_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_overview_map_opened" type="checkbox" />
											<label><?php _e( 'Overview Map Opened', 'gglmps' ); ?></label>
										</p>
										<p class="gglmpspr_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_map_draggable" type="checkbox" />
											<label><?php _e( 'Draggable', 'gglmps' ); ?></label>
										</p>
										<p class="gglmpspr_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_double_click" type="checkbox" />
											<label><?php _e( 'Double Click', 'gglmps' ); ?></label>
										</p>
										<p class="gglmpspr_settings_additional_option">
											<input disabled="disabled" name="gglmpspr_control_scroll_wheel" type="checkbox" />
											<label><?php _e( 'Scroll Wheel', 'gglmps' ); ?></label>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" colspan="2">
										* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'gglmps' ); ?>
									</th>
								</tr>
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to PRO version.', 'gglmps' ); ?>
								<a target="_blank" href="http://bestwebsoft.com/products/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'gglmps' ); ?></a>
							</div>
							<a class="bws_button" href="http://bestwebsoft.com/products/bws-google-maps/buy/?k=5ae35807d562bf6b5c67db88fefece60&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Maps Pro">
								<?php _e( 'Go', 'gglmps' ); ?> <strong>PRO</strong>
							</a>
							<div class="clear"></div>
						</div>
					</div>
					<p>
						<input id="gglmps_editor_action" name="gglmps_editor_action" type="hidden" value="<?php echo $gglmps_editor_action; ?>" />
						<input id="gglmps_editor_mapid" name="gglmps_editor_mapid" type="hidden" value="<?php echo $gglmps_editor_mapid; ?>" />
						<?php wp_nonce_field( plugin_basename( __FILE__ ) ); ?>
						<input class="button-primary" id="gglmps_editor_submit" name="gglmps_editor_submit" type="submit" value="<?php echo $gglmps_editor_submit[ $gglmps_editor_action ]; ?>" />
					</p>
				</form><!-- #gglmps_editor_form -->
			</div><!-- #gglmps_editor_settings -->
			<div id="gglmps_editor_preview">
				<div class="bws_pro_version_bloc bws_pro_version_bloc_mini">
					<div class="bws_pro_version_table_bloc">
						<div class="bws_table_bg"></div>
						<table class="form-table bws_pro_version">
							<tr valign="middle">
								<th>
									<img src="<?php echo plugins_url( 'images/map_preview_example.png', __FILE__ ); ?>">
								</th>
							</tr>
						</table>
					</div>
					<div class="bws_pro_version_tooltip">
						<div class="bws_info">
							<?php _e( 'Unlock premium options by upgrading to PRO version.', 'gglmps' ); ?>
							<a target="_blank" href="http://bestwebsoft.com/products/bws-google-maps/?k=f546edd672c2e16f8359dcb48f9d2fff&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( 'Learn More', 'gglmps' ); ?></a>
						</div>
						<a class="bws_button" href="http://bestwebsoft.com/products/bws-google-maps/buy/?k=5ae35807d562bf6b5c67db88fefece60&pn=124&v=<?php echo $gglmps_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Google Maps Pro">
							<?php _e( 'Go', 'gglmps' ); ?> <strong>PRO</strong>
						</a>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div><!-- #gglmps_editor_wrap -->
	<?php }
}

/*
* Function to display table screen options.
*/
if ( ! function_exists ( 'gglmps_screen_options' ) ) {
	function gglmps_screen_options() {
		$args = array(
			'label'   => __( 'Map(s)', 'gglmps' ),
			'default' => 20,
			'option'  => 'gglmps_maps_per_page'
		);
		add_screen_option( 'per_page', $args );
	}
}

/*
* Function to add script and styles to the admin panel.
*/
if ( ! function_exists( 'gglmps_admin_head' ) ) {
	function gglmps_admin_head() {
		global $wp_version, $gglmps_options;
		if ( $wp_version < 3.8 ) {
			wp_enqueue_style( 'gglmps_stylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );
		} else {
			wp_enqueue_style( 'gglmps_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'gglmps_editor' ) {
			$gglmps_api_key = ! empty( $gglmps_options['api_key'] ) ? sprintf( '&key=%s', $gglmps_options['api_key'] ) : '';
			$gglmps_language = sprintf( '&language=%s', $gglmps_options['language'] );
			$gglmps_api = sprintf(
				'https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places%1$s%2$s',
				$gglmps_api_key,
				$gglmps_language
			);
			wp_enqueue_script( 'gglmps_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'gglmps_api', $gglmps_api );
			wp_enqueue_script( 'gglmps_editor_script', plugins_url( 'js/gglmps-editor.js', __FILE__ ), array( 'jquery-ui-slider' ) );
			$gglmps_translation_array = array(
				'deleteMarker'   => __( 'Delete', 'gglmps' ),
				'editMarker'     => __( 'Edit', 'gglmps' ),
				'findMarker'     => __( 'Find', 'gglmps' ),
				'noMarkers'      => __( 'No markers', 'gglmps' ),
				'getCoordinates' => __( 'Get coordinates', 'gglmps' )
			);
			wp_localize_script( 'gglmps_editor_script', 'gglmps_translation', $gglmps_translation_array );
		}
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'bws-google-maps.php' ) {
			wp_enqueue_script( 'gglmps_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'gglmps_settings_script', plugins_url( 'js/gglmps-settings.js', __FILE__ ), array( 'jquery-ui-slider' ) );
		}
	}
}

/*
* Function to set up table screen options.
*/
if ( ! function_exists ( 'gglmps_set_screen_options' ) ) {
	function gglmps_set_screen_options( $status, $option, $value ) {
		if ( $option == 'gglmps_maps_per_page' ) {
			return $value;
		}
		return $status;
	}
}

/*
* Function to add meta tag to the front-end.
*/
if ( ! function_exists( 'gglmps_head' ) ) {
	function gglmps_head() { ?>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<?php }
}

/*
* Function to add script and styles to the front-end.
*/
if ( ! function_exists( 'gglmps_frontend_head' ) ) {
	function gglmps_frontend_head() {
		global $gglmps_options;
		$gglmps_api_key = ! empty( $gglmps_options['api_key'] ) ? sprintf( '&key=%s', $gglmps_options['api_key'] ) : '';
		$gglmps_language = sprintf( '&language=%s', $gglmps_options['language'] );
		$gglmps_api = sprintf(
			'https://maps.googleapis.com/maps/api/js?sensor=false%1$s%2$s',
			$gglmps_api_key,
			$gglmps_language
		);
		wp_enqueue_style( 'gglmps_style', plugins_url( 'css/gglmps.css', __FILE__ ) );
		wp_enqueue_script( 'gglmps_api', $gglmps_api );
		wp_enqueue_script( 'gglmps_script', plugins_url( 'js/gglmps.js', __FILE__ ), array( 'jquery' ) );
	}
}

/*
* Function to display Google Maps.
*/
if ( ! function_exists( 'gglmps_shortcode' ) ) {
	function gglmps_shortcode( $atts ) {
		global $gglmps_maps, $gglmps_count;
		if ( empty( $gglmps_count ) ) {
			$gglmps_count = 1;
		}
		if ( $gglmps_count > 1 ) {
			return;
		}
		if ( ! isset( $atts['id'] ) ) {
			$gglmps_count++;
			return sprintf(
				'<div class="gglmps_map_error">[Google Maps: %s]</div>',
				__( 'You have not specified map ID', 'gglmps' )
			);
		}
		if ( isset( $gglmps_maps[ $atts['id'] ] ) ) {
			$gglmps_mapid = uniqid('gglmps_map_');
			$gglmps_map_data = $gglmps_maps[ $atts['id'] ]['data'];
			$gglmps_map_width = $gglmps_map_data['basic']['width'];
			$gglmps_map_height = $gglmps_map_data['basic']['height'];
			$gglmps_map_markers = '{}';
			switch ( $gglmps_map_data['basic']['alignment'] ) {
				case 'right':
					$gglmps_map_alignment = 'float: right;';
					break;
				case 'center':
					$gglmps_map_alignment = 'margin: 0 auto;';
					break;
				case 'left':
				default:
					$gglmps_map_alignment = 'float: left;';
					break;
			}
			if ( count( $gglmps_map_data['markers'] ) ) {
				$gglmps_map_markers = '{';
				foreach ( $gglmps_map_data['markers'] as $key => $gglmps_marker ) {
					$gglmps_map_markers .= sprintf(
						"%d:{'latlng':'%s','location':'%s','tooltip':'%s'}",
						$key,
						$gglmps_marker[0],
						$gglmps_marker[1],
						preg_replace( "|<script.*?>.*?</script>|", "", html_entity_decode( $gglmps_marker[2] ) )
					);
					$gglmps_map_markers .= count( $gglmps_map_data['markers'] ) - 1 != $key ? ',' : '';
				}
				$gglmps_map_markers .= '}';
			}
			$gglmps_count++;
			return sprintf(
				'<div class="gglmps_container_map">
					<div id="%1$s" class="gglmps_map" style="%2$s width: %3$dpx; height: %4$dpx;">
						<noscript>
							<p class="gglmps_no_script">
								[Google Maps: %5$s <a href="http://www.google.ru/support/bin/answer.py?answer=23852" target="_blank">%6$s</a>]
							</p>
						</noscript>
					</div>
				</div>
				<script>
					var gglmps_map_data = {
						"container"   : "%7$s",
						"basic"       : %8$s,
						"controls"    : %9$s,
						"markers"     : %10$s
					};
				</script>',
				 $gglmps_mapid,
				 $gglmps_map_alignment,
				 $gglmps_map_width,
				 $gglmps_map_height,
				 __( 'Please, enable JavaScript!', 'gglmps' ),
				 __( 'HELP', 'gglmps' ),
				 $gglmps_mapid,
				 json_encode( $gglmps_map_data['basic'] ),
				 json_encode( $gglmps_map_data['controls'] ),
				 $gglmps_map_markers
			);
		} else {
			$gglmps_count++;
			return sprintf(
				'<div class="gglmps_map_error">[Google Maps: %1$s ID#%2$d %3$s]</div>',
				__( 'Map with', 'gglmps' ),
				$atts['id'],
				__( 'not found', 'gglmps' )
			);
		}
	}
}

/*
* Function to add action links to the plugin menu.
*/
if ( ! function_exists ( 'gglmps_plugin_action_links' ) ) {
	function gglmps_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=bws-google-maps.php">' . __( 'Settings', 'gglmps' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/*
* Function to add links to the plugin description on the plugins page.
*/
if ( ! function_exists ( 'gglmps_register_action_links' ) ) {
	function gglmps_register_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) ) {
			if ( ! is_network_admin() )
				$links[] = sprintf( '<a href="admin.php?page=bws-google-maps.php">%s</a>', __( 'Settings', 'gglmps' ) );
			$links[] = sprintf( '<a href="http://wordpress.org/plugins/bws-google-maps/faq/" target="_blank">%s</a>', __( 'FAQ', 'gglmps' ) );
			$links[] = sprintf( '<a href="http://support.bestwebsoft.com">%s</a>', __( 'Support', 'gglmps' ) );
		}
		return $links;
	}
}

if ( ! function_exists ( 'gglmps_plugin_banner' ) ) {
	function gglmps_plugin_banner() {
		global $hook_suffix;
		if ( 'plugins.php' == $hook_suffix ) {
			global $gglmps_plugin_info;
			bws_plugin_banner( $gglmps_plugin_info, 'gglmps', 'bws-google-maps', 'f546edd672c2e16f8359dcb48f9d2fff', '124', '//ps.w.org/bws-google-maps/assets/icon-128x128.png' );
		}
	}
}

/*
* Function to uninstall Google Maps.
*/
if ( ! function_exists( 'gglmps_uninstall' ) ) {
	function gglmps_uninstall() {
		delete_option( 'gglmps_options' );
		delete_option( 'gglmps_maps' );
	}
}

/* Displaying admin menu */
add_action( 'admin_menu', 'gglmps_admin_menu' );
/* Initialization */
add_action( 'init', 'gglmps_init' );
add_action( 'admin_init', 'gglmps_admin_init' );
/* Adding scripts and styles in the admin panel */
add_action( 'admin_enqueue_scripts', 'gglmps_admin_head' );
/* Adding support for pagination in the maps manager */
add_filter( 'set-screen-option', 'gglmps_set_screen_options', 10, 3 );
/* Adding meta tag, scripts and styles on the frontend */
add_action( 'wp_head', 'gglmps_head' );
add_action( 'wp_enqueue_scripts', 'gglmps_frontend_head' );
/* Adding a plugin support shortcode */
add_shortcode( 'bws_googlemaps', 'gglmps_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );
/* Adding additional links on the plugins page */
add_filter( 'plugin_action_links', 'gglmps_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'gglmps_register_action_links', 10, 2 );

add_action( 'admin_notices', 'gglmps_plugin_banner' );
/* Uninstall plugin */
register_uninstall_hook( __FILE__, 'gglmps_uninstall' );
?>