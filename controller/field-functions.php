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
 * Class UltimateField
 * @package Smackcoders\TOOLSENGINE
 */
class UltimateFields
{
	protected static $instance = null,$plugin,$helperInst,$groupInst,$formTaxInst;

	/**
	 * UltimateField constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * UltimateField Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$groupInst = FieldGroup::getInstance();
			self::$helperInst = UltimateHelper::getInstance();
			self::$formTaxInst=Taxonomy::getInstance();
			
		}
		return self::$instance;
	}
   
	
	public static function updateField( $field, $specific = array(),$action, $i = null ) {
		$field = self::$instance->validateField( $field );
		$field = wp_unslash( $field );        
		$field = self::$groupInst->parseType( $field );

        //s $field['parent']=$field_group['ID'];
		if( $field['parent'] && !is_numeric($field['parent']) ) { 
			$parent = self::$instance->getFieldPost( $field['parent'] );
			$field['parent'] = $parent ? $parent->ID : 0;
		}

		$_field = $field;
		self::$groupInst->extractVars( $_field, array( 'ID', 'key', 'label', 'name', 'prefix', 'value', 'menu_order', 'id', 'class', 'parent', '_name', '_prepare', '_valid' ) );
		
		$excerpt = '';

		//** Handling the clone(copy) fields */
		
		if(strstr($field['label'],'copy')){
			$excerpt = explode('copy',$field['label']);
			if(is_numeric($excerpt[1])){
				$excerpt = $field['name'].'copy'.$excerpt[1];
			}
			else {
				$excerpt = $field['name'];
			}
		}
		else {
			$excerpt = $field['name'];
		}

		$save = array(
			'ID'			=> $field['ID'],
			'post_status'	=> 'publish',
			'post_type'		=> 'smack-field',
			'post_title'	=> $field['label'],
			// 'post_name'		=> $field['key'],
			// 'post_excerpt'	=> $field['name'],
			//'post_excerpt'	=> $slug_name,
			'post_name'		=> $field['name'],
			'post_excerpt'	=> $excerpt,
			'post_content'	=> maybe_serialize( $_field ),
			'post_parent'	=> $field['parent'],
			'menu_order'	=> $i,
		);
       
		if( $specific ) {
			$specific[] = 'ID';
			$save = self::$helperInst->getSubArray( $save, $specific );
		}

		//remove_filter( 'content_save_pre', 'wp_targeted_link_rel' );
		$save = wp_slash( $save );
		$url1= admin_url('post.php?post=' . $field['parent'] . '&action=edit');
		if($action == 'edit'){
			if( $field['ID'] != 0) {
				$update_result = wp_update_post( $save );
				if($update_result == 0){
					unset($save['ID']);
					$field['ID'] = wp_insert_post( $save );
				}
			} 
			else{
				unset($save['ID']);
				$field['ID'] = wp_insert_post( $save );
			}
	    }
	    else{
			unset($save['ID']);
			$field['ID'] = wp_insert_post( $save );
		}       
		
		if($field['type'] == 'group' || $field['type'] == 'repeater'){
			$subArray = array();
			$j = 0;
			
			self::$instance->insertGroupFields($field, $action);
		}

		return $field;
	}

	public static function insertGroupFields($field, $action){
		global $wpdb;
		$group_name = $field['name'];
		$get_group_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = '$group_name' AND post_type = 'smack-field' ");
		
		$temp = 0;
		foreach($field['sub_fields'] as $subfields){
		
			foreach($subfields as $sub_field){
				$fields_array = [];
				foreach($sub_field as $sub_field_key => $sub_field_value) {
					foreach($sub_field_value as $sub_field_key1 => $sub_field_value1) {	
						$fields_array[$sub_field_key1] = $sub_field_value1;
					}		 
				}
		
				$fields_array = self::$instance->validateField( $fields_array );
				$fields_array = wp_unslash( $fields_array );        
				$fields_array = self::$groupInst->parseType( $fields_array );

				$fields_array['parent'] = $get_group_id;

				$_field = $fields_array;
				self::$groupInst->extractVars( $_field, array( 'ID', 'key', 'label', 'name', 'prefix', 'value', 'menu_order', 'id', 'class', 'parent', '_name', '_prepare', '_valid' ) );

				$excerpt = '';

				//** Handling the clone(copy) fields */
				
				if(strstr($fields_array['label'],'copy')){
					$excerpt = explode('copy',$fields_array['label']);
					if(is_numeric($excerpt[1])){
						$excerpt = $fields_array['name'].'copy'.$excerpt[1];
					}
					else {
						$excerpt = $fields_array['name'];
					}
				}
				else {
					$excerpt = $fields_array['name'];
				}

				$save_field = array(
					'ID'			=> $fields_array['ID'],
					'post_status'	=> 'publish',
					'post_type'		=> 'smack-field',
					'post_title'	=> $fields_array['label'],
					'post_name'		=> $fields_array['name'],
					'post_excerpt'	=> $excerpt,
					'post_content'	=> maybe_serialize( $_field ),
					'post_parent'	=> $fields_array['parent'],
					'menu_order'	=> $temp,
				);

				if($action == 'edit'){
					if( $fields_array['ID'] != 0) {
						$update_result = wp_update_post( $save_field );
						if($update_result == 0){
							unset($save_field['ID']);
							$fields_array['ID'] = wp_insert_post( $save_field );
						}
					} 
					else{
						unset($save_field['ID']);
						$fields_array['ID'] = wp_insert_post( $save_field );
					}
				}
				else{
					unset($save_field['ID']);
					$fields_array['ID'] = wp_insert_post( $save_field );
				}     
			}
			$temp++;  
		}
		
	}

	public static function validateField( $field = array() ) {
      
		if( is_array($field) && !empty($field['_valid']) ) {
			return $field;
		}
       
		$field = wp_parse_args($field, array(
			'ID'				=> 0,
			'key'				=> isset($_POST['key']) ? sanitize_text_field($_POST['key']) : '',
			'label'				=> '',
			'name'				=> '',
			'prefix'			=> '',
			'type'				=> 'text',
			'value'				=> null,
			'menu_order'		=> 0,
			'instructions'		=> '',
			'required'			=> false,
		//	'id'				=> '',
		//	'class'				=> '',
			'conditional_logic'	=> false,
			'parent'			=> 0,
		));

		$field['ID'] = (int) $field['ID'];
		$field['menu_order'] = (int) $field['menu_order'];
		
		$field['_name'] = $field['name'];
		$field['_valid'] = 1;  
		return $field;
	}

	public static function getFieldPost( $id = 0 ) {
		
		if( is_numeric($id) ) { 
			return get_post( $id );

		}
		elseif( is_string($id) ) { 
		
	
			$type = self::$instance->isFieldKey($id) ? 'key' : 'name';
			
			$cache_key = self::$instance->cacheKey( "getFieldPost:$type:$id" );
			$post_id = wp_cache_get( $cache_key, 'smack-field' );
			if( $post_id === false ) { 
				
				// Query posts.
				$posts = get_posts(array(
					'posts_per_page'			=> 1,
					'post_type'					=> 'smack-field',
					'orderby' 					=> 'menu_order title',
					'order'						=> 'ASC',
					'suppress_filters'			=> false,
					'cache_results'				=> true,
					'update_post_meta_cache'	=> false,
					'update_post_term_cache'	=> false,
					"smack_field_$type"			=> $id
				));
				
				$post_id = $posts ? $posts[0]->ID : 0;
				
			}
			
			
			if( $post_id ) {
				return get_post( $post_id );
			}
		}
		
		return false;

	}
    public static function cacheKey($key = ''){ 
		return apply_filters( "get_cache_key", $key, $key );
	}
	public static function isFieldKey( $id = '' ) {
       
		if( is_string($id) && substr($id, 0, 6) === 'field_' ) { 
			return true;
		}

	}
	public static function getField( $id = 0 ) { 
		
		if( is_object($id) ) { 
			$id = $id->ID;
		}
	
		$field =self::$instance->getRawField( $id );
		$field = self::$instance->validateField( $field );
		$field['prefix'] = 'smack-field';

		if($field['type'] == 'group' || $field['type'] == 'repeater'){
			global $wpdb;

			$get_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_parent = $id AND post_type = 'smack-field' ", ARRAY_A);
			foreach($get_id as $ids){
				$sub_fields[] = self::$instance->getField($ids['ID']);
			}

			$field['sub_fields'] = $sub_fields;

			// if(is_array($field['sub_fields'])){
			// 	$subfield = $field['sub_fields'];
			// 	foreach($subfield as $fieldd){
			// 		foreach($fieldd as $key => $val){
			// 			if($field['type'] == 'group'){
			// 				$field_name = $val[5]['label'];
			// 			}
			// 			else{
			// 				$field_name = $val[3]['label'];
			// 			}
			// 			$get_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$field_name' AND post_type = 'smack-field' ", ARRAY_A);
			// 			foreach($get_id as $ids){
							
			// 				// $sub_fields[] = self::$instance->getField($ids['ID']);
			// 			}
						
			// 		}
			// 	}
			// 	$field['sub_fields'] = $sub_fields;	
			// }

		}
		// if($field['type'] == 'repeater'){
		// 	if(is_array($field['sub_fields'])){
		// 		global $wpdb;
		// 		$get_sub_fields = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_parent = $id AND post_type = 'smack-field' ", ARRAY_A);
		// 		$sub_fields = [];
		// 		foreach($get_sub_fields as $get_sub_field){
		// 			$sub_fields[] = self::$instance->getField($get_sub_field['ID']);
		// 		}
		// 	}
		// 	$field['sub_fields'] = $sub_fields;
		// }	
		return $field;
	}
	
	public static function smackGetSetting( $name, $value = null ) {
	
		// validate name
		$name =self::$instance-> validateSetting( $name );
		
		// check settings
		if( self::$instance->hasSetting($name) ) {
			$value = self::$instance->getSetting( $name );
		}
		
		// filter
		$value = apply_filters( "smack/settings/{$name}", $value );
		
		return $value;
	}

	public static function hasSetting( $name ) {
		return isset(self::$instance->settings[ $name ]);
	}

	public static function settings( $customizer ) {
		// vars
		$data = array();
		$settings = $customizer->settings();
			
		// bail ealry if no settings
		if( empty($settings) ) return false;
			
		// loop over settings
		foreach( $settings as $setting ) {
			
			// vars
			$id = $setting->id;
					
			// verify settings type
			if( substr($id, 0, 6) == 'widget' || substr($id, 0, 7) == 'nav_menu' ) {
				// allow
			} else {
				continue;
			}
			
			// get value
			$value = $setting->post_value();	
						
			// bail early if no smack
			if( !is_array($value) || !isset($value['smack-field']) ) continue;
				
			// set data	
			$setting->smack = $value['smack'];
			
			// append
			$data[] = $setting;
						
		}	
		
		// bail ealry if no settings
		if( empty($data) ) return false;
		
		return $data;	
	}

	public static function getSetting( $name ) {
		return isset(self::$instance->settings[ $name ]) ? self::$instance->settings[ $name ] : null;
	}

	public static function validateSetting( $name = '' ) {
		return apply_filters( "smack/validateSetting", $name );
	}

	public static function isFilterEnabled( $name = '' ) {
		return self::$instance->getStore( 'filters' )->get( $name );
	}

	public static function getStore( $name = '' ) {
		global $isStores;
		return isset( $isStores[ $name ] ) ? $isStores[ $name ] : false;
	}

    public static function getRawField( $id = 0 ) {
		
		$post =self::$instance-> getFieldPost( $id );
	
		if( !$post ) { 
			return false;
		}
		
		
		if( $post->post_type !== 'smack-field' ) {
			return false;
		}
		
		$field = (array) maybe_unserialize( $post->post_content );		
		//added
		if(isset($field['choices']) && !empty($field['choices'])){
			$field['choices'] = str_replace('<br />', "\n", $field['choices']);
		}
		if((isset($field['select_multiple_values'])) && ($field['select_multiple_values'] == 1) && (isset($field['default_value']))){
			$field['default_value'] = str_replace('<br />', "\n", $field['default_value']);
		}

		if(isset($field['message']) && !empty($field['message'])){
			$field['message'] = str_replace('<br />', "\n", $field['message']);
		}
		
		// update attributes
		$field['ID'] = $post->ID;
		// $field['key'] = $post->post_name;
		$field['key'] = $post->post_excerpt;
		$field['label'] = $post->post_title;
		// $field['name'] = $post->post_excerpt;
		$field['name'] = $post->post_name;
		$field['menu_order'] = $post->menu_order;
		$field['parent'] = $post->post_parent;
	  
		return $field;
	}

	function getFields( $parent ) { 
//		$fields[]= isset($fields) ? $fields: '';
		$fields = [];
	
		if( !is_array($parent) ) { 
			$parent = self::$groupInst->getFieldGroup( $parent );
				
			if( !$parent ) {
				return array();
			}
		}
	
		$raw_fields = self::$instance->getRawFields( $parent['ID'] );
		
		foreach( $raw_fields as $raw_field ) {
			$fields[] = self::$instance->getField( $raw_field['ID'] );
		}	
		return $fields;	
	}

	public static function getRawFields( $id = 0 ) {
		
		$posts = get_posts(array(
			'posts_per_page'			=> -1,
			'post_type'					=> 'smack-field',
			'orderby'					=> 'menu_order',
			'order'						=> 'ASC',
			'suppress_filters'			=> true, 
			'cache_results'				=> true,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'post_parent'               => $id,
			'post_status'				=> array('publish', 'trash','draft'),
		));
		
		
		$post_ids = array();
		foreach( $posts as $post ) {
			$post_ids[] = $post->ID;
		}
			
		$fields = array();
		foreach( $post_ids as $post_id ) {
			$fields[] =self::$instance->getRawField( $post_id );
		}
		
		return $fields;
	}	
}