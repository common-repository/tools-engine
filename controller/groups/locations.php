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
class LocationRule
{
	protected static $instance = null,$plugin,$fieldGrpInstance;

	/**
	 * LocationRule constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * LocationRule Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$fieldGrpInstance = FieldGroup::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public static function doHooks(){
		// ajax calls for advanced rules
		add_action('wp_ajax_getLocationTypes', array(self::$instance,'getLocationTypes'));
		add_action('wp_ajax_getPostTemplateRule',array(self::$instance,'getPostTemplateRule'));
		add_action('wp_ajax_getPostTypeRule', array(self::$instance,'getPostTypes'));
		add_action('wp_ajax_getPostStatusRule', array(self::$instance,'getPostStatusRule'));
		add_action('wp_ajax_getallPosts', array(self::$instance,'getallPosts'));
		add_action('wp_ajax_getPostFormatRule', array(self::$instance,'getPostFormatRule'));
		add_action('wp_ajax_getPostCategoryRule', array(self::$instance,'getPostCategoryRule'));
		add_action('wp_ajax_getPageTypeRule', array(self::$instance,'getPageTypeRule'));
		add_action('wp_ajax_getPages', array(self::$instance,'getPages'));
		add_action('wp_ajax_getparentPages', array(self::$instance,'getparentPages'));
		add_action('wp_ajax_getCurrentUserRole', array(self::$instance,'getCurrentUserRole'));	
		add_action('wp_ajax_getCurrentUsers', array(self::$instance,'getCurrentUsers'));	
		add_action('wp_ajax_getUserForm', array(self::$instance,'getUserForm'));
		add_action('wp_ajax_getUserRole', array(self::$instance,'getUserRole'));
		add_action('wp_ajax_getPostTaxonomy', array(self::$instance,'getPostTaxonomy'));
		add_action('wp_ajax_getTaxonomy', array(self::$instance,'getTaxonomy'));
		add_action('wp_ajax_getWidget', array(self::$instance,'getWidget'));
		add_action('wp_ajax_getMenu', array(self::$instance,'getMenu'));
		add_action('wp_ajax_getMenuItem', array(self::$instance,'getMenuItem'));
		add_action('wp_ajax_getComment', array(self::$instance,'getComment'));
		add_action('wp_ajax_getAttachmentRules', array(self::$instance,'getAttachmentRules'));
		add_action('wp_ajax_getPageTemplate', array(self::$instance,'getPageTemplate'));	
		add_action('wp_ajax_getAllFieldNames', array(self::$instance,'getAllFieldNames'));	

		// ajax calls for basic rule
		add_action('wp_ajax_basicRulePosttype', array(self::$instance,'basicRulePosttype'));	
		add_action('wp_ajax_basicRuleUser', array(self::$instance,'basicRuleUser'));	
		add_action('wp_ajax_basicRuleTaxonomy', array(self::$instance,'basicRuleTaxonomy'));
		
		// ajax for import and export
		add_action('wp_ajax_getToolsParamsRule',array(self::$instance,'getToolsParams'));
	}

	public function getToolsParams() {
		global $wpdb;

		$response = $wpdb->get_results("SELECT ID , post_title FROM {$wpdb->prefix}posts WHERE (post_type = 'tools-engine') AND post_status = 'publish'");
		
		echo wp_json_encode($response);
		wp_die();
	}

	public static function getLocationTypes(){

		$field_type = array(array('Post'=> array(
			'Post Type' => 'post_type',
			'Post Template' => 'post_template',
			'Post Status' => 'post_status',
			'Post Format' => 'post_format',
			'Post Category' => 'post_category',
			'Post Taxonomy' => 'post_taxonomy',
			'Post' => 'post'
		)),array('Page'=> array(
			'Page Template' => 'page_template',
			'Page Type' => 'page_type',
			'Page Parent' => 'page_parent',
			'Page' => 'page'
		)),array('User'=>array(
			'Current User' => 'current_user',
			'Current User Role' => 'current_user_role',
			'User Form' => 'user_form',
			'User Role' => 'user_role'
		)),array('Forms'=>array(
			'Taxonomy' => 'taxonomy',
			'Attachment' => 'attachment',
			'Comment' => 'comment',
			'Widget' => 'widget',
			'Menu' => 'nav_menu',
			'Menu Item' => 'nav_menu_item',
			'Block' => 'block',
			'Options Page' => 'options_page'
		)));
		$locationTypes = self::$instance->getFormattedArray($field_type);

		$response = $locationTypes ;
		echo wp_json_encode($response);
		wp_die();
	}

	public function getFormattedArray($static_value){

		if (is_array($static_value) || is_object($static_value)){
			foreach($static_value as $key=>$values){
				foreach($values as $tkey=>$tvalues){
					foreach($tvalues as $fkey=>$fvalues){
						$static_fields_getting[$tkey][$fkey] = array('label' => $fkey,
							'value' => $fvalues			
						);
					}
				}
			}
		}
		return $static_fields_getting;

	}
     
	public static function getPostTypeRule(){
		$postTypes = get_post_types();
	
		$result = array_diff_key($postTypes,  
			array_flip((array) ['attachment']));

		$postTypeLabel = self::$instance->getPostLabel($postTypes);
		
		$postTypeList = self::$instance->labelNameFormat($postTypeLabel);
		
		echo wp_json_encode($postTypeList);
		wp_die();

	}
	
	public static function getPostTemplateRule(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
        $choices = array(
			'Default Template' => apply_filters( 'default_page_template_title',  __('Default Template', 'tools-engine') )
		);
		
		$posttemplates=get_page_templates();
		$posttem=array_merge($choices,$posttemplates);
		$postTemplateList = self::$instance->labelNameFormat($posttem);
	
		echo wp_json_encode($postTemplateList);
		wp_die();
	}

	public static function getPostStatusRule() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wp_post_statuses;
		
		if( !empty($wp_post_statuses) ) {

			$i = 0;
			foreach( $wp_post_statuses as $status ) {

				$postStatuses[$i][ 'value' ] = $status->name;
				$postStatuses[$i][ 'label' ] = $status->label;
				$i++;

			}

		}
		echo wp_json_encode($postStatuses);
		wp_die();
	}

	public static function getPostFormatRule() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$formatChoices = get_post_format_strings();	
		$postFormat = self::$instance->labelNameFormat($formatChoices);
		echo wp_json_encode($postFormat);
		wp_die();
	}

	public static function getPostCategoryRule(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$postCategory = get_categories('hide_empty=0');
		$i = 0;

		foreach ($postCategory as $pKey => $pValues){

			$formatChoicess[$i]['label'] = $pValues->name;
			$formatChoicess[$i]['value'] = $pValues->slug;
			$i++;

		}	
		echo wp_json_encode($formatChoicess);
		wp_die();
	}

	public static function getPageTemplate(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$choices = array(
			'Default Template' => apply_filters( 'default_page_template_title',  __('Default Template', 'tools-engine') )
		);
		$posttemplates=get_page_templates();
		$posttem=array_merge($choices,$posttemplates);
	
		$postTemplateList = self::$instance->labelNameFormat($posttem);
	
		echo wp_json_encode($postTemplateList);
		wp_die();	
	}

	public function getPostLabel($postTypes){
		$ref = array();
		$r = array();

		foreach( $postTypes as $postType ) {	
			$label = self::$instance->getPostTypeLabels($postType);
			$r[ $postType ] = $label;

			if( !isset($ref[ $label ]) ) {
				$ref[ $label ] = 0;			
			}
			$ref[ $label ]++;
		}

		foreach( array_keys($r) as $i ) {
			$post_type = $r[ $i ];

			if( $ref[ $post_type ] > 1 ) {
				$r[ $i ] .= ' (' . $i . ')';
			}
		}
		return $r;
	}

	public static function getPostTypeLabels( $post_type ) {
		$label = $post_type;

		if( post_type_exists($post_type) ) {
			$obj = get_post_type_object($post_type);
			$label = $obj->labels->singular_name;
		}
		return $label;
	}

    public static function getPages(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$args=array('post_type'=>'page',
		'post_status' => 'publish');
     	$page_titles = wp_list_pluck( get_pages(), 'post_title' );
		$pageLabel = self::$instance->getPostLabel($page_titles);
		$pages = self::$instance->labelNameFormat($pageLabel);
		 echo wp_json_encode($pages);
		 wp_die();
	}

	public static function getparentPages(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$arg=array('post_parent'=>0,'post_type'=>'page');
		$parentpage=get_posts($arg);
		
		$parent=wp_list_pluck( $parentpage, 'post_title' );
		$parentLabel = self::$instance->getPostLabel($parent);
		$parents = self::$instance->labelNameFormat($parentLabel);
		echo wp_json_encode($parents);
		 wp_die();
	}

    public static function getallPosts(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$args=array('post_type'=>'post');
		$posts=get_posts($args);
		$post=wp_list_pluck( $posts, 'post_title' );
		$pageLabel = self::$instance->getPostLabel($post);
		$posts1 = self::$instance->labelNameFormat($pageLabel);
		echo wp_json_encode($posts1);
		wp_die();	
	}
	
	public static function getPageTypeRule($post_type){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$pageType=array(
			'Front Page'=>'front_page',
			'Posts Page'=>'posts_page',
			'Top Page'=>'top_page',
			'Parent Page'=>'parent_page',
			'Child Page'=>'child_page'
		);
		$pageTypeValues = self::$instance->labelNameFormat($pageType);
		echo wp_json_encode($pageTypeValues);
		wp_die();
	}

	public static function getCurrentUserRole( ) {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wp_roles;
		$currentUser = $wp_roles->get_names();
		$currentUserRole = self::$instance->labelNameFormat($currentUser);		
		echo wp_json_encode($currentUserRole);
		wp_die();
	}

	public static function getUserRole( ) {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
	    global $wp_roles;
		$currentUser = $wp_roles->get_names();
		$array=array("All"=>'all');
		$currentUsers=array_merge($array,$currentUser);
		$currentUserRole = self::$instance->labelNameFormat($currentUsers);	
		echo wp_json_encode($currentUserRole);
		wp_die();
	}
	
	public static function getCurrentUsers( $post_type ) {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$currentUser = array(
			'Logged in' => 'logged_in',
			'Viewing front end' => 'viewing_front',
			'Viewing back end' => 'viewing_back'			
		);
		$currentUsers = self::$instance->labelNameFormat($currentUser);
		echo wp_json_encode($currentUsers);
		wp_die();

	}

	public static function getUserForm() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$userForm = array(
			'All' => 'all',
			'Add' => 'add',
			'Add/Edit' => 'add/edit',
			'Register' => 'register'			
		);
		$userForms = self::$instance->labelNameFormat($userForm);
		echo wp_json_encode($userForms);
		wp_die();

	}

	public static function getPostTaxonomy(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$helperInstance = SmackFieldHelper::getInstance();
		$postTaxonomyList = $helperInstance->smack_get_taxonomies();
		echo wp_json_encode($postTaxonomyList);
		wp_die();
	}

    public static function getTaxonomy(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$taxonomies = self::$instance->getTaxonomies();
		$taxonomylabel = self::$instance->labelNameFormat($taxonomies);
		echo wp_json_encode($taxonomylabel);
		wp_die();
	}

	public function getTaxonomies(){
		$taxo = get_taxonomies();
        foreach($taxo as $tax){ 
            $terms_arg = array('taxonomy' => $tax,
                                'hide_empty' => false,
                            );
			$all_term_taxos[] = $terms_arg['taxonomy'];
		}
		
		$taxonomies = array_combine($all_term_taxos, $all_term_taxos);
		unset($taxonomies['nav_menu']);
		unset($taxonomies['link_category']);
		//Unset post_format for removed the post_format option in taxonomies module
		unset($taxonomies['post_format']);
		unset($taxonomies['wp_theme']);
		return $taxonomies;
	}

	public static function getWidget() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wp_widget_factory;
		$choices = array('all' => 'All');
		if( !empty( $wp_widget_factory->widgets ) ) {

			foreach( $wp_widget_factory->widgets as $widget ) {
				$choices[ $widget->id_base ] = $widget->name;	
				$widgets = self::$instance->labelNameFormat($choices);
			}			

		}				
		echo wp_json_encode($widgets);
		wp_die();		
	}

	public static function getMenu() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$choices = array(
			'all' => 'All',
		);

		$navLocations = get_registered_nav_menus();
		
		if( !empty($navLocations) ) {
			//$cat = 'Menu Locations';
			foreach( $navLocations as $slug => $title ) {
				$choices[ 'location/'.$slug ] = $title;
			}
		}

		$navMenus = wp_get_nav_menus();
		if( !empty($navMenus) ) {
			//$cat = 'Menus';
			foreach( $navMenus as $navMenu ) {
				$choices[ $navMenu->term_id ] = $navMenu->name;
			}
		}
		
		$posttemplateLabel = self::$instance->getPostLabel($choices);
		$postTemplateList = self::$instance->labelNameFormat($posttemplateLabel);
		echo wp_json_encode($postTemplateList);
		wp_die();

	}
	
	public function labelNameFormat($staticValue){
		if (is_array($staticValue) || is_object($staticValue)){
			foreach($staticValue as $sKey=>$sValue){
				$labelName[] = array('label' => ucfirst($sKey),
					'value' => $sValue			
				);
			}
		}
		return $labelName;
	}

    public static function getMenuItem(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$choices = array(
			'all' => 'All',
		);

		$navLocations = get_registered_nav_menus();
	
		if( !empty($navLocations) ) {
			//$cat = 'Menu Locations';
			foreach( $navLocations as $slug => $title ) {
				$choices[ 'location/'.$slug ] = $title;
			}
		}

		$navMenus = wp_get_nav_menus();
		
		if( !empty($navMenus) ) {
			//$cat = 'Menus';
			foreach( $navMenus as $navMenu ) {
				$choices[ $navMenu->term_id ] = $navMenu->name;
			}
		}
		
		$posttemplateLabel = self::$instance->getPostLabel($choices);
		$postTemplateList = self::$instance->labelNameFormat($posttemplateLabel);
		echo wp_json_encode($postTemplateList);
		wp_die();

	}

	public static function getAttachmentRules(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$mimes = get_allowed_mime_types();
		$choices = array(
			'all' => 'All'
		);
		
		foreach( $mimes as $type => $mime ) {
			$choices[ $mime ] = "$type ($mime)";
		}
				
		$attachment=self::$instance->getPostLabel($choices);
		$attachmentlist=self::$instance->labelNameFormat($attachment);
		echo wp_json_encode($attachmentlist);
		wp_die();
	}

    public static function getComment($args=array()){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$comments=array('All'=>'all');
		$exclude = self::$fieldGrpInstance->extractVar( $args, 'exclude', array() );
		$exclude[] = 'smack-field';
		$exclude[] = 'tools-engine';
		$exclude[] ='attachment';
		$objects=get_post_types($args,'objects');
		foreach( $objects as $i => $object ) {
			if( in_array($i, $exclude) ) continue;
			if( $object->_builtin && !$object->public ) continue;
			$comments[] = $i;
		}
	
		$commentlabel=self::$instance->getPostLabel($comments);
		$commentlist=self::$instance->labelNameFormat($commentlabel);
		
		echo wp_json_encode($commentlist);
		wp_die();
	}

	public static function getPostTypes( $args = array() ) {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$posttypelabel = self::$instance->getAllPostTypes($args);
		$posttypelist=self::$instance->labelNameFormat($posttypelabel);
		echo wp_json_encode($posttypelist);
		wp_die();
	}

	public function getAllPostTypes($args){
		$post_types = array();
		$postTypes = array();
		$exclude = self::$fieldGrpInstance->extractVar( $args, 'exclude', array() );
		
		$exclude[] = 'smack-field';
		$exclude[] = 'tools-engine';
		$exclude[] ='attachment';
		$objects = get_post_types( $args, 'objects' );
		$other_posttypes = array('attachment','revision','wpsc-product-file','mp_order','shop_webhook','custom_css','customize_changeset','oembed_cache','user_request','_pods_template','wpmem_product','wp-types-group','wp-types-user-group','wp-types-term-group','gal_display_source','display_type','displayed_gallery','wpsc_log','lightbox_library','scheduled-action','cfs','_pods_pod','_pods_field','acf-field','acf-field-group','wp_block','ngg_album','ngg_gallery','nf_sub','wpcf7_contact_form','iv_payment', 'product_variation', 'shop_order_refund');
		
		foreach( $objects as $i => $object ) {	
			if( in_array($i, $exclude) ) continue;
			if( $object->_builtin && !$object->public ) continue;
			$post_types[] = $i;
			$postTypes[$i] = $object->labels->singular_name;
		}

		$post_types = array_diff($post_types, $other_posttypes);

		$final_post_types = [];
		foreach($post_types as $post_type){
			$final_post_types[] = $postTypes[$post_type];
		}
		array_unshift($final_post_types, 'All');
	
		$posttypelabel=self::$instance->getPostLabel($final_post_types);
		return $posttypelabel;
	}

	public function getAllFieldNames(){
		global $wpdb;

		$get_all_group_names = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine' AND post_status = 'publish' ", ARRAY_A);
		$get_group_names = array_column($get_all_group_names, 'post_title');

		$get_all_field_names = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_status = 'publish' ", ARRAY_A);
		$get_field_names = array_column($get_all_field_names, 'post_title');

		$result['success']= true;
		$result['group_names'] = $get_group_names;
		$result['field_names'] = $get_field_names;

		echo wp_json_encode($result);
		wp_die();
	}

	public function basicRulePosttype($args = array()){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$posttypelabel = self::$instance->getAllPostTypes($args);
		foreach($posttypelabel as $sKey => $sValue){
			if($sValue == 'Post'){
				$labelName[] = array('label' => $sValue,
				'selected' => true);
				continue;
			}
			$labelName[] = array('label' => $sValue,
				'selected' => false		
			);
		}
		echo wp_json_encode($labelName);
		wp_die();
	}

	public function basicRuleUser(){	
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wp_roles;
		$currentUser = $wp_roles->get_names();
		foreach($currentUser as $sKey => $sValue){
			$labelName[] = array('label' => $sValue,
				'selected' => false			
			);
		}
		echo wp_json_encode($labelName);
		wp_die();		
	}

	public function basicRuleTaxonomy(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$taxonomies = self::$instance->getTaxonomies();
		foreach($taxonomies as $sKey => $sValue){
			$taxonomylabel[] = array('label' => $sValue,
				'selected' => false			
			);
		}
		echo wp_json_encode($taxonomylabel);
		wp_die();
	}
}