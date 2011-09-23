<?php
/*
Plugin Name: Shashin permalinks
Plugin URI: http://kerlinux.org/info/soft/shashin-permalinks
Description: This plugin add permalinks support for Shashin plugin galleries (default keywords: "album" and "page")
Author: Sébastien "SLiX" Liénard
Author URI: http://kerlinux.org
Version: 1.1
License: GPLv3
*/

/**
 * Copyright 2011 Sébastien "SLiX" Liénard <my_nickname_à_kerlinux.org>
 *
 * Shashin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Shashin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

load_plugin_textdomain( 'shashin-permalinks', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

if (get_option('shashin_permalinks-on')) {
	// If enabled, add filters and actions
	add_filter( 'the_content',			'shashin_permalinks_replace' );
	add_filter( 'the_excerpt_rss',		'shashin_permalinks_replace');
	add_filter( 'rewrite_rules_array',	'shashin_permalinks_rewrite_rules' );
	add_filter( 'query_vars',			'shashin_permalinks_query_vars' );
	add_action( 'wp_loaded',			'shashin_permalinks_wp_loaded' );
	add_action( 'pre_get_posts',		'shashin_permalinks_pre_get_posts' );

	// Set global variables
	$key_album = get_option('shashin_permalinks-key_album');
	$key_page = get_option('shashin_permalinks-key_page');
}

add_action('admin_menu', 'shashin_permalinks_menu');
register_activation_hook(__FILE__, 'shashin_permalinks_install');
register_deactivation_hook(__FILE__, 'shashin_permalinks_uninstall');

function shashin_permalinks_replace ($content)
{
	if ( get_option('permalink_structure') != '' && ! is_preview() ) {

		global $key_album, $key_page;

		$content = preg_replace('/(<a\s+href=[\'"][^>]+)(\?|&amp;)shashin_album_key=(\d+)\/*/i', '$1' . $key_album . '/$3/', $content);

		if (isset($key_page) && $key_page != '') {
			$content = preg_replace('/(<a\s+href=[\'"][^>]+)(\?|&amp;)shashin_page=(\d+)\/*/i', '$1' . $key_page . '/$3/', $content);
		} else {
			$content = preg_replace('/(<a\s+href=[\'"][^>]+)(\?|&amp;)shashin_page=(\d+)\/*/i', '$1' . '$3/', $content);
		}
	}
	return $content;
}

function shashin_permalinks_pre_get_posts() {
	// Shashin plugin gets its parameters from $_REQUEST, so we have to set them (!)
	$_REQUEST['shashin_album_key'] = get_query_var('shashin_album_key');
	$_REQUEST['shashin_page'] = get_query_var('shashin_page');
}

// flush_rules() if our rules are not yet included
function shashin_permalinks_wp_loaded(){
	global $key_album, $key_page;
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules["^(.*)/$key_album/(.+?)/*$"] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Adding a new rule
function shashin_permalinks_rewrite_rules( $rules )
{
	global $key_album, $key_page;

	$newrules = array();

	// Rule with pages
	if (isset($key_page) && $key_page != '') {
		$newrules["^(.*)/$key_album/(.+?)/$key_page/(\d+)/*$"] = 'index.php?pagename=$matches[1]&shashin_album_key=$matches[2]&shashin_page=$matches[3]';
	} else {
		$newrules["^(.*)/$key_album/(.+?)/(\d+)/*$"] = 'index.php?pagename=$matches[1]&shashin_album_key=$matches[2]&shashin_page=$matches[3]';
	}
	// Rule without pages
	$newrules["^(.*)/$key_album/(\d+)/*$"] = 'index.php?pagename=$matches[1]&shashin_album_key=$matches[2]';
	return $newrules + $rules;
}

// Add the 'shashin_album_key' var so that WP recognizes it, used in shashin_permalinks_pre_get_posts()
function shashin_permalinks_query_vars( $vars )
{
    array_push($vars, 'shashin_album_key');
    array_push($vars, 'shashin_page');
    return $vars;
}

function shashin_permalinks_install() {
	add_option('shashin_permalinks-on', 0);
	add_option('shashin_permalinks-key_album', 'album');
	add_option('shashin_permalinks-key_page', 'page');
}

function shashin_permalinks_uninstall() {
	delete_option('shashin_permalinks-on');
	delete_option('shashin_permalinks-key_album');
	delete_option('shashin_permalinks-key_page');
}

function shashin_permalinks_menu() {
    add_options_page(__('Shashin permalinks options','shashin-permalinks'), __('Shashin permalinks','shashin-permalinks'), 1, 'shashin-permalinks', 'shashin_permalinks_options');
}

function shashin_permalinks_options() {

	if ( isset($_POST['submit']) || isset($_POST['reset'])) {
        if (!current_user_can('manage_options')) die(__('You cannot edit the Shashin permalinks options.', 'shashin-permalinks'));
        check_admin_referer('shashin_permalinks-config');
	}

	if ( isset($_POST['submit']) ) {

		$error = false;
		$post_key_album = $_POST['shashin_permalinks-key_album'];
		$post_key_page = $_POST['shashin_permalinks-key_page'];

		if ($post_key_album == '' || preg_match('/[\/#=]/', $post_key_album)) {
			echo '<div id="message" class="error"><p>' . __('Value for <strong>Album</strong> parameter is invalid', 'shashin-permalinks') . '</p></div>';
			$error = true;
		}

		if (preg_match('/[\/#=]/', $post_key_page)) {
			echo '<div id="message" class="error"><p>' . __('Value for <strong>Page</strong> parameter is invalid', 'shashin-permalinks') . '</p></div>';
			$error = true;
		}

		if ($error) {
			echo '<div id="message" class="error"><p><strong>' . __('Shashin permalinks settings were not updated', 'shashin-permalinks') . '<strong></p></div>';
		} else {
			update_option('shashin_permalinks-on', $_POST['shashin_permalinks-on']);
			update_option('shashin_permalinks-key_album', $post_key_album);
			update_option('shashin_permalinks-key_page', $post_key_page);
			echo '<div id="message" class="updated fade"><p>' . __('Shashin permalinks settings saved', 'shashin-permalinks') . '</p></div>';
		}
	}

	if ( isset($_POST['reset']) ) {
		// Don't change status on reset
		//update_option('shashin_permalinks-on', 0);
		update_option('shashin_permalinks-key_album', 'album');
		update_option('shashin_permalinks-key_page', 'page');

		echo '<div id="message" class="updated fade"><p>' . __('Shashin permalinks settings resetted to defaults.', 'shashin-permalinks') . '</p></div>';
	}

	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>'.__('Shashin permalinks options', 'shashin-permalinks').'</h2>';
	echo '<form name="form1" method="post">';
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('shashin_permalinks-config');
	echo '<table class="form-table">';
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="shashin_permalinks-on">'.__('Enable Shashin permalinks', 'shashin-permalinks').':</label></th>';
	echo '<td><input name="shashin_permalinks-on" type="checkbox" id="shashin_permalinks-on"'; echo get_option('shashin_permalinks-on')?'checked':''; echo ' value="1" /></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="shashin_permalinks-key_album">'.__('Keyword for album parameter', 'shashin-permalinks').':</label></th>';
	echo '<td><input name="shashin_permalinks-key_album" type="text" id="shashin_permalinks-key_album" value="'; echo stripslashes(get_option('shashin_permalinks-key_album')); echo '" class="regular-text" /></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row"><label for="shashin_permalinks-key_page">'.__('Keyword for page parameter', 'shashin-permalinks').':</label></th>';
	echo '<td><input name="shashin_permalinks-key_page" type="text" id="shashin_permalinks-key_page" value="'; echo stripslashes(get_option('shashin_permalinks-key_page')); echo '" class="regular-text" /><br /><span class="description">'; _e('Can be empty, in this case URIs will be "/album/ID/PAGE/"','shashin-permalinks'); echo '</span></td>';
	echo '</tr>';
	echo '</table>';
	echo '<p class="submit">';
	echo '<input type="submit" name="submit" class="button-primary" value="'; _e('Save settings', 'shashin-permalinks'); echo '" />';
	echo '<input type="submit" name="reset" onclick=\'return confirm("'; _e('Do you really want to reset your configuration ?','shashin-permalinks'); echo '");\' value="';_e('Reset settings', 'shashin-permalinks'); echo '" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}
?>
