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
 * Class LocationRule
 * @package Smackcoders\TOOLSENGINE
 */
class FieldGroup
{
	protected static $instance = null,$plugin,$helperInst,$fieldInst,$postinst, $themeCodeInst;

	/**
	 * FieldGroup constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * FieldGroup Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$helperInst = UltimateHelper::getInstance();
			self::$fieldInst =  UltimateFields::getInstance();
			self::$postinst=PostView::getInstance();
			self::$themeCodeInst = SmackThemeCode::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function postToolsParamsRule(){
		global $wpdb;

		$field_id = explode(",",sanitize_text_field($_POST['exportField']));
		sort($field_id);

		foreach($field_id as $selected_id){
			$response_field_group = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE ID = $selected_id");

			$response_sub_group = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $selected_id");

			unset($json_sub_group);

			foreach($response_sub_group as $sub_group){
				$sub_group->post_content = unserialize($sub_group->post_content);

				if ($sub_group->post_content['type'] == 'group'){
					$parent_group_id = $sub_group->ID;

					$child_group = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $parent_group_id");
					$child_group_fields = [];

					foreach ($child_group as $child) {
						$child->post_content = unserialize($child->post_content);
						$child_group_fields[] = (object)array(
							'key' => $child->post_name,
							'title' => $child->post_title,
							'content' => $child->post_content,
							'menu_order' => $child->menu_order,
						);
					}

					$json_sub_group[] = (object)array(
						'key' => $sub_group->post_name,
						'title' => $sub_group->post_title,
						'content' => $sub_group->post_content,
						'group_sub_fields' => $child_group_fields,
						'menu_order' => $sub_group->menu_order,
					);
				}
				elseif ($sub_group->post_content['type'] == 'repeater'){
					$parent_repeater_id = $sub_group->ID;
					$child_repeater_fields = [];

					$child_repeater = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $parent_repeater_id");

					foreach ($child_repeater as $child) {
						$child->post_content = unserialize($child->post_content);
						$child_repeater_fields[] = (object)array(
							'key' => $child->post_name,
							'title' => $child->post_title,
							'content' => $child->post_content,
							'menu_order' => $child->menu_order,
						);
					}

					$json_sub_group[] = (object)array(
						'key' => $sub_group->post_name,
						'title' => $sub_group->post_title,
						'content' => $sub_group->post_content,
						'repeater_sub_fields' => $child_repeater_fields,
						'menu_order' => $sub_group->menu_order,
					);
				}
				else {
					$json_sub_group[] = (object)array(
						'key' => $sub_group->post_name,
						'title' => $sub_group->post_title,
						'content' => $sub_group->post_content,
						'menu_order' => $sub_group->menu_order,
					);
				}
			}

			foreach($response_field_group as $field_group){
				$field_group->post_content = unserialize($field_group->post_content);
				$json_field_group[] = (object)array(
					'key' => $field_group->post_name,
					'title' => $field_group->post_title,
					'fields' => $json_sub_group,
					'content' => $field_group->post_content,
					'menu_order' => $field_group->menu_order,
				);
			}
		}

		echo wp_json_encode($json_field_group);
		wp_die();
	}

	public function importToolsParams() {
		global $wpdb;

		$import_field_group = json_decode(stripslashes($_POST['importedValue']));

		foreach($import_field_group as $field_group){

			$wpdb->insert("{$wpdb->prefix}posts",array(
				'post_name' => $field_group->key ,
				'post_title' => $field_group->title,
				'post_excerpt' => strtolower($field_group->title),
				'post_content' => serialize(json_decode(json_encode($field_group->content), true)),
				'post_type' => 'tools-engine',
				'menu_order' => $field_group->menu_order,
				'post_date' => date("y-m-d h:i:s"),
				'post_date_gmt' => date("y-m-d h:i:s"),
				'post_modified' => date("y-m-d h:i:s"),
				'post_modified_gmt' => date("y-m-d h:i:s"),
			));

			$last_id = $wpdb->insert_id;

			foreach($field_group->fields as $sub_group){
				$wpdb->insert("{$wpdb->prefix}posts",array(
					'post_name' => $sub_group->key ,
					'post_title' => $sub_group->title,
					'post_excerpt' => strtolower($sub_group->title),
					'post_content' => serialize(json_decode(json_encode($sub_group->content), true)),
					'post_parent' => $last_id,
					'post_type' => 'smack-field',
					'menu_order' => $sub_group->menu_order,
					'post_date' => date("y-m-d h:i:s"),
					'post_date_gmt' => date("y-m-d h:i:s"),
					'post_modified' => date("y-m-d h:i:s"),
					'post_modified_gmt' => date("y-m-d h:i:s"),
				));

				if ($sub_group->content->type == 'group'){
					$parent_group_id = $wpdb->insert_id;

					foreach($sub_group->group_sub_fields as $group_child){
						$wpdb->insert("{$wpdb->prefix}posts",array(
							'post_name' => $group_child->key ,
							'post_title' => $group_child->title,
							'post_excerpt' => strtolower($group_child->title),
							'post_content' => serialize(json_decode(json_encode($group_child->content), true)),
							'post_parent' => $parent_group_id,
							'post_type' => 'smack-field',
							'menu_order' => $group_child->menu_order,
							'post_date' => date("y-m-d h:i:s"),
							'post_date_gmt' => date("y-m-d h:i:s"),
							'post_modified' => date("y-m-d h:i:s"),
							'post_modified_gmt' => date("y-m-d h:i:s"),
						));
					}
				}

				if ($sub_group->content->type == 'repeater'){
					$parent_repeater_id = $wpdb->insert_id;

					foreach($sub_group->repeater_sub_fields as $repeater_child){
						$wpdb->insert("{$wpdb->prefix}posts",array(
							'post_name' => $repeater_child->key ,
							'post_title' => $repeater_child->title,
							'post_excerpt' => strtolower($repeater_child->title),
							'post_content' => serialize(json_decode(json_encode($repeater_child->content), true)),
							'post_parent' => $parent_repeater_id,
							'post_type' => 'smack-field',
							'menu_order' => $repeater_child->menu_order,
							'post_date' => date("y-m-d h:i:s"),
							'post_date_gmt' => date("y-m-d h:i:s"),
							'post_modified' => date("y-m-d h:i:s"),
							'post_modified_gmt' => date("y-m-d h:i:s"),
						));
					}
				}
			}
		}
		// echo wp_json_encode("Imported Successfully");
		wp_die();
	}

	public static function doHooks(){
		add_action('wp_ajax_saveFields',array(self::$instance, 'postSave'), 10, 2);
		add_action('wp_ajax_sendIdAction',array(self::$instance,'editAction'),10,2);
		add_action('wp_ajax_smack_group_add_fields',array(self::$instance,'smack_group_add_fields'));
		add_action('wp_ajax_smack_add_groups',array(self::$instance,'smack_add_groups'));
		add_action('wp_ajax_smack_delete_field',array(self::$instance,'smack_delete_field'));
		add_action('wp_ajax_ultimate_group_field',array(self::$instance,'ultimate_group_field'));
		add_action('wp_ajax_createCustomTaxonomy',array(self::$instance,'createTerms'));
		add_action('wp_ajax_UpdatePostContent',array(self::$instance, 'UpdatePostContent'));
		add_action('wp_ajax_smack_delete_sub_field',array(self::$instance, 'smack_delete_subfield'));

		// ajax for import and export Tools
		add_action('wp_ajax_postToolsParamsRule',array(self::$instance,'postToolsParamsRule'));
		add_action('wp_ajax_importToolsParams',array(self::$instance,'importToolsParams'));
	}

	public static function remove_duplicateChoice($choices) {
		$get_choice = explode('<br />',$choices);
		$filter_choice = array_values (array_unique($get_choice));
		$choices = implode('<br />',$filter_choice);
		return $choices;
	}

	public static function postSave($post_id){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		if(sanitize_text_field($_POST['active']) == 'Delete'){
			$group_name = sanitize_text_field($_POST['field_group_name']);
			$get_group_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine' AND post_title = '$group_name' ");
			$check_for_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $get_group_id AND post_type = 'smack-field' ", ARRAY_A);
			if(!empty($check_for_fields)){
				foreach($check_for_fields as $fields){
					wp_trash_post($fields['ID']);
				}
			}
			wp_trash_post($get_group_id);
			// wp_redirect(admin_url(). 'edit.php?post_type=tools-engine');
			
			$response['success'] = false;
			$response['url'] = admin_url(). 'edit.php?post_type=tools-engine';
			echo wp_json_encode($response);
			wp_die();
		}

		$location = str_replace( "\\", "", sanitize_text_field($_POST['ultimate_field_group']));
		$location = json_decode($location,true);
		$position = wp_unslash(sanitize_text_field($_POST['position']));
		$position = json_decode($position,true);
		$style = wp_unslash(sanitize_text_field($_POST['style']));
		$style = json_decode($style,true);
	
		if(sanitize_text_field($_POST['type_rule']) == 'advanced'){
			foreach ($location as $keyloc=>$values){
				if(is_array($values)){
					$locations=$values[0];
					$posttypes=$values[0]['group_0'][0]['rule_0'][0]['param'];
					$posttype=$values[0]['group_0'][0]['rule_0'][2]['value'];
				}
			}
		}
		elseif(sanitize_text_field($_POST['type_rule']) == 'basic'){
			$locations = $location;
		}

		$labelins = wp_unslash(sanitize_text_field($_POST['label_placement']));
		$labelins = json_decode($labelins,true);
		$inst = wp_unslash(sanitize_text_field($_POST['instruction_placement']));
		$inst = json_decode($inst,true);
		$hide = wp_unslash(sanitize_text_field($_POST['hide_on_screen']));
		$hide = json_decode($hide,true);
		
		if( wp_is_post_revision($post_id) ) { 
			return $post_id;
		}
		
		//check for empty field name and group name

		if( !empty(wp_kses_post($_POST['ultimate_smack_fields']) )) {
			$fields = wp_kses_post($_POST['ultimate_smack_fields']);
			$field = str_replace( "\\", "", $fields );
			$field = json_decode( $field, true );
			$check_fdlabel = self::$instance->CheckFieldLabelisempty($field);
			if($check_fdlabel != 'Not Empty'){
				$response['success'] = false;
				$response['message'] = $check_fdlabel;
				echo wp_json_encode($response);
				wp_die();	
			}
			
			if(empty($_POST['field_group_name'])){
				$response['success'] = false;
				$response['message'] = "Group name is mandatory";
				echo wp_json_encode($response);
				wp_die();
			}
		}
		
		$fieldGroup=array(
			'ID'                    =>0,
			'key'                   => sanitize_text_field($_POST['key']),
			'title'                 => sanitize_text_field($_POST['field_group_name']),
			'location'				=> $locations,
			'menu_order'			=> 0,
			'position'				=> $position['value'],
			'style'					=> $style['value'],
			'label_placement'		=> $labelins['value'],
			'instruction_placement'	=> $inst['value'],
			'hide_on_screen'		=> array(),
			'active'				=> sanitize_text_field($_POST['active']),
			'description'			=> sanitize_text_field($_POST['description']),
			'type_rule'         => sanitize_text_field($_POST['type_rule']),
		);
	
		$url = esc_url_raw($_POST['url']);
		
		$parse = parse_url($url, PHP_URL_QUERY);
		$par = explode("=",$parse);
		$par1 = explode("&",$par[1]);
		$id = $par1[0];
	
		if(isset($par[2])){
			$action = $par[2];
		}
		else{
			$action = 'new';
		}

		// $grp = self::$instance->updateFieldGroup( $fieldGroup,$url,$id);
		
		// $Id = $grp['ID'];

		$required_fields = [];

		if( !empty(wp_kses_post($_POST['ultimate_smack_fields']) )) {
			$fields = wp_kses_post($_POST['ultimate_smack_fields']);
			$field = str_replace( "\\", "", $fields );
			$field = json_decode( $field, true );
			if(!empty($field)){
				$grp = self::$instance->updateFieldGroup( $fieldGroup,$url,$id);
				$Id = $grp['ID'];
			}
			else{
				$result['success'] = false;
				$result['message'] = "custom fields not found";
				echo wp_json_encode($result);
				wp_die();
			}
			$key=str_replace("\\","",sanitize_text_field($_POST['key']));
			$key=json_decode($key,true);

			for($i=0;$i<count($field);$i++){
			
				foreach ($field[$i] as $fieldkey1){
					$newArray = array();
					foreach($fieldkey1 as $key => $value) {
								
						foreach($value as $key2 => $value2) {	
							$newArray[$key2] = $value2;		 
						}
						
						$specific = false;
						$save = self::$helperInst->formattedVar( $newArray, 'save' );							
					
						if( $save == 'meta' ) {
							$specific = array(
								'menu_order',
								'post_parent',
							);
						}
						
						if( !isset($newArray['parent']) ) {
							$newArray['parent'] = $Id;
						}
					}

					if($newArray['type'] == 'checkbox' || $newArray['type'] == 'radiobutton' || 
					$newArray['type'] == 'select' || $newArray['type'] == 'buttongroup') {
						$newArray['choices'] = self::$instance->remove_duplicateChoice($newArray['choices']);
					}
					$field1=self::$fieldInst->updateField( $newArray, $specific ,$action, $i);	
					
					if(!empty($field1['required'])){
						array_push($required_fields,  'wp-smack-'. $field1['_name']);
					}	
				}
			}	
			
			
		}
		
		// $check_for_existing_required_fields = get_option('wp_smack_required_fields');
		update_option('wp_smack_required_fields', $required_fields);
	
		$field_grp_array = [];
		$field_grp_array['title'] = $grp['title'];
		$fields=self::$fieldInst->getFields($grp['ID']);
		
		if(!empty($fields))
		{
			$result['success']=true;
		}
		
		$result['field_group_name'] = $grp['title'];
		$url=admin_url('post.php?post=' . $grp['ID'] . '&action=edit');
		if($grp['title']){
			$result['success']= true;
		}
		else{
		 	$result['success']= false;
	   	}
	   	//if($field1['parent']==$grp['ID']){
			$result['fields']=$fields;
	  	// }  
		  
	   

		if(sanitize_text_field($_POST['type_rule']) == 'advanced'){
			// added code to change term id to term name
			foreach($grp['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){
				$posttypes = $fieldGroupValues[0]['param'];
				$operator = $fieldGroupValues[1]['operator'];
				$posttype = $fieldGroupValues[2]['value'];

				if($posttypes == 'Post Taxonomy' && $operator == 'is equal to'){
					$term_name = get_term( $posttype )->name;
					$grp['location']['group_0'][0][$fieldGroupKeys][2]['value'] = $term_name;
				}

				if($posttypes == 'Post Category' && $operator == 'is equal to'){
					$term_name = $wpdb->get_var("SELECT name FROM {$wpdb->prefix}terms WHERE slug = '$posttype'");
					$fieldGroup['location']['group_0'][0][$fieldGroupKeys][2]['value'] = $term_name;
				}
			}		
		}
		
	   	$result['location']=$grp['location'];
		$result['url']=$url;	
		$result['code'] = self::$themeCodeInst->smack_theme_code($grp['ID']);
		echo wp_json_encode($result);
		wp_die();			
	}
	
	public static function updateFieldGroup( $field_group ,$url,$id) {
		
		$field_group = self::$instance->validateFieldGroup( $field_group );
		$field_group = wp_unslash( $field_group );		
		$field_group = self::$instance->parseType( $field_group );

		$_field_group = $field_group;
		
		self::$instance->extractVars( $_field_group, array( 'ID', 'key', 'title', 'menu_order', 'fields', 'active', '_valid' ) );
		if(is_numeric($id)){
			$save = array(
				'ID'			=> $id,
				//'post_status'	=> $field_group['active'] == 'true' ? 'publish' : 'smack-disabled',
				'post_status'	=> $field_group['active'] == 'Publish' ? 'publish' : 'smack-disabled',
				'post_type'		=> 'tools-engine',
				'post_title'	=> $field_group['title'],
				'post_name'		=> $field_group['key'],
				'post_excerpt'	=> sanitize_title( $field_group['title'] ),
				'post_content'	=> maybe_serialize( $_field_group ),
				'menu_order'	=> $field_group['menu_order'],
				'comment_status' => 'closed',
				'ping_status'	=> 'closed',
			);
		}
		else{
			$save = array(
				'ID'			=> $field_group['ID'],
				// 'post_status'	=> $field_group['active'] ? 'publish' : 'smack-disabled',
				'post_status'	=> $field_group['active'] == 'Publish' ? 'publish' : 'smack-disabled',
				'post_type'		=> 'tools-engine',
				'post_title'	=> $field_group['title'],
				'post_name'		=> $field_group['key'],
				'post_excerpt'	=> sanitize_title( $field_group['title'] ),
				'post_content'	=> maybe_serialize( $_field_group ),
				'menu_order'	=> $field_group['menu_order'],
				'comment_status' => 'closed',
				'ping_status'	=> 'closed',
			);
		}

		$save = wp_slash( $save );
		
		if ($field_group['ID']!= 0){
		//	$field_group['ID'] = wp_insert_post( $save );
		   wp_update_post( $save );
		}
		else {
			$field_group['ID'] = wp_insert_post( $save );	
		}
		return $field_group;	
	}

    public static function editAction(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$id = intval($_POST['post_id']);
		$action = sanitize_text_field($_POST['action_type']);
		global $wpdb;
		if($action=='edit'){
			$result['success']=true;
		}
		$fieldgrp_details['post_title']=$wpdb->get_results("SELECT post_title from {$wpdb->prefix}posts where ID=$id");
		$fieldgrp_details['post_name']=$wpdb->get_results("SELECT post_name from {$wpdb->prefix}posts where ID=$id");
			
		$fieldGroup = self::$instance->getFieldGroup($id);
		$fields = self::$fieldInst->getFields($fieldGroup);
		if($fieldGroup['type_rule'] == 'advanced'){
			// added code to change term id to term name
			foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){
				$posttypes = $fieldGroupValues[0]['param'];
				$operator = $fieldGroupValues[1]['operator'];
				$posttype = $fieldGroupValues[2]['value'];

				if($posttypes == 'Post Taxonomy' && $operator == 'is equal to'){
					$term_name = get_term( $posttype )->name;
					$fieldGroup['location']['group_0'][0][$fieldGroupKeys][2]['value'] = $term_name;
				}

				if($posttypes == 'Post Category' && $operator == 'is equal to'){
					$term_name = $wpdb->get_var("SELECT name FROM {$wpdb->prefix}terms WHERE slug = '$posttype'");
					$fieldGroup['location']['group_0'][0][$fieldGroupKeys][2]['value'] = $term_name;
				}				
			}
		}
	
		$get_group_status = $wpdb->get_var("SELECT post_status FROM {$wpdb->prefix}posts WHERE ID = $id ");
		
		if($get_group_status == 'publish'){
			$active_status = 'Publish';
		}
		elseif($get_group_status == 'smack-disabled'){
			$active_status = 'Draft';
		}

		$result['post_title']=$fieldgrp_details;
		$result['fields'] = $fields;
		$result['location'] = $fieldGroup['location'];
		// $result['active']=$fieldGroup['active'];
		$result['active'] = $active_status;
		$result['style'] = $fieldGroup['style'];
		$result['position'] = $fieldGroup['position'];
		$result['label_placement'] = $fieldGroup['label_placement'];
		$result['instruction_placement'] = $fieldGroup['instruction_placement'];
		$result['menu_order'] = $fieldGroup['menu_order'];
		$result['description'] = $fieldGroup['description'];
		$result['type_rule'] = $fieldGroup['type_rule'];
		$result['hide_on_screen'] = $fieldGroup['hide_on_screen'];
		$result['key'] = $fieldGroup['key'];
		//$result['url']=$url;
		$result['code'] = self::$themeCodeInst->smack_theme_code($id);
	
		echo wp_json_encode($result);
		wp_die();		
	}

	public static function validateFieldGroup( $field_group = array() ) { 
		
		if( is_array($field_group) && !empty($field_group['_valid']) ) { 
			return $field_group;
		}

		$field_group = wp_parse_args($field_group, array(
		 'ID'		    		=> 0,
		 'key'                => '',
		'title'					=> '',
		'fields'				=> array(),
		'location'				=> array(),
		'menu_order'			=> 0,
		'position'				=> 'normal',
		'style'					=> 'default',
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label',
		'hide_on_screen'		=> array(),
		'active'				=> true,
		'description'			=> '',
		'type_rule'             => 'basic',
		));

		$field_group['ID'] = (int) $field_group['ID'];
		$field_group['menu_order'] = (int) $field_group['menu_order'];
		$field_group['_valid'] = 1;
		return $field_group;
	}

	public static function extractVars( &$array, $keys ) {
		$r = array();
		foreach( $keys as $key ) {
			$r[ $key ] = self::$instance->extractVar( $array, $key );
		}
		return $r;
	}

	public static function extractVar( &$array, $key, $default = null ) {
		if( is_array($array) && array_key_exists($key, $array) ) { 
			$v = $array[ $key ];
			unset( $array[ $key ] );	
		}	 
		return $default;
	}

	public static function parseType( $v ) {
		if( is_string($v) ) {
			$v = trim( $v );
			if( is_numeric($v) && strval(intval($v)) === $v ) {
				$v = intval( $v );
			}
		}
		return $v;
	}

	public function getFieldGroupsView( $filter = array() ) {
		$fieldGroups = array();
		$rawFieldGroups = self::$instance->getRawFieldGroups();
		
		if( $rawFieldGroups ) {
			foreach( $rawFieldGroups as $rawFieldGroup ) {
				$fieldGroups[] = self::$instance->getFieldGroup( $rawFieldGroup['ID'] );
			}
		}
		
		return $fieldGroups;
	}

	public function getRawFieldGroups() {

		$posts = get_posts(array(
			'posts_per_page'			=> -1,
			'post_type'					=> 'tools-engine',
			'orderby'					=> 'menu_order title',
			'order'						=> 'ASC',
			'suppress_filters'			=> false,
			'cache_results'				=> true,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'post_status'				=> array('publish', 'smack-disabled'),
		));
		$postIds = array();
		foreach( $posts as $post ) {
			$postIds[] = $post->ID;
		}
		$fieldGroups = array();

		foreach( $postIds as $postId ) {
			$fieldGroups[] = self::$instance->getRawFieldGroup( $postId );
		}
		return $fieldGroups;
	}

	public function getFieldGroup( $id = 0 ) {

		if( is_object($id) ) {
			$id = $id->ID;
		}

		$fieldGroup = self::$instance->getRawFieldGroup( $id );
		
		if( !$fieldGroup ) {
			return false;
		}
		$fieldGroup = self::$instance->validateFieldGroup( $fieldGroup );
		
		return $fieldGroup;
	}

	public function getRawFieldGroup( $id = 0 ) {
		$post = self::$instance->getFieldGroupPost( $id );
	
		if( !$post ) {
			return false;
		}
		if( $post->post_type !== 'tools-engine' ) {
			return false;
		}
		$fieldGroup = (array) maybe_unserialize( $post->post_content );
		$fieldGroup['ID'] = $post->ID;
		$fieldGroup['title'] = $post->post_title;
		$fieldGroup['key'] = $post->post_name;
		$fieldGroup['menu_order'] = $post->menu_order;
		$fieldGroup['active'] = in_array($post->post_status, array('publish', 'auto-draft'));
		return $fieldGroup;
	}

	public function getFieldGroupPost( $id = 0 ) {
		if( is_numeric($id) ) {
			return get_post( $id );
		} elseif( is_string($id) ) {
			$posts = get_posts(array(
				'posts_per_page'			=> 1,
				'post_type'					=> 'tools-engine',
				'post_status'				=> array('publish', 'smack-disabled', 'trash'),
				'orderby' 					=> 'menu_order title',
				'order'						=> 'ASC',
				'suppress_filters'			=> false,
				'cache_results'				=> true,
				'update_post_meta_cache'	=> false,
				'update_post_term_cache'	=> false,
				'ultimate_smack_group_key'	=> $id
			));
			$post_id = $posts ? $posts[0]->ID : 0;
		}
		if( $post_id ) {
			return get_post( $post_id );
		}
		return false;	
	}

	public function smack_group_add_fields(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$group_id = intval($_POST['group_id']);

		$fieldGroup=self::$instance->getFieldGroup($group_id);
		$fields=self::$fieldInst->getFields($fieldGroup);
		
		$result['success']=true;
		$result['fields']=$fields;

		echo wp_json_encode($result);
		wp_die();
	}

	public function ultimate_group_field(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
	
		$group_name = sanitize_text_field($_POST['field_group_name']);
		$Id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$group_name' ");
		// $Id=$grp['ID'];
	
		if( !empty($_POST['ultimate_smack_fields']) ) {
			
			$fields = sanitize_text_field($_POST['ultimate_smack_fields']);
			$field = str_replace( "\\", "", $fields );
			$field = json_decode( $field, true );
			$check_fdlabel = self::$instance->CheckFieldLabelisempty($field);
			if($check_fdlabel != 'Not Empty'){
				$response['success'] = false;
				$response['message'] = $check_fdlabel;
				echo wp_json_encode($response);
				wp_die();	
			}
		
			// $key=str_replace("\\","",sanitize_text_field($_POST['key']));
			// $key=json_decode($key,true);

			if(isset($_POST['key'])){
				$key=str_replace("\\","",sanitize_text_field($_POST['key']));
				$key=json_decode($key,true);
			}
			else{
				$key = '';
			}
		
			for($i=0;$i<count($field);$i++){
			
				foreach ($field[$i] as $fieldkey1){
					$newArray = array();
					foreach($fieldkey1 as $key => $value) {
								
						foreach($value as $key2 => $value2) {	
							$newArray[$key2] = $value2;		 
						}
						
						$specific = false;
						$save = self::$helperInst->formattedVar( $newArray, 'save' );							
					
						if( $save == 'meta' ) {
							$specific = array(
								'menu_order',
								'post_parent',
							);
						}
						
						if( !isset($newArray['parent'] )) {
							$newArray['parent'] = $Id;
						}
					}
					$field1=self::$fieldInst->updateField( $newArray, $specific ,'edit', $i);	
				
				}
			}	
		}
		
		$result['success']= true;

		echo wp_json_encode($result);
		wp_die();		
	}

	public function smack_add_groups(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$result['success'] = true;
		$result['url'] = admin_url(). 'post-new.php?post_type=tools-engine';
		echo wp_json_encode($result);
		wp_die();
	}

	public function smack_delete_field(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		$group_name = sanitize_text_field($_POST['group_name']);
		$field_name = sanitize_text_field($_POST['field_name']);
		$get_group_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine' AND post_name = '$group_name' ");
		if(array_key_exists('type', $_POST) && $_POST == 'message') {
			$get_field_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $get_group_id AND post_title = '$field_name' ");
		}
		else {
			$get_field_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $get_group_id AND post_name = '$field_name' ");
			$meta_key = 'wp-smack-'.$field_name;
			$metaid = $wpdb->get_results("SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$meta_key'",ARRAY_A);
		}
		if(sanitize_text_field ($_POST['field_type']) == 'repeater') {
			$sub_field_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $get_field_id ");
			if(!empty($sub_field_id)){
				foreach($sub_field_id as $sub_field => $id){
					$id = 	json_decode(json_encode($id), true);
					$id = intval($id['ID']);	
					$val = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE id = $id");
					$meta_key = 'wp-smack-'.$field_name;
					$metavalue = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '$meta_key'",ARRAY_A);	
					foreach($metavalue as $meta_val){
						$repeat = $meta_val['meta_value'];
						for($i=0;$i<$repeat;$i++){
							$meta_key = 'wp-smack-'.$field_name.'_'.$i.'_'.$val;
							$meta_id = $wpdb->get_results("SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$meta_key'",ARRAY_A);
							foreach($meta_id as $meta){
								$meta_id = $meta['meta_id'];
								$wpdb->get_results("DELETE FROM {$wpdb->prefix}postmeta where meta_id ='$meta_id'");
							}
						} 
					}	
					wp_delete_post($id);
				}
			}
		}
		if(sanitize_text_field ($_POST['field_type']) == 'group') {
			$sub_field_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $get_field_id ");
			if(!empty($sub_field_id)){
				foreach($sub_field_id as $sub_field => $id){
					$id = 	json_decode(json_encode($id), true);
					$id = intval($id['ID']);	
					$val = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE id = $id");
					$meta_key = 'wp-smack-'.$field_name.'_'.$val;
					$metaid = $wpdb->get_results("SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$meta_key'",ARRAY_A);
					foreach($metaid as $meta){
						$meta_id = $meta['meta_id'];
						$wpdb->get_results("DELETE FROM {$wpdb->prefix}postmeta where meta_id ='$meta_id'");
					}	
					wp_delete_post($id);
				}
			}
		}
		wp_delete_post($get_field_id);
		foreach($metaid as $meta){
			$meta_id = $meta['meta_id'];
			$wpdb->get_results("DELETE FROM {$wpdb->prefix}postmeta where meta_id ='$meta_id'");
		}
		$result['success'] = true;
		echo wp_json_encode($result);
		wp_die();
	}

	public function smack_delete_subfield(){	
		check_ajax_referer('smack-tools-engine-key', 'securekey');	
		global $wpdb;
		$parent_group_name = sanitize_text_field($_POST['parent_group_name']);
		$group_name = sanitize_text_field($_POST['group_name']);
		$field_name = sanitize_text_field($_POST['field_name']);
		$get_group_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine' AND post_title = '$parent_group_name' ");
		$get_rep_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_name = '$group_name' AND post_parent = $get_group_id ");
		$get_field_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_name = '$field_name' AND post_parent = $get_rep_id ");
		wp_delete_post($get_field_id);
		$result['success'] = true;
		echo wp_json_encode($result);
		wp_die();
	}

	public function createTerms() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		$term_name = sanitize_text_field($_POST['term_name']);
		$type = sanitize_text_field($_POST['taxonomy_type']);
		$new_term = wp_insert_term($term_name,$type);
		if(is_wp_error($new_term)) {
			$result['message'] = $new_term->get_error_message();
		}
		else {
			$result['message'] = 'Added';
			$result['term_data'] = $new_term;
		}
		echo wp_json_encode($result);
		wp_die();
	}

	public function UpdatePostContent() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		$name = sanitize_text_field($_POST['name']);
		$newvalue = sanitize_text_field($_POST['new_option']);
		if(strstr($name,'groupField') || strstr($name,'repeaterField') || strstr($name,'cloneField')) {
			$getname = explode('--',$name);
			$name = $getname[2];
			if(strstr($name,'__')){
				$getname = explode('__',$name);
				$name = $getname[0];
			}
		}
		else {
			if(strstr($name,'wp-smack-'))
			$name = str_replace('wp-smack-','',$name);
		}
		$post_content = $wpdb->get_var("SELECT POST_CONTENT FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_excerpt = '$name'");
		$post_content = unserialize($post_content);
		$choices = $post_content['choices'];
		$post_content['choices'] = $choices . '<br />' . $newvalue;
		$post_content = serialize($post_content);
		$updatedata = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}posts SET post_content = '$post_content' WHERE post_type = 'smack-field' AND post_excerpt = '$name'"));
		if(is_wp_error($updatedata)) {
			$result['message'] = $updatedata->get_error_message();
		}
		else{
		$result['message'] = 'Added';
		}
		echo wp_json_encode($result);
		wp_die();
	}

	public static function CheckFieldLabelisempty($field){
		$missing_array = [];
			foreach($field as $field_key => $field_value){
				foreach($field_value as $field_per_value){
					if (array_keys($field_per_value[5])[0] === 'label'){
						if(empty($field_per_value[5]['label'])){
							array_push($missing_array, $field_per_value[7]['type']);
						}
					}
					else{
						if(empty($field_per_value[3]['label'])){
							array_push($missing_array, $field_per_value[5]['type']);
						}
					}
					/** For Repeater Field */		
					if (isset($field_per_value[5]['type'])){
						if($field_per_value[5]['type'] == 'repeater') {
							if(!empty($field_per_value[8]['sub_fields'])){
								foreach($field_per_value[8]['sub_fields'] as $sub_index => $sub_data){
									foreach($sub_data as $key => $field_data){
										if(empty($field_data[3]['label'])){
											$rep_missing = 'rep_'. $field_data[5]['type'];
											array_push($missing_array,$rep_missing);
										}
									}
								}
							}
						}
					}
					/** For Group Field */		
					if (isset($field_per_value[5]['type'])){
						if($field_per_value[5]['type'] == 'group') {
							if(!empty($field_per_value[8]['sub_fields'])){
								foreach($field_per_value[8]['sub_fields'] as $sub_index => $sub_data){
									foreach($sub_data as $key => $field_data){
										if(empty($field_data[3]['label'])){
											$gp_missing = 'gp_'. $field_data[5]['type'];
											array_push($missing_array,$gp_missing);
										}
									}
								}
							}
						}
					}
				}
		    }
			
			if(!empty($missing_array[0])){
				$missing_fields = implode(',', $missing_array);
				$missing_field = rtrim($missing_fields, ',');
				$response = "Field Label is mandatory for ". $missing_field . " field";	
			}
			else {
				$response = 'Not Empty';
			}
			return $response;
	}
	
}