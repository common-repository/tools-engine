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
class SmackFieldtype{
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
            self::$helperInst = UltimateHelper::getInstance();
            self::$fieldInst=UltimateFields::getInstance();
			self::$postInst=PostView::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
    }
    
    public static function doHooks(){ 
        add_action('deleted_post',array(self::$instance, 'deletePost'), 10, 2);
        add_action('trashed_post',array(self::$instance, 'trashPost'));
        add_action('untrashed_post',array(self::$instance, 'untrashPost'));
        add_action('wp_ajax_getPostTaxonomies', array(self::$instance,'getPostTaxonomies'));
    }

    public function deletePost( $post_id ) {
		if( get_post_type($post_id) != 'tools-engine' ) {
			return;
		}
    
        global $wpdb;
        $check_for_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $post_id AND post_type = 'smack-field' ", ARRAY_A);
        if(!empty($check_for_fields)){
            foreach($check_for_fields as $fields){
                wp_delete_post($fields['ID']);
            }
        }

        // check for groups with status - Auto Draft
        $check_draft_groups = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine' AND post_status = 'auto-draft' ", ARRAY_A);
        if(!empty($check_draft_groups)){
            foreach($check_draft_groups as $drafts){
                wp_delete_post($drafts['ID']);
            }
        }
    }
    
    public function trashPost($post_id){
        if( get_post_type($post_id) != 'tools-engine' ) {
			return;
        }

        global $wpdb;
        $check_for_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $post_id AND post_type = 'smack-field' ", ARRAY_A);
        if(!empty($check_for_fields)){
            foreach($check_for_fields as $fields){
                wp_trash_post($fields['ID']);
            }
        }
    }

    public function untrashPost($post_id){
        if( get_post_type($post_id) != 'tools-engine' ) {
			return;
        }

        global $wpdb;
        $check_for_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $post_id AND post_type = 'smack-field' ", ARRAY_A);
        if(!empty($check_for_fields)){
            foreach($check_for_fields as $fields){
                wp_untrash_post($fields['ID']);
            }
        }
    }

    public function getPostTaxonomies(){
        check_ajax_referer('smack-tools-engine-key', 'securekey');
        $taxo = get_taxonomies();
        //Removed post_format option under taxonomy filter type ( from taxonomy property)
        $unset_tax = array('nav_menu', 'link_category','wp_theme','post_format');
        $temp = 0;
        $get_related_terms = [];
        foreach($taxo as $tax){ 
            if(!in_array($tax, $unset_tax)){
                $get_related_terms[$temp]['label'] = $tax;
                $get_related_terms[$temp]['value'] = $tax;
                $temp++;
            }
        }
        
        $taxo_arr[0]['label'] = 'taxonomies';
        $taxo_arr[0]['options'] = $get_related_terms;
            
        echo wp_json_encode($taxo_arr);
        wp_die();
    }

}