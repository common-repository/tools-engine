<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class Admin
 * @package Smackcoders\TOOLSENGINE
 */
class Admin
{
	protected static $instance = null,$plugin;
	public $plugisnScreenHookSuffix=null;
	/**
	 * Admin constructor.
	 */
	public function __construct()
	{

	}

	// Custom Script tag for the purpose of Working with Visual Mode 
	private static function set_script( $scripts, $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
		$script = $scripts->query( $handle, 'registered' );

		if ( $script ) {
			// If already added
			$script->src  = $src;
			$script->deps = $deps;
			$script->ver  = $ver;
			$script->args = $in_footer;

			unset( $script->extra['group'] );

			if ( $in_footer ) {
				$script->add_data( 'group', 1 );
			}
		} else {
			// Add the script
			if ( $in_footer ) {
				$scripts->add( $handle, $src, $deps, $ver, 1 );
			} else {
				$scripts->add( $handle, $src, $deps, $ver );
			}
		}
	}
	public static function replace_scripts( $scripts ) {
		
		$assets_url = plugins_url( 'assets/js/', __FILE__ );

		// Set 'jquery-core' to 1.12.4-wp.
		self::set_script( $scripts, 'jquery-core', $assets_url . 'jquery-1.12.4-wp.min.js', array(), '1.12.4-wp' );

		$deps = array( 'jquery-core' );

		self::set_script( $scripts, 'jquery', false, $deps, '1.12.4-wp' );
		
	}
	//End of Script Changing Visual Mode for a Editor

	/**
	 * Admin Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	/**
	 * Admin Hooks
	 */
	public function doHooks(){
		add_action('init',	array(self::$instance, 'register_post_types'), 5);
		add_action( 'init', array(self::$instance, 'register_post_status'), 5 );
		add_action('admin_menu', array($this,'addpluginAdminmenu'));
		add_action('current_screen',array(self::$instance, 'current_screen'));
		add_action('admin_enqueue_scripts',array($this,'enqueueAdminScripts'));
		add_action( 'wp_default_scripts', array( __CLASS__, 'replace_scripts' ), -1 );
		if ( ! class_exists( '_WP_Editors', false ) ) {
			require( ABSPATH . WPINC . '/class-wp-editor.php' );
		}
		add_action( 'admin_print_footer_scripts', array( '_WP_Editors', 'print_default_editor_scripts' ) );
		// add_filter('upload_mimes', function( $types ) {
		// 	$types = get_option('wp_smack_restricted_file_types');
		// 	return $types;
		// });
		add_filter('manage_edit-tools-engine_columns',			array(self::$instance, 'smack_group_columns'), 10, 1);
		add_action('manage_tools-engine_posts_custom_column',	array(self::$instance, 'smack_group_columns_html'), 10, 2);		
		// add_action( 'save_post',    array( $this, 'saveCustomFields' ));
	}

	public function saveCustomFields( $postID )
	{
		$keys = array_keys($_POST);
		for($i =0; $i < sizeof($keys); $i++) {
			if(strrchr($keys[$i], 'wp-smack') === $keys[$i]) {				
			}
		
		}

    // // ...
	// 	if( filter_var( $_POST[ self::PREFIX . 'zIndex'], FILTER_VALIDATE_INT ) === FALSE )
	// 	{
	// 		update_post_meta( $post->ID, self::PREFIX . 'zIndex', 0 );
	// 		$this->enqueueMessage( 'The stacking order has to be an integer.', 'error' );
	// 	}   
	// 	else
	// 		update_post_meta( $post->ID, self::PREFIX . 'zIndex', $_POST[ self::PREFIX . 'zIndex'] );

	// 	// ...
	}
	

	/**
	 * Register new post types
	 */
	public static function register_post_types() {

		register_post_type('tools-engine', array(
			'labels'			=> array(
				'name'					=> 'Field Groups',
				'singular_name'			=> 'Field Group',
				'add_new'				=> 'Add New',
				'add_new_item'			=> 'Add New Field Group',
				'edit_item'				=> 'Edit Field Group',
				'new_item'				=> 'New Field Group' ,
				'view_item'				=> 'View Field Group', 
				'search_items'			=> 'Search Field Groups', 
				'not_found'				=> 'No Field Groups found',
				'not_found_in_trash'	=> 'No Field Groups found in Trash', 
			),
			'public'			=> false,
			'show_ui'			=> true,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> 'administrator',
				'delete_post'		=> 'administrator',
				'edit_posts'		=> 'administrator',
				'delete_posts'		=> 'administrator',
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));

		register_post_type('smack-field', array(
			'labels'			=> array(
				'name'					=>  'Fields', 
				'singular_name'			=>  'Field', 
				'add_new'				=>  'Add New' , 
				'add_new_item'			=>  'Add New Field' , 
				'edit_item'				=>  'Edit Field' , 
				'new_item'				=>  'New Field' ,
				'view_item'				=>  'View Field',
				'search_items'			=>  'Search Fields', 
				'not_found'				=>  'No Fields found',
				'not_found_in_trash'	=>  'No Fields found in Trash',
			),
			'public'			=> false,
			'show_ui'			=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> 'administrator',
				'delete_post'		=> 'administrator',
				'edit_posts'		=> 'administrator',
				'delete_posts'		=> 'administrator',
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));

	}

	public static function register_post_status() {
	
		// Register the Disabled post status.
		register_post_status('smack-disabled', array(
			'label'                     => 'Inactive',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'smack' ),
		));
	}

	/**
	 * Plugin Menus 
	 */
	public function addpluginAdminmenu(){
		$this->pluginScreenHookSuffix=add_menu_page(
			'Tools Engine',
			'Tools Engine', 
			'manage_options', 
			'edit.php?post_type=tools-engine',
			'',
			plugins_url('assets/images/tools-engine-logo.png', __FILE__)
			);
		add_submenu_page('edit.php?post_type=tools-engine', 'Field Groups','Field Groups', "administrator", 'edit.php?post_type=tools-engine' );
		add_submenu_page('edit.php?post_type=tools-engine', 'Add New','Add New', "administrator", 'post-new.php?post_type=tools-engine' );
		add_submenu_page('edit.php?post_type=tools-engine', 'Tools','Tools',"administrator",'tools',array(self::$instance,'importExport'));
	}

	public function importExport(){
		wp_register_script(self::$plugin->getPluginSlug() . 'smack_react_script', plugins_url('assets/js/tools-engine.js', __FILE__), array('jquery'));
        wp_enqueue_script(self::$plugin->getPluginSlug() . 'smack_react_script');
		wp_enqueue_style(self::$plugin->getPluginSlug() . 'ultimate-tools-css', plugins_url('assets/css/ultimate_tools.css', __FILE__));
		?>
		<div id="tools" class="tools"></div>
		<?php
	}

	public function display_plugin_admin_page(){
		wp_redirect(admin_url(). 'edit.php?post_type=tools-engine');
	}

	public function current_screen() {
		self::$instance->admin_head();
		global $wp_post_statuses;
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'smack-custom-fields' );
	}

	public function smack_group_columns( $columns ) {
		return array(
			'cb'	 				=> '<input type="checkbox" />',
			'title' 				=> __('Title', 'smack'),
			'smack-fg-count' 		=> __('Fields', 'smack'),
			'date'					=> __('Date', 'smack'),
		);	
	}

	public function smack_group_columns_html( $column, $post_id ) {
		if( $column == 'smack-fg-count' ) {	
			global $wpdb;
			$count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}posts WHERE post_parent = $post_id AND post_type = 'smack-field' ");
			echo esc_html( $count );
        }
	}

	/**
	 * Added meta box in field groups
	 */
	public static function admin_head() {
		add_meta_box('tools-engine-options', "Tools Engine", array(self::$instance, 'rootDiv'), 'tools-engine', 'normal', 'high');
		remove_meta_box( 'metabox_id', 'post_type', 'default_position' );
	}

	
	function hide_publish_metabox() {
		$post_types = get_post_types( '', 'names' );
	
		if( ! empty( $post_types ) ) {
			foreach( $post_types as $type ) {
				remove_meta_box( 'submitdiv', $type, 'side' );
			}
		}
	}
	
	public function rootDiv() {
		?>
		<div id="root" class="root">
		<div id="wp-ultimate-field-display"></div>
		<div id="wp-ultimate-location-display"></div>
		<div id="wp-ultimate-setting-display"></div>
		</div>
		<?php
	} 

	/**
	 * Admin Scripts
	 */
	public function enqueueAdminScripts(){
		wp_enqueue_editor();
		if(!isset($this->pluginScreenHookSuffix)){
			return;
		}
		$screen = get_current_screen();
	
		$acceptable_screens = array('tools-engine', 'edit-tools-engine', 
									'post', 'page', 'user', 'user-edit', 'profile', 'edit-category', 'edit-post_tag', 'product'	
								);
		
		$groupInst = FieldGroup::getInstance();
		$fieldGroups = $groupInst->getFieldGroupsView(array());
		
		$posttype = [];
		if(!empty($fieldGroups)){
			foreach($fieldGroups as $fieldGroup){ 

				if($fieldGroup['type_rule'] == 'basic'){
					foreach($fieldGroup['location'] as $field_groups_value){
						foreach($field_groups_value as $field_group_key => $field_group_value){
							if($field_group_key == 'postTypes'){
								foreach($field_group_value as $post_type_field_group){
									if($post_type_field_group['selected']){
										if($post_type_field_group['label'] == 'All'){
											$locationInst = LocationRule::getInstance();
											$posttype = $locationInst->getAllPostTypes([]);
										}else{
											$posttype[] = $post_type_field_group['label'];
										}
									}
								}
							}
							else{
								foreach($field_group_value as $post_type_field_group){
									if($post_type_field_group['selected']){
										$posttype[] = 'edit-'.$post_type_field_group['label'];
									}
								}
							}
						}
					}
				}
				elseif($fieldGroup['type_rule'] == 'advanced'){
					foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){
						if($fieldGroupValues[0]['param'] == 'Post Type'){
							$posttype[] = $fieldGroupValues[2]['value'];
						}else{
							$posttype[] = 'edit-'.$fieldGroupValues[2]['value'];
						}  
					}
				}
			}
		}
		
		$acceptable_screens_arr = array_merge($acceptable_screens, $posttype);
		
	
		if($this->pluginScreenHookSuffix == $screen->id || in_array($screen->id, $acceptable_screens_arr) ){
			wp_register_script(self::$plugin->getPluginSlug() . 'smack_react_script', plugins_url('assets/js/tools-engine.js', __FILE__), array('jquery'));
        	wp_enqueue_script(self::$plugin->getPluginSlug() . 'smack_react_script');
			wp_enqueue_style(self::$plugin->getPluginSlug().'boostrap-css', plugins_url( 'assets/css/bootstrap.min.css',__FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'font-awesome-css', plugins_url('assets/css/font-awesome.min.css', __FILE__));
			// wp_enqueue_style(self::$plugin->getPluginSlug() . 'ultimate-fields-css', plugins_url('assets/css/ultimate_fields.css', __FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'react-datepicker-css', plugins_url('assets/css/react-datepicker.css', __FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'react-datetimepicker-css', plugins_url('assets/css/react-datetime.css', __FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'react-timepicker-css', plugins_url('assets/css/react-timepicker.css', __FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'react-toastify-css', plugins_url('assets/css/ReactToastify.css', __FILE__));
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'ultimate-fields-css', plugins_url('assets/css/ultimate_fields.css', __FILE__));
			
			wp_enqueue_style(self::$plugin->getPluginSlug() . 'rc-timepicker-css', plugins_url('assets/css/rc-timepicker.css', __FILE__));
			wp_localize_script(self::$plugin->getPluginSlug().'admin-script', 'wpr_object', array( 'imagePath' => 'assets/images/'));
			// wp_localize_script(self::$plugin->getPluginSlug().'admin-script', 'wpr_object', array( 'file' => 'assets/images'));
			
        }

		/* Create Nonce */
		$secure_uniquekey_tools = array(
			'url' => admin_url('admin-ajax.php') ,
			'nonce' => wp_create_nonce('smack-tools-engine-key')
		);
		wp_localize_script(self::$plugin->getPluginSlug() . 'smack_react_script', 'smack_tools_engine_object', $secure_uniquekey_tools);
	}
}
