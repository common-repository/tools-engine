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
class Taxonomy{
	var $view = 'add';
    protected static $instance = null,$plugin,$helperInst,$groupInst,$postInst,$fieldInst;

	
	public function __construct()
	{

	}
	
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$groupInst = FieldGroup::getInstance();
			self::$fieldInst=UltimateFields::getInstance();
			self::$helperInst = UltimateHelper::getInstance();
			self::$postInst=PostView::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}
	
    public static function doHooks(){ 
        add_action( 'admin_enqueue_scripts',array( self::$instance, 'adminEnqueueScripts' ) );
		add_action('create_term',			array(self::$instance, 'save_term'), 10, 3);
		add_action('edit_term',				array(self::$instance, 'save_term'), 10, 3);
    }

    public static function validatePage() {
		global $pagenow;
		// validate page
		if( $pagenow === 'edit-tags.php' || $pagenow === 'term.php' ) {	
			return true;
		}
	
		return false;		
	}

	public static function adminEnqueueScripts() {
		// validate page
		if( !self::$instance->validatePage() ) {	
			return;
		}
		
		// vars
		$screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
		
		add_action("{$taxonomy}_add_form_fields", 	array(self::$instance, 'add_term'), 10, 1);

		add_action("{$taxonomy}_edit_form", array(self::$instance, 'editTerm'), 10, 2);	
	}

	public function save_term($term_id, $tt_id, $taxonomy){
		self::$postInst->saveFieldsMeta($term_id, 'taxo');
    }

	public static function add_term($term = null, $taxonomy = null){
		$post_id = self::$instance->getTermPostId($taxonomy, 0 );
		self::$instance->view = 'edit';
		$screen = get_current_screen();
		$screen_id = $screen->taxonomy;
	
		//$this->view = 'add';
	
		$fieldGroups = self::$groupInst->getFieldGroupsView(array(
			'taxonomy' => $taxonomy
		));
	
		if( !empty($fieldGroups) ) { 	
			foreach( $fieldGroups as $fieldGroup ) {
			
				// skip current loop if group is inactive
				if(!$fieldGroup['active']){
					continue;
				}

				if($fieldGroup['type_rule'] == 'advanced'){
					foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

						$posttypes = $fieldGroupValues[0]['param'];
						$operator = $fieldGroupValues[1]['operator'];
						$posttype = $fieldGroupValues[2]['value']; 

						if($operator=='is equal to'){
							if($posttypes=='Taxonomy'){
								if( $taxonomy=='post_tag' ) {
									if($posttype=='post_tag'  ){
										echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
										echo '<table>';
										$fields =self::$fieldInst->getFields( $fieldGroup );
										echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
										echo '</table>';
									}
									elseif($posttype == 'category'){
										echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
										echo '<table>';
										$fields =self::$fieldInst->getFields( $fieldGroup );
										echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
										echo '</table>';
									}
								}
								else if ($taxonomy=='category'){
									if($posttype=='category'  ){
										self::$instance->display_category($fieldGroup, $post_id);
									}
								}
								else{
									echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
									// echo '<table class="form-table">';
									echo '<div>';
									$fields =self::$fieldInst-> getFields( $fieldGroup );
									echo self::$postInst->renderFields( $fields, $post_id, 'div', 'field', 'taxonomy' );
									echo '</div>';
									//	echo '</table>';
								}	 
							}
							elseif($posttypes == 'Post Category'){
								global $wpdb;
								$current_term_id = $term->term_id;     
								$postid = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$posttype'");
							
								if($postid == $current_term_id){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}
						}
					}
				}
				elseif($fieldGroup['type_rule'] == 'basic'){
					
					foreach($fieldGroup['location'][2]['taxonomies'] as $field_groups_value){
						if($field_groups_value['selected']){
							if( $taxonomy == 'post_tag' ) {
								if($field_groups_value['label'] == 'post_tag'  ){
									echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
									echo '<table>';
									$fields =self::$fieldInst->getFields( $fieldGroup );
									echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
									echo '</table>';
								}
								elseif($field_groups_value['label'] == 'category'){
									echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
									echo '<table>';
									$fields =self::$fieldInst->getFields( $fieldGroup );
									echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
									echo '</table>';
								}
							}
							else if ($taxonomy == 'category'){
								if($field_groups_value['label'] == 'category'  ){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}
							else{
								if($screen_id == $field_groups_value['label']){
									echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
									// echo '<table class="form-table">';
									echo '<div>';
									$fields =self::$fieldInst->getFields( $fieldGroup );
									echo self::$postInst->renderFields( $fields, $post_id, 'div', 'field', 'taxonomy' );
									echo '</div>';
									//	echo '</table>';
								}
							}	 
						}
					}
				}			
			}
		}
	}

	public static function editTerm( $term, $taxonomy ) {
		global $wpdb;
		$post_id = self::$instance->getTermPostId( $term->taxonomy, $term->term_id );
		self::$instance->view = 'edit';
		$screen = get_current_screen();
		$screen_id = $screen->taxonomy;
		
		// get field groups
		$fieldGroups = self::$groupInst->getFieldGroupsView(array(
			'taxonomy' => $taxonomy
		));
	
		if( !empty($fieldGroups) ) { 	
			foreach( $fieldGroups as $fieldGroup ) {
				if($fieldGroup['type_rule'] == 'basic'){
					foreach($fieldGroup['location'][2]['taxonomies'] as $field_groups_value){
						if($field_groups_value['selected']){
							if( $taxonomy == 'post_tag' ) {
								if($field_groups_value['label'] == 'post_tag'  ){
									echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
									echo '<table>';
									$fields =self::$fieldInst-> getFields( $fieldGroup );
									echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
									echo '</table>';
								}
							}
							else if ($taxonomy == 'category'){
								if($field_groups_value['label'] == 'category'  ){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}
							else{
								if($screen_id == $field_groups_value['label']){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}	 
						}
					}
				}
				elseif($fieldGroup['type_rule'] == 'advanced'){
					foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){
						$posttypes = $fieldGroupValues[0]['param'];
						$operator = $fieldGroupValues[1]['operator'];
						$posttype = $fieldGroupValues[2]['value']; 

						if($operator=='is equal to'){
							if($posttypes=='Taxonomy'){
								if( $taxonomy=='post_tag' ) {
									if($posttype=='post_tag'  ){
										echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
										echo '<table>';
										$fields =self::$fieldInst-> getFields( $fieldGroup );
										echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
										echo '</table>';
									}
								}
								else if ($taxonomy=='category'){
									if($posttype=='category'  ){
										self::$instance->display_category($fieldGroup, $post_id);
									}
								}
								else{
									self::$instance->display_category($fieldGroup, $post_id);
								}	 
							}
							elseif($posttypes == 'Post Category'){
								$current_term_id = $term->term_id;     
								$postid = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$posttype'");
							
								if($postid == $current_term_id){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}
							elseif($posttypes == 'Post Taxonomy'){
								$current_term_id = $term->term_id;  

								if($posttype == $current_term_id){
									self::$instance->display_category($fieldGroup, $post_id);
								}
							}
						}
						else{
							if($posttypes=='Taxonomy'){
								if( $taxonomy!='post_tag' ) {
									if($posttype=='tag'  ){
										echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
										echo '<table>';
										$fields =self::$fieldInst-> getFields( $fieldGroup );
										echo self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
										echo '</table>';
									}
								}
								else if ($taxonomy!='category'){
									if($posttype=='category'  ){
										echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
										echo '<table class="form-table">';
										$fields =self::$fieldInst-> getFields( $fieldGroup );
										self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
										echo '</table>';
									}	
								}
							}
						}	
					}
				}
			}
		}
	}

	public function display_category($fieldGroup, $post_id){
		echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
		echo '<table class="form-table">';
		$fields = self::$fieldInst->getFields( $fieldGroup );
		self::$postInst->renderFields( $fields, $post_id, 'tr', 'field', 'taxonomy' );
		echo '</table>';
	}
		
	public static function getTermPostId( $taxonomy, $term_id ) {   
		if( !self::$instance->issetTermmeta() ) {
			return $taxonomy . '_' . $term_id;
		}
		// return
		return 'term_' . $term_id;
	}

	public static function issetTermmeta( $taxonomy = '' ) {
		if( $taxonomy && !taxonomy_exists($taxonomy) ) return false;
			// return
			return true;	
	}
			
}
?>