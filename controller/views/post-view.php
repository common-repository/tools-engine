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
 * Class PostView
 * @package Smackcoders\TOOLSENGINE
 */
class PostView
{
	protected static $instance = null,$widgetInst,$smacktypeInst,$attachInst,$comInst,$plugin,$helperInst,$groupInst,$formTaxInst,$fieldInst,$formmenuInst,$ultimatefield,$userInst;
	protected static $smackHelperInst = null;
	public $selected_image = '';
	public $valid = false;

	/**
	 * PostView constructor.
	 */
	public function __construct()
	{
		add_action('wp_ajax_smack_posttype_based_filter',array($this,'smack_posttype_based_filter'));
		add_action('wp_ajax_smack_taxo_based_filter',array($this,'smack_taxo_based_filter'));
		add_action('wp_ajax_removeFieldValue',array($this,'removeFieldValue'));	
	}

	/**
	 * PostView Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$widgetInst=Widgets::getInstance();
			self::$plugin = Plugin::getInstance();
			self::$groupInst = FieldGroup::getInstance();
			self::$comInst=Comments::getInstance();
			self::$helperInst = UltimateHelper::getInstance();
			self::$formTaxInst=Taxonomy::getInstance();
			self::$userInst=Users::getInstance();
			self::$formmenuInst=Taxmenu::getInstance();
			self::$attachInst=Attachments::getInstance();
			self::$smacktypeInst=SmackFieldtype::getInstance();
			self::$ultimatefield=UltimateFields::getInstance();
			self::$smackHelperInst = SmackFieldHelper::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}	

	public static function doHooks(){
		add_action('load-post.php',		array(self::$instance, 'startView'));
		add_action('load-post-new.php',	array(self::$instance, 'startView'));
		add_action( 'save_post', array(self::$instance,'save_field_data'), 10, 2);
		add_action( 'untrash_post', array(self::$instance, 'smack_restore_subfield') );
	}

	public static function smack_restore_subfield($post_id = ''){
		wp_update_post(array('post_status' => 'publish'));
	}

	public static function startView(){ 
		global $typenow;
		$exceptType = array('tools-engine', 'attachment');
		if( in_array($typenow, $exceptType) ) { 
			return;
		}
		add_action('add_meta_boxes', array(self::$instance, 'add_meta_boxes'), 10, 2);	
	}   

	//For repeater relational and link fd

	Public function removeFieldValue() {
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$index = $_POST['index'];
		if(isset($_POST['field_value']) && !empty($_POST['field_value'])){
			$fieldvalue = $_POST['field_value'];
			$fieldvalue = str_replace('\"','"',$fieldvalue);
			$fieldvalue = json_decode($fieldvalue,true);
			if(array_key_exists($index,$fieldvalue)){
				unset($fieldvalue[$index]);
				$fieldvalue = array_values($fieldvalue);
			}
			$result = [];
			$result['message'] = "success";
			$result['fieldvalue'] = json_encode($fieldvalue);
			echo wp_json_encode($result);
			wp_die();
		}
	}


	public static function add_meta_boxes( $post_type, $post ) {
		
		global $post;
		$postboxes = array();
		$fieldGroups = self::$groupInst->getFieldGroupsView(array(
			'post_id'	=> $post->ID, 
			'post_type'	=> $post_type
		));
		
		if( $fieldGroups ) {
			foreach( $fieldGroups as $fieldGroup ) {	
				
				// skip current loop if group is inactive
				if(!$fieldGroup['active']){
					continue;
				}

				$id = "ultimate-{$fieldGroup['key']}";			
				$title = $fieldGroup['title'];				
				$context = $fieldGroup['position'];		
				$priority = 'high';	
		
				if($fieldGroup['type_rule'] == 'advanced'){
					foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){
						
						$posttypes = $fieldGroupValues[0]['param'];
						$operator = $fieldGroupValues[1]['operator'];
						$posttype = $fieldGroupValues[2]['value']; 

						$postarray[]=$posttypes;
						if($context=='Normal (after content)'){
							$context='normal';
						}
						
						if( $context == 'side' ) {
							$priority = 'core';
						}
					
						$postboxes[] = array(
							'id'		=> $id,
							'key'		=> $fieldGroup['key'],
							'style'		=> $fieldGroup['style'],
							'label'		=> $fieldGroup['label_placement'],
							'edit'		=> self::$instance->getFieldGroupEditLink( $fieldGroup['ID'] )
						);
					
						if($operator=='is equal to'){
							if($posttypes == 'Post Type'){
								if($posttype == 'Post'){
									//self::$instance->groupinpost($id,$title,$post_type,$context,$priority,$fieldGroup);
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'post', $context, $priority, array('field_group' => $fieldGroup) );
								}
								if($posttype == 'Page'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'page', $context, $priority, array('field_group' => $fieldGroup) );
								}
								if($posttype == 'Product'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'product', $context, $priority, array('field_group' => $fieldGroup) );
								}
								if($posttype == 'All'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
								}
								else{
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$posttype, $context, $priority, array('field_group' => $fieldGroup) );
								}
							}
							if($posttypes=='Post Template'){ 
								global $pagenow;
								if($posttype=='Default Template'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'page', $context, $priority, array('field_group' => $fieldGroup) ); 
								}
								if($posttype=='templates/template-cover.php'){	
									if(is_page_template('templates/template-cover.php')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) ); 
									}
								}
								if($posttype=='templates/template-full-width.php'){
									if($pagenow=='templates/template-full-width.php'){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  
									}
								}		
							}
							if($posttypes=='Post Status'){
								global $wpdb;
								if($posttype=='publish'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_type in('post','page')");
								
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}
								}
								if($posttype=='future'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='future' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}
								}
								if($posttype=='draft'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='draft' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}
								}
								if($posttype=='pending'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='pending' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	
								}
								if($posttype=='private'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='private' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	
								}
								if($posttype=='trash'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='trash' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		
								}
								if($posttype=='auto-draft'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='auto-draft' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		 
								}
								if($posttype=='inherit'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='inherit' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		 
								}
								if($posttype=='request-confirmed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-confirmed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		   
								}
								if($posttype=='request-failed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-failed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		     
								}
								if($posttype=='request-completed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-completed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids==$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	 
								}
							}
							
							if($posttypes=='Post Category'){
						
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where slug='$posttype'");
								$array = json_decode(json_encode($postid), true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2==$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}
							}
							if($posttypes=='Post Taxonomy'){
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name in('$posttype','post-format-$posttype')");
								$array = json_decode(json_encode($postid), true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2==$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}

							}
							if($posttypes=='Post Format'){
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name='post-format-$posttype'");
								$array=json_decode(json_encode($postid),true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2==$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}
							}	
							if($posttypes=='Post'){ 
								$page=get_posts($posttype);
								$ids=wp_list_pluck( $page, 'ID' );
							
								foreach($ids as $key=>$values){
									$postid=$values;
									if($post->ID==$postid){ 
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}		
							}	
						
							if($posttypes=='Page Template')
							{
								if($posttype=='Default Template'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'page', $context, $priority, array('field_group' => $fieldGroup) ); 	
								}
								if($posttype=='templates/template-cover.php'){
									if(is_page_template('template-cover.php')){ 
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) ); 
									}
								}
								if($posttype=='templates/template-full-width.php'){
									if($pagenow=='template-full-width.php'){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  
									}
								}	
							}
							if($posttypes=='Page Type'){
								if($posttype=='front_page'){
									$frontpage_id = get_option( 'page_on_front' );
									if($frontpage_id==$post->ID){	
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}	
								}
								else if($posttype=='posts_page'){
									$posts_page =  get_option('page_for_posts');
									if($post->ID==$posts_page){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='top_page'){ 
									$parent=$post->post_parent;
									if($parent==0){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									} 
								}
								else if($posttype=='parent_page'){
									$parent=$post->post_parent;
									if($parent==0){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}	
								}
								else if($posttype=='child_page'){
									$parent=$post->post_parent;
									if($parent!=0){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}	
								}
							}	
							
							if($posttypes=='Page Parent'){ 
								$page = get_page_by_title($posttype); 
								$ids = $page->ID;
								if($post->ID==$ids){ 
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
								}
							}

							if($posttypes=='Page'){
								$page = get_page_by_title($posttype); 
								$ids=$page->ID;
								if($post->ID==$ids){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
								}
							}
							
							if($posttypes=='Current User'){
								if($posttype=='logged_in'){
									if(is_user_logged_in()){	
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								
								else if($posttype=='viewing_front'){
									if(!is_admin()){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}
								}
								else if($posttype=='viewing_back'){	
									if(is_admin()){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );	
									}
								}
							}
							if($posttypes=='Current User Role'){
								if($posttype=='Administrator'){
									if(current_user_can('administrator')) {
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Editor'){	
									if(current_user_can('editor')){	
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Author'){
									if( current_user_can('author') ) {
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Contributor'){
									if( current_user_can('contributor')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Subcriber'){
									if( current_user_can('subscriber')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								
							}
						}
						else{
							if($posttypes=='Post Type'){
								if($posttype=='Post'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'page', $context, $priority, array('field_group' => $fieldGroup) );
								}
								else if($posttype=='Page'){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'post', $context, $priority, array('field_group' => $fieldGroup) );
								}
							}
							if($posttypes=='Post Template'){ 
								global $pagenow;
								if($posttype=='Default Template'){
									if(is_page_template('default')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) ); 
									}
								}
								if($posttype=='templates/template-cover.php'){
									if(is_page_template('template-cover.php')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) ); 
									}
								}
								if($posttype=='template-full-width.php'){
									if($pagenow=='templates/template-full-width.php'){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  
									}
								}	
							}
							if($posttypes=='Post Status'){
								global $wpdb;
								if($posttype=='publish'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='publish' AND post_type in('post','page')");
								
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	
								}
								if($posttype=='future'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='future' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}
								}
								if($posttype=='draft'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='draft' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}
								}
								if($posttype=='pending'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='pending' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	
								}
								if($posttype=='private'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='private' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	
								}
								if($posttype=='trash'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='trash' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		
								}
								if($posttype=='auto-draft'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='auto-draft' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		 
								}
								if($posttype=='inherit'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='inherit' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		 
								}
								if($posttype=='request-confirmed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-confirmed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		   
								}
								if($posttype=='request-failed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-failed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}		     
								}
								if($posttype=='request-completed'){
									$postid=$wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_status='request-completed' AND post_type in('post','page')");
									$array = json_decode(json_encode($postid), true);
									foreach($array as $key){
										$ids=$key['ID'];
										if($ids!=$post->ID){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
										}
									}	 
								}
							}
							if($posttypes=='Post Category'){
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where slug='$posttype'");
								$array = json_decode(json_encode($postid), true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2!=$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}
							}
							if($posttypes=='Post Taxonomy'){
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name in('$posttype','post-format-$posttype')");
								$array = json_decode(json_encode($postid), true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2!=$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}
							}

							if($posttypes=='Post Format'){
								global $wpdb;
								$postid=$wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms where name='post-format-$posttype'");
								$array=json_decode(json_encode($postid),true);
								foreach($array as $key){
									$ids=$key['term_id'];
									$ids=$key['term_id'];
									$posttaxid=$wpdb->get_results("SELECT term_taxonomy_id FROM  {$wpdb->prefix}term_taxonomy where term_id=$ids");
									$array1=json_decode(json_encode($posttaxid), true);
									foreach($array1 as $key1){
										$ids1=$key1['term_taxonomy_id'];
										$postobjid=$wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id=$ids1");
										$array2=json_decode(json_encode($postobjid), true);
										foreach($array2 as $key2){
											$ids2=$key2['object_id'];
											if($ids2!=$post->ID){
												add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );  	
											}
										}
									}
								}
							}	
							if($posttypes=='Post'){ 
								$page=get_posts($posttype);
								$ids=wp_list_pluck( $page, 'ID' );
								
								foreach($ids as $key=>$values){
									$postid=$values;
									if($post->ID!=$postid){ 
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}		
							}	

							if($posttypes=='Page Template'){
								if($posttype=='default'){
									$procedures = get_pages(array(
										'meta_key' => '_wp_page_template'));
									foreach($procedures as $procedure){
										$ids=$procedure->ID;
										if($post->ID !=$ids){
											add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
										}
									}
								}
							}
							if($posttypes=='Page Type'){
								if($posttype=='front_page'){
									$frontpage_id = get_option( 'page_on_front' );
									if($post->ID!=$frontpage_id){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}		
								}
								else if($posttype=='posts_page'){
									$posts_page =  get_option('page_for_posts');
									if($post->ID!=$posts_page){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='top_page'){
									$parent=$post->post_parent;
									if($parent!=0){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}
								}
								else if($posttype=='parent'){
									$parent=$post->post_parent;
									if($post->ID!=$parent){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}
								}
								else if($posttype=='child'){
									$parent=$post->post_parent;
									if($parent==0){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}	
								}
							}
						
							if($posttypes=='Page Parent'){
								$ids=$posttype;
								if($post->ID!=$ids){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
								}
							}
							if($posttypes=='Page'){
								$ids=$posttype;
								add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'attachment', $context, $priority, array('field_group' => $fieldGroup) );
								
								if($post->ID!=$ids){
									add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
								}
							}

							if($posttypes=='Current User'){
								if($posttype=='logged_in'){
									if(!is_user_logged_in()){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );	
									}
								}
								
								else if($posttype=='viewing_front'){
									if(is_admin()){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
									}
								}
								else if($posttype=='viewing_back'){								
									if(!is_admin()){
										
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );	
									}
								}
							}

							if($posttypes=='Current User Role'){
								if($posttype=='administrator'){
									if(!current_user_can('administrator')) {
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Editor'){	
									if(!current_user_can('editor')){
										
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Author'){								
									if( !current_user_can('author') ) {
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Contributor'){								
									if( !current_user_can('contributor')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								else if($posttype=='Subcriber'){
									if( !current_user_can('subscriber')){
										add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
									}
								}
								
							}
						}
					}
					if(!in_array($posttypes,$postarray)){
						add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );		
					}
				}
			//if($posttypes!='page_template' && $posttypes!='page_type' && $posttypes!='page_parent' && $posttypes!='page' && $posttypes!='current_user_role' && $posttypes!='current_user' && $posttypes!='user_form' && $posttypes!='user_role' && $posttypes!='taxonomy' && $posttypes!='widget' && $posttypes!='nav_menu' && $posttypes!='nav_menu_item' && $posttypes!='comment'){
				elseif($fieldGroup['type_rule'] == 'basic'){
					if($context=='Normal (after content)'){
						$context='normal';
					}
					
					if( $context == 'side' ) {
						$priority = 'core';
					}
				
					foreach($fieldGroup['location'][0]['postTypes'] as $field_groups_value){
						if($field_groups_value['selected']){
							if($field_groups_value['label'] == 'Post'){
								add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'post', $context, $priority, array('field_group' => $fieldGroup) );
							}
							if($field_groups_value['label'] == 'Page'){
								add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'page', $context, $priority, array('field_group' => $fieldGroup) );
							}
							if($field_groups_value['label'] == 'Product'){
								add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),'product', $context, $priority, array('field_group' => $fieldGroup) );
							}
							if($field_groups_value['label'] == 'All'){
                                add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
                            }
							else{
								add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$field_groups_value['label'], $context, $priority, array('field_group' => $fieldGroup) );
							}
						}
					}
				}			
	    	}
		}
	}

	public function current_screen() {
		self::$instance->add_meta_boxes;
		global $wp_post_statuses;
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'smack-custom-fields' );
	}

	public static function groupinpost($id,$title,$post_type,$context,$priority,$fieldGroup){
		add_meta_box( $id, $title, array(self::$instance, 'renderMetaBox'),$post_type, $context, $priority, array('field_group' => $fieldGroup) );
	}

	public static function renderMetaBox( $post, $metabox ) {
		$id = $metabox['id'];
		$field_group = $metabox['args']['field_group'];
		$fields = self::$ultimatefield->getFields( $field_group );	
		self::$instance->renderFields( $fields, $post->ID, 'div', 'label');
	}
	
	public static function renderFields( $fields, $post_id = 0, $el = 'div', $instruction = 'label', $screen = null ) {
	
		if( is_array($post_id) ) {
			$args = func_get_args();
			$fields = $args[1];
			$post_id = $args[0];
		}
		$fields = apply_filters( 'smack/pre_render_fields', $fields, $post_id );
	
		if( $fields ) {
			global $wpdb;
			//if (isset($uploadZoneData[1]['size']) != 0) {
			$group_div = "tools-engine-add-group-fields-" . $fields[0]['parent'];
			$group_id = $fields[0]['parent'];
			$group_name = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $group_id AND post_type = 'tools-engine' ");
			$group_postname = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE ID = $group_id AND post_type = 'tools-engine' ");
			?><div 
				id="<?php echo esc_attr($group_div) ?>" 
				data-id="<?php echo esc_attr($group_id) ?>"
				data-postname="<?php echo esc_attr($group_postname) ?>"
				data-name="<?php echo esc_attr($group_name) ?>" >
			</div>
			<?php
			foreach( $fields as $field ) { 
				self::$instance->renderFieldWrap( $field, $el, $instruction, $post_id, $screen );
			}
		}
		do_action( 'smack/render_fields', $fields, $post_id );
	//}
}

public static function renderFieldWrap( $field, $element = 'div', $instruction = 'label' , $postID, $screen) {
	
		?><div id="wp-ultimate-test-div"></div><?php
		$field = self::$ultimatefield->validateField( $field );
		$field = self::$instance->prepareField( $field );
		if( !$field ) {
			return;
		}
	
		$elements = array(
			'div'	=> 'div',
			'tr'	=> 'td',
			'td'	=> 'div',
			'ul'	=> 'li',
			'ol'	=> 'li',
			'dl'	=> 'dt',
		);
		
		if( isset($elements[$element]) ) {
			$inner_element = $elements[$element];
		} else {
			$element = $inner_element = 'div';
		}
			
		// Generate wrapper attributes.
		$wrapper = array(
			'id'		=> '',
			'class'		=> 'smack-field',
			'width'		=> '',
			'style'		=> '',
			'data-name'	=> $field['_name'],
			'data-type'	=> $field['type'],
			'data-key'	=> $field['key'],
		);
		
		$wrapper['class'] .= " smack-field-{$field['type']}";
		
		if( $field['key'] ) {
			$wrapper['class'] .= " smack-field-{$field['key']}";
		}
		
		if( $field['required'] ) {
			$wrapper['class'] .= ' is-required';
			$wrapper['data-required'] = 1;
		}
		
		$wrapper['class'] = str_replace( '_', '-', $wrapper['class'] );
		$wrapper['class'] = str_replace( 'field-field-', 'field-', $wrapper['class'] );
		
		// if( $field['wrapper'] ) {
		// 	$wrapper = self::$instance->smack_merge_attributes( $wrapper, $field['wrapper'] );
		// }
		
		$width = self::$groupInst->extractVar( $wrapper, 'width' );
		$wrapper = array_map( 'trim', $wrapper );
		$wrapper = array_filter( $wrapper );
		
		global $post;
		$attributes_html = self::$instance->escAttrs( $wrapper );

		$current_screen = get_current_screen();
	
		//if($current_screen->id == 'post'){
		if($current_screen->base == 'post'){
			$page_type = 'post';
		}
		elseif($current_screen->base == 'edit-tags'){
			$page_type = 'taxonomy';
		}
		elseif($current_screen->base == 'term'){
			$page_type = 'taxonomy-edit';
		}
		// elseif($current_screen->id == 'product'){
		// 	$page_type = 'product';
		// }
		elseif($current_screen->id == 'user' || $current_screen->id == 'user-edit' || $current_screen->id == 'profile'){
			$page_type = 'user';
		}
	
		if($page_type == 'post'){
			echo "<$element $attributes_html>" . "\n";
			if( $element !== 'td' ) {
				/*echo "<$inner_element class=\"smack-label\">" . "\n";
				self::$instance->renderFieldLabel( $field );
					
				echo "</$inner_element>" . "\n";*/
			}
			echo "<$inner_element class=\"smack-input\">" . "\n";
		}

		$smack_field_label = $field['label'];
		$smack_field_instructions = $field['instructions'];
		$smack_field_name = 'wp-smack-'. $field['_name'];

		$is_required = ($field['required']) ? 'true' : 'false';
		$check_for_required = get_option('wp_smack_required_fields_missing');

		if(!empty($check_for_required)){
			if(in_array($smack_field_name, $check_for_required)){
				$required_message = 'true';
			}else{
				$required_message = 'false';
			}
		}
		if($page_type == 'user'){
			global $user_id;
			if ($field['type'] == 'relationship'){
				$smack_field_value[0][] = get_user_meta( $user_id, $smack_field_name."_postType", false );
				$smack_field_value[0][] = get_user_meta( $user_id, $smack_field_name."_taxonomy", false );
				$smack_field_value[0][] = get_user_meta( $user_id, $smack_field_name, false );
			}
			else {
				$smack_field_value = get_user_meta( $user_id, $smack_field_name, true );
			}
		}
		elseif($screen == 'taxonomy'){
			$get_term_id = explode('term_', $postID);
			if ($field['type'] == 'relationship'){
				$smack_field_value[0][] = get_term_meta( $get_term_id[1], $smack_field_name."_postType", false );
				$smack_field_value[0][] = get_term_meta( $get_term_id[1], $smack_field_name."_taxonomy", false );
				$smack_field_value[0][] = get_term_meta( $get_term_id[1], $smack_field_name, false );
			}
			else {
				$smack_field_value = get_term_meta( $get_term_id[1], $smack_field_name, true );
			}
			if($smack_field_value == false){
				$smack_field_value= "";
			}
		}
		else{
			if ($field['type'] == 'relationship'){
				$smack_field_value[0][] = get_post_meta( get_the_ID(), $smack_field_name."_postType", false );
				$smack_field_value[0][] = get_post_meta( get_the_ID(), $smack_field_name."_taxonomy", false );
				$smack_field_value[0][] = get_post_meta( get_the_ID(), $smack_field_name, false );
			}
			else {
				$smack_field_value = get_post_meta( get_the_ID(), $smack_field_name, true );
				echo "</$inner_element>" . "\n";
				echo "</$element>" . "\n";
			}
		}		

		self::$smackHelperInst->render_sub_fields($screen, $current_screen, $smack_field_name, $smack_field_label, $smack_field_value, $postID, $field, $page_type, 'via_post');
		// echo "</$inner_element>" . "\n";
		// echo "</$element>" . "\n";

	}

	public static function save_field_data( $post_id, $post ) {
		

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		// return if tools engine publish / update is clicked
		if(isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'saveFields'){
			return;
		}
	
		if ( $parent_id = wp_is_post_revision( $post_id ) ) {
			$post_id = $parent_id;
		}
	
		if(empty($post_id)){
			$post_id = intval($_POST['ID']);
		}


		if ($post->post_type === 'post'  && !empty($_POST)) {
			// $valid = false;

			global $wpdb;
			$postContent = $wpdb->get_results("SELECT post_content , post_name FROM {$wpdb->prefix}posts WHERE (post_type = 'smack-field') AND post_title <> 'Auto Draft'");

			$content_array = array();
			foreach ($postContent as $post){
				$content_array = unserialize($post->post_content);
				if($content_array['required'] === true){
					$name_array[] = $post->post_name;
				}
			}

			$array_key_meta = array_keys($_POST);
			foreach ($array_key_meta as $array_key){
				foreach($name_array as $name){
					if (strstr($array_key,$name) != null){
						$metaKey = $array_key;
						$meta_value = $_POST[$metaKey];
						if (empty($meta_value)){
							$empty_values_array[] = $metaKey;
						}
					}
				}
			}
			if (!empty($empty_values_array)){
				$GLOBALS['valid'] = true;
				add_filter( 'post_updated_messages', array(self::$instance,'save_field_data'));
			}
			else{
				$GLOBALS['valid'] = false;
			}
			
		}
		$GLOBALS['valid'] =isset($GLOBALS['valid'])?$GLOBALS['valid']:'';
		if ($GLOBALS['valid']){

			remove_action('save_post', array(self::$instance,'save_field_data'));

			wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));

			// re-hook this function
			add_action('save_post', array(self::$instance,'save_field_data'));
		}
		self::$instance->saveFieldsMeta($post_id, 'post');
		// self::$instamce->checkRequiredFields();

		if(self::$instance->checkRequiredFields($post_id, 'post')){
			return;
		}
	}

	public function checkRequiredFields(){
		if(!empty($_POST)) {
			$get_required_fields = get_option('wp_smack_required_fields');
		
			for($i =0; $i < sizeof($get_required_fields); $i++) {
				
				// ($_POST[$get_required_fields[$i]] == "") ? wp_die($message, 'Error - Missing Rquired Field!') : "";
				if($_POST[$get_required_fields[$i]] === "") {
					echo "<script type='text/javascript'>alert('Missing Rquired Field');</script>";
				}
			}
		}
		// $smack_required_fields_missing = [];
		// foreach($get_required_fields as $required_fields){
		// 	if(empty($_POST[$required_fields])){
		// 		array_push($smack_required_fields_missing, $required_fields);
		// 		$has_error = 'true';
		// 	}
		// }
		// update_option('wp_smack_required_fields_missing', $smack_required_fields_missing);
		// if($has_error == 'true'){
		// 	return true;
		// }
		// else{
		// 	return false;
		// }
	}

	public function saveFieldsMeta($post_id, $type){
		$get_array_keys = array_keys($_POST);
		foreach($get_array_keys as $all_post_values){
			if (strpos($all_post_values, 'wp-smack-') !== false) {

				if(!is_array($_POST[$all_post_values])){

					if ((strpos($_POST[$all_post_values], '[\"') !== false) || (strpos($_POST[$all_post_values], '[{\"url\":\"') !== false)) {
						$result_array =  str_replace("\\" , '' , $_POST[$all_post_values]);
						$result_array = json_decode($result_array, True);

						// self::$instance->updateMetas($post_id, $all_post_values, $result_array, $type);
						$meta_key = self::$instance->getGroupMetaKey($all_post_values);
						self::$instance->updateMetas($post_id, $meta_key, $result_array, $type);
					}
					elseif(strpos($_POST[$all_post_values], '[{\"label\":\"') !== false || strpos($_POST[$all_post_values], '{\"label\":\"') !== false) {
						$result_array =  str_replace("\\" , '' , $_POST[$all_post_values]);
						$result_array = json_decode($result_array, True);

						if (strpos($all_post_values,"_postType") !== false || strpos($all_post_values,"_taxonomy") !== false){
							$get_ids = $result_array;
						}
						else{
							if ($result_array[0]){
								if($result_array[0]['value']){
									$get_ids = array_column($result_array, 'value');
								}
								elseif($result_array[0]['id']){
									$get_ids = array_column($result_array, 'id');
								}
							}
							else{
								$get_ids = $result_array;
							}
						}

						// self::$instance->updateMetas($post_id, $all_post_values, $get_ids, $type);
						$meta_key = self::$instance->getGroupMetaKey($all_post_values);
						self::$instance->updateMetas($post_id, $meta_key, $get_ids, $type);
					}
					else{
						if($_POST[$all_post_values] != strip_tags($_POST[$all_post_values])){
							//self::$instance->updateMetas($post_id, $all_post_values, $_POST[$all_post_values], $type);
							$meta_key = self::$instance->getGroupMetaKey($all_post_values);
							self::$instance->updateMetas($post_id, $meta_key, $_POST[$all_post_values], $type);
						}
						else{
							//self::$instance->updateMetas($post_id, $all_post_values, sanitize_text_field( $_POST[$all_post_values] ), $type );
							// self::$instance->updateMetas($post_id, $all_post_values, $_POST[$all_post_values] , $type );
							$meta_key = self::$instance->getGroupMetaKey($all_post_values);
							self::$instance->updateMetas($post_id, $meta_key, $_POST[$all_post_values] , $type );
						}
					}
				}
				else{ 
					$meta_key = self::$instance->getGroupMetaKey($all_post_values);
					self::$instance->updateMetas($post_id, $meta_key, $_POST[$all_post_values], $type);
					//self::$instance->updateMetas($post_id, $all_post_values, $_POST[$all_post_values], $type);
				}
			}
		}
	}

	public function getGroupMetaKey($all_post_values){
		if(strpos($all_post_values, 'wp-smack-groupField--') !== false){
			$get_group_names = explode('--', $all_post_values);
			$all_post_values = "wp-smack-". $get_group_names[1] . "_" .  $get_group_names[2];
		}
		elseif(strpos($all_post_values, 'wp-smack-repeaterField--') !== false){
			$get_repeater_names = explode('--', $all_post_values);
			$repeater_name = explode('__', $get_repeater_names[2]);
			$all_post_values = "wp-smack-". $get_repeater_names[1] . "_" .  $repeater_name[1] . "_" . $repeater_name[0];
		}
		elseif(strpos($all_post_values, 'wp-smack-cloneField--') !== false){
			$get_clone_names = explode('--', $all_post_values);
			$all_post_values = "wp-smack-". $get_clone_names[1]."_". $get_clone_names[2];		
		}
		return $all_post_values;
	}

	public function updateMetas($post_id, $meta_key, $meta_value, $type){
		if($type == 'post'){

			update_post_meta($post_id, $meta_key, $meta_value);
		}
		elseif($type == 'user'){
			update_user_meta($post_id, $meta_key, $meta_value);
		}
		elseif($type == 'taxo'){
			update_term_meta($post_id, $meta_key, $meta_value);
		}
	}

	public static function escAttrs( $attrs ) {
		$html = '';
		
		// Loop over attrs and validate data types.
		foreach( $attrs as $k => $v ) {
			
			// String (but don't trim value).
			if( is_string($v) && ($k !== 'value') ) {
				$v = trim($v);
				
			// Boolean	
			} elseif( is_bool($v) ) {
				$v = $v ? 1 : 0;
				
			// Object
			} elseif( is_array($v) || is_object($v) ) {
				$v = json_encode($v);
			}
			
			// Generate HTML.
			$html .= sprintf( ' %s="%s"', esc_attr($k), esc_attr($v) );
		}
		
		// Return trimmed.
		return trim( $html );
	}

	public static function renderFieldLabel( $field ) {
		
		// Get label.
		$label = self::$instance->getFieldLabel( $field );
		
		//return $label;
		if( $label ) {
			echo '<label style = "font-weight:bold"' . ($field['id'] ? ' for="' . esc_attr($field['id']) . '"' : '' ) . '>' . ($label) . '</label>';
		}
	}

	public static function getFieldLabel( $field, $context = '' ) {
		
		// Get label.
		$label = $field['label'];
		
		if( $context == 'admin' && $label === '' ) {
			$label = __('(no label)', 'smack');
		}
		
		if( $field['required'] ) {
			$label .= ' <span class="smack-required" style="color:red;font-weight:bold">*</span>';
			
		}
		$label = apply_filters( "smack/get_field_label", $label, $field, $context );
		
		// Return label.
		return $label;
	}

	public static function smack_merge_attributes( $array1, $array2 ) {
		
		// Merge together attributes.
		$array3 = array_merge( $array1, $array2 );
		
		// Append together special attributes.
		foreach( array('class', 'style') as $key ) {
			if( isset($array1[$key]) && isset($array2[$key]) ) {
				$array3[$key] = trim($array1[$key]) . ' ' . trim($array2[$key]);
			}
		}
		
		// Return.
		return $array3;
	}

	public static function prepareField( $field ) {
		
		if( !empty($field['_prepare']) ) {
			return $field;
		}
		
		if( $field['key'] ) {
			$field['name'] = $field['key'];
		}

		if( $field['prefix'] ) {
			$field['name'] = "{$field['prefix']}[{$field['name']}]";
		}
		
		$field['id'] =self::$instance-> smack_idify( $field['name'] );
		$field['_prepare'] = true;	
		$field = apply_filters( "smack/prepare_field", $field );
		
		// return
		return $field;
	}

    public static function smack_idify( $str = '' ) {
		return str_replace(array('][', '[', ']'), array('-', '-', ''), strtolower($str));
    }

	public static function getFieldGroupEditLink( $post_id ) {
		if( $post_id ) {
			return admin_url('post.php?post=' . $post_id . '&action=edit');
		}
		return '';
	}

	public function smack_posttype_based_filter(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		$get_post_type = sanitize_text_field($_POST['post_type']);
		$get_taxo_type = sanitize_text_field($_POST['taxo_type']);
	
		// $category_query_args = array(
		// 	'post_type' => 'post',
		// 	'cat' => 2
		// );
		// $category_query = new \WP_Query( $category_query_args );
	
		if($get_post_type == 'attachment'){
			$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$get_post_type' AND post_status = 'inherit' ", ARRAY_A);
		}
		else{
			$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$get_post_type' AND post_status = 'publish' ", ARRAY_A);
		}

		$allpost_titles = array_column($all_post_titles, 'post_title');
		$allpost_ids = array_column($all_post_titles, 'ID');

		$all_post_title_ids = array_combine($allpost_ids, $allpost_titles);

		if(empty($get_taxo_type) || $get_taxo_type == 'undefined'){
			$all_post_title_label_value = [];
			$temp = 0;
			foreach($allpost_titles as $all_post_title_value){
				$post_title_value = $allpost_ids[$temp];
				$all_post_title_label_value[$temp]['label'] = $all_post_title_value;
				$all_post_title_label_value[$temp]['value'] = $post_title_value;
				$temp++;
			}
			$response['post_fields'] = $all_post_title_label_value;
			
		}
		else{
			$get_taxoid = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy WHERE term_id = $get_taxo_type ");
			$get_taxo_posts = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = $get_taxoid ", ARRAY_A);
			if(!empty($get_taxo_posts)){
				$get_taxo_post_ids = array_column($get_taxo_posts, 'object_id');

				$get_posts_taxos = array_intersect($allpost_ids, $get_taxo_post_ids);
				
				$all_post_taxos_label_value = [];
				$temp = 0;
				foreach($get_posts_taxos as $get_posts_taxos_ids){
					$all_post_taxos_label_value[$temp]['label'] = $all_post_title_ids[$get_posts_taxos_ids];
					$all_post_taxos_label_value[$temp]['value'] = $get_posts_taxos_ids;
					$temp++;
				}

				$response['post_fields'] = $all_post_taxos_label_value;
			}
			else{
				$response['post_fields'] = [];
			}
		}

		echo wp_json_encode($response);
		wp_die();
	}

	public function smack_taxo_based_filter(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		global $wpdb;
		$get_post_type = sanitize_text_field($_POST['post_type']);
		$get_taxo_type = sanitize_text_field($_POST['taxo_type']);

		if(empty($get_post_type) || $get_post_type == 'undefined'){
			$get_taxoid = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy WHERE term_id = $get_taxo_type ");
			$get_taxo_posts = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = $get_taxoid ", ARRAY_A);
		
			if(!empty($get_taxo_posts)){
				$get_taxo_post_ids = array_column($get_taxo_posts, 'object_id');

				$all_post_taxos_label_value = [];
				
				$post_type_title = [];
				$post_title_ids = [];
				foreach($get_taxo_post_ids as $get_taxo_post_id){
					$get_taxo_posttype = $wpdb->get_var("SELECT post_type FROM {$wpdb->prefix}posts WHERE ID = $get_taxo_post_id");
					$get_taxo_posttitle = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $get_taxo_post_id");

					$post_type_title[$get_taxo_posttitle] = $get_taxo_posttype;
					$post_title_ids[$get_taxo_post_id] = $get_taxo_posttitle;
				}

				$final_array = [];
				$temp = 0;
				$get_postType = array_unique($post_type_title);
				foreach($get_postType as $postType){
					
					$get_posts_basedon_types = array_keys($post_type_title, $postType);
					$posts_basedon_types = [];
					$temps = 0;
					foreach($get_posts_basedon_types as $per_posts){
						$per_posts_ids = array_search($per_posts, $post_title_ids);
						$posts_basedon_types[$temps]['label'] = $per_posts;
						$posts_basedon_types[$temps]['value'] = $per_posts_ids;
						$temps++;
					}

					$final_array[$temp]['label'] = $postType;
					$final_array[$temp]['options'] = $posts_basedon_types;
					$temp++;
				}
				
				$response['taxo_fields'] = $final_array;
			}
			else{
				$response['taxo_fields'] = [];
			}
		}
		else{
			if($get_post_type == 'attachment'){
				$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$get_post_type' AND post_status = 'inherit' ", ARRAY_A);
			}
			else{
				$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$get_post_type' AND post_status = 'publish' ", ARRAY_A);
			}
			$allpost_titles = array_column($all_post_titles, 'post_title');
			$allpost_ids = array_column($all_post_titles, 'ID');

			$all_post_title_ids = array_combine($allpost_ids, $allpost_titles);

			$get_taxoid = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy WHERE term_id = $get_taxo_type ");
			$get_taxo_posts = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = $get_taxoid ", ARRAY_A);
			if(!empty($get_taxo_posts)){
				$get_taxo_post_ids = array_column($get_taxo_posts, 'object_id');

				$get_posts_taxos = array_intersect($allpost_ids, $get_taxo_post_ids);
				
				$all_post_taxos_label_value = [];
				$temp = 0;
				foreach($get_posts_taxos as $get_posts_taxos_ids){
					$all_post_taxos_label_value[$temp]['label'] = $all_post_title_ids[$get_posts_taxos_ids];
					$all_post_taxos_label_value[$temp]['value'] = $get_posts_taxos_ids;
					$temp++;
				}

				$response['taxo_fields'] = $all_post_taxos_label_value;
			}
			else{
				$response['taxo_fields'] = [];
			}
		}

		echo wp_json_encode($response);
		wp_die();
	}
}