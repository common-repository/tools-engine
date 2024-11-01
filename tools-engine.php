<?php
/**
 * Tools Engine.
 *
 * Tools Engine plugin file.
 *
 * @package   Smackcoders\TOOLSENGINE
 * @copyright Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name:  Tools Engine 
 * Version:    	 1.1
 * Description:  Tools engine provides an easy to use interface for registering and managing custom fields for your website.
 * Author:       Smackcoders
 * Author URI:   https://www.smackcoders.com/wordpress.html
 * Text Domain:  tools-engine
 * Domain Path:  /languages
 * License:      GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */


namespace Smackcoders\TOOLSENGINE;
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'TOOLS_ENGINE', '1.1' );

include_once(ABSPATH.'wp-admin/includes/plugin.php');

/**
 * When plugin loads 
 */

//Gutenberg Blocks
function SelectBoxValues(){
	global $wpdb;
	$response = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE (post_type = 'tools-engine' OR post_type = 'smack-field') AND post_title <> 'Auto Draft'");

	//$response = json_decode(wp_json_encode($response),true);
	 foreach($response as $res){
		 $res->post_content = unserialize($res->post_content);
	 }
	// $response = 'Hello there!';
	return rest_ensure_response($response);
}


add_action('rest_api_init', function() {
	register_rest_route('tools-engine/fields', '/mydata', [
		'method' => 'GET',
		'callback' => 'Smackcoders\\TOOLSENGINE\\SelectBoxValues',
		'permission_callback' => '__return_true'
	]);
});


function ToolsEngineBlock(){

	$block_url = plugins_url('app/blocks/',__FILE__);

	wp_enqueue_script('tools-engine-block',$block_url.'tools-engine-block.js',array('wp-blocks','wp-components','wp-element','wp-i18n','wp-editor'),true);

}

function load_wp_media_files() {
	wp_enqueue_media();
}

add_action( 'admin_enqueue_scripts', 'Smackcoders\\TOOLSENGINE\\load_wp_media_files' );

add_action('enqueue_block_editor_assets','Smackcoders\\TOOLSENGINE\\ToolsEngineBlock');

add_action( 'plugins_loaded', 'Smackcoders\\TOOLSENGINE\\pluginInit' );

require_once(__DIR__.'/admin-functions.php');
require_once(__DIR__.'/plugin.php');

require_once(__DIR__.'/smack-theme-functions.php');
require_once(__DIR__.'/smack-theme-code.php');

add_filter( 'page_row_actions', 'Smackcoders\\TOOLSENGINE\\remove_row_actions', 10, 2 );
add_action('wp_ajax_smack_duplicate','Smackcoders\\TOOLSENGINE\\smack_duplicate');

add_shortcode("toolsengine", 'Smackcoders\\TOOLSENGINE\\smack_field_shortcode_function');

function smack_field_shortcode_function($atts){
	
	extract( shortcode_atts( array(
		'field'			=> '',
		'post_id'		=> false,
		'format_value'	=> true
	), $atts ) );

	global $post;
	global $wpdb;

	$theme_obj = TEFunctions::getInstance();
	$result = $theme_obj->returnFieldValues($post->ID, $field, 'string', 'basic');
	
	return $result;
}

function smack_duplicate(){
	check_ajax_referer('smack-tools-engine-key', 'securekey');
	global $wpdb;
	$post_id = intval($_POST['postId']);
	$get_parent_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE ID = $post_id AND post_type = 'tools-engine' ", ARRAY_A);

	$save = array(
		'post_status'	=> $get_parent_data[0]['post_status'],
		'post_type'		=> 'tools-engine',
		'post_title'	=> $get_parent_data[0]['post_title'] . ' (copy)',
		'post_name'		=> $get_parent_data[0]['post_name'] . '-copy',
		'post_excerpt'	=> $get_parent_data[0]['post_excerpt'] . '-copy',
		'post_content'	=> $get_parent_data[0]['post_content'],
		'menu_order'	=> $get_parent_data[0]['menu_order'],
		'comment_status' => 'closed',
		'ping_status'	=> 'closed',
	);

	$save = wp_slash( $save );
	$new_id = wp_insert_post( $save );

	$get_child_fields_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $post_id ", ARRAY_A);
	foreach($get_child_fields_data as $child_fields_data){

		$field_save = array(
			'post_status'	=> 'publish',
			'post_type'		=> 'smack-field',
			'post_title'	=> $child_fields_data['post_title'],
			'post_name'		=> $child_fields_data['post_name'],
			'post_excerpt'	=> $child_fields_data['post_excerpt'],
			'post_content'	=> $child_fields_data['post_content'],
			'post_parent'	=> $new_id,
			'menu_order'	=> $child_fields_data['menu_order'],
		);
		$field_save = wp_slash( $field_save );
		$new_field_id = wp_insert_post( $field_save );

	}

	$response['success'] = true;
	echo wp_json_encode($response);
	wp_die();

}

function remove_row_actions( $actions, $post )
{
	
    if( get_post_type() === 'tools-engine' ){
        unset( $actions['Quick Edit'] );
		unset( $actions['inline hide-if-no-js'] );

		//$get_edit_data = $actions['edit'];
		$get_post_id = $post->ID;
		$actions['Duplicate'] = '<a href="#" title="" onclick="theFunction('.$get_post_id.');" rel="permalink">Duplicate</a>';
		
		?>
			<script>
				
				function theFunction(postIds){
					jQuery(document).ready(function($){
	
						$.ajax({
							type: "POST",
							url: ajaxurl,

							data: {"action":"smack_duplicate",
								"postId": postIds,
								"securekey": smack_tools_engine_object.nonce
							},
							
							success: function(data){
								location.reload(true);
							}
						});
					});	
				}				
			</script>

		<?php

		return $actions;
    }
}


$ctrlExtensions = glob(__DIR__ . '/controller/*.php');

foreach ($ctrlExtensions as $ctrlExtensionVal) {
	require_once $ctrlExtensionVal;
}

$ctrlGroupExtensions = glob(__DIR__ . '/controller/groups/*.php');

foreach ($ctrlGroupExtensions as $ctrlGroupExtensionVal) {
	require_once $ctrlGroupExtensionVal;
}

$ctrlViewExtensions = glob(__DIR__ . '/controller/views/*.php');

foreach ($ctrlViewExtensions as $ctrlViewExtensionVal) {
	require_once $ctrlViewExtensionVal;
}

require_once(__DIR__ . '/controller/fields/class-smack-helper.php');

$basicFieldsViewExtensions = glob(__DIR__ . '/controller/fields/basicFields/*.php');
foreach ($basicFieldsViewExtensions as $basicFielViewExtensionVal) {
	require_once $basicFielViewExtensionVal;
}

$optionsFieldsViewExtensions = glob(__DIR__ . '/controller/fields/optionsFields/*.php');
foreach ($optionsFieldsViewExtensions as $choiceFielViewExtensionVal) {
	require_once $choiceFielViewExtensionVal;
}

$advancedFieldsViewExtensions = glob(__DIR__ . '/controller/fields/advancedFields/*.php');
foreach ($advancedFieldsViewExtensions as $contentFielViewExtensionVal) {
	require_once $contentFielViewExtensionVal;
}

$layoutFieldsViewExtensions = glob(__DIR__ . '/controller/fields/layoutFields/*.php');
foreach ($layoutFieldsViewExtensions as $layoutFielViewExtensionVal) {
	require_once $layoutFielViewExtensionVal;
}

$relationshipFieldsViewExtensions = glob(__DIR__ . '/controller/fields/relationshipFields/*.php');
foreach ($relationshipFieldsViewExtensions as $relationalFielViewExtensionVal) {
	require_once $relationalFielViewExtensionVal;
}

/**
 * Plugin Init Function 
 */
function pluginInit() {
	Plugin::getInstance();
	Admin::getInstance();
	FieldType::getInstance();
	LocationRule::getInstance();
	FieldGroup::getInstance();
	UltimateHelper::getInstance();
	UltimateFields::getInstance();
	PostView::getInstance();
	SmackThemeCode::getInstance();
}

register_activation_hook( __FILE__, array('Smackcoders\\TOOLSENGINE\\Plugin','activate' ));
register_deactivation_hook( __FILE__, array('Smackcoders\\TOOLSENGINE\\Plugin','deactivate' ));