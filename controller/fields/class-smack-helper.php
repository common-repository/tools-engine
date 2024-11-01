<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class SmackFieldHelper
{
	protected static $instance = null;
	protected static $smackTextInst = null, $smackTextareaInst, $smackNumberInst, $smackRangeInst, $smackUrlInst, $smackPasswordInst, $smackEmailInst, 
									$smackImageInst, $smackGalleryInst, $smackFileInst, $smackSiwygInst, $smackEmbedInst,
									$smackSelectInst, $smackCheckboxInst, $smackRadioButtonInst, $smackButtonGroupInst, $smackTrueFalseInst, 
									$smackLinkInst, $smackPostObjectInst, $smackPageLinkInst, $smackRelationshipInst, $smackTaxonomyInst, $smackUserInst, 
									$smackDateInst, $smackDateTimeInst, $smackTimeInst, $smackColorInst, 
									$smackMessageInst, $smackGroupInst, $smackRepeaterInst,$smackGoogleMapInst,$smackCloneInst;
	
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$smackTextInst = SmackFieldText::getInstance();
			self::$smackTextareaInst = SmackFieldTextarea::getInstance();
			self::$smackNumberInst = SmackFieldNumber::getInstance();
			self::$smackRangeInst = SmackFieldRange::getInstance();
			self::$smackUrlInst = SmackFieldUrl::getInstance();
			self::$smackPasswordInst = SmackFieldPassword::getInstance();
			self::$smackEmailInst = SmackFieldEmail::getInstance();
			self::$smackImageInst = SmackFieldImage::getInstance();
			self::$smackGalleryInst = SmackFieldGallery::getInstance();
			self::$smackFileInst = SmackFieldFile::getInstance();
			self::$smackSiwygInst = SmackFieldSiwyg::getInstance();
			self::$smackEmbedInst = SmackFieldEmbed::getInstance();
			self::$smackSelectInst = SmackFieldSelect::getInstance();
			self::$smackCheckboxInst = SmackFieldCheckbox::getInstance();
			self::$smackRadioButtonInst = SmackFieldRadioButton::getInstance();
			self::$smackButtonGroupInst = SmackFieldButtonGroup::getInstance();
			self::$smackTrueFalseInst = SmackFieldTrueFalse::getInstance();
			self::$smackLinkInst = SmackFieldLink::getInstance();
			self::$smackPostObjectInst = SmackFieldPostObject::getInstance();
			self::$smackPageLinkInst = SmackFieldPageLink::getInstance();
			self::$smackRelationshipInst = SmackFieldRelationship::getInstance();
			self::$smackTaxonomyInst = SmackFieldTaxonomy::getInstance();
			self::$smackUserInst = SmackFieldUser::getInstance();
			self::$smackDateInst = SmackFieldDate::getInstance();
			self::$smackDateTimeInst = SmackFieldDateTime::getInstance();
			self::$smackTimeInst = SmackFieldTime::getInstance();
			self::$smackColorInst = SmackFieldColor::getInstance();
			self::$smackMessageInst = SmackFieldMessage::getInstance();
			self::$smackGroupInst = SmackFieldGroup::getInstance();
			self::$smackRepeaterInst = SmackFieldRepeater::getInstance();
			self::$smackGoogleMapInst = SmackFieldGoogleMap::getInstance();
			self::$smackCloneInst = SmackFieldClone::getInstance();
		}
		return self::$instance;
	}

	public function get_all_post_titles($type, $post_type_filter = null, $taxonomy_type_filter = null){
		global $wpdb;
        $all_post_titles = [];
        $final_array = [];
		$temps = 0;
		
		$other_taxo = array('nav_menu','action-group','product_type','product_visibility','product_shipping_class','wpsc-variation','wpsc_log_type','post_format','link_category', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'smack-field', 'tools-engine');
		$get_all_post_types = get_post_types();	

		if(!empty($taxonomy_type_filter)){
			$filter_taxo_result = self::$instance->filter_by_taxonomy($post_type_filter, $taxonomy_type_filter);
			return $filter_taxo_result;
		}

		if(!empty($post_type_filter) && empty($taxonomy_type_filter)){
			$get_all_post_types = array_column($post_type_filter, 'label');
		}

		foreach($get_all_post_types as $post_types){
			$post_types = strtolower($post_types);
			if(!in_array($post_types, $other_taxo)){
				if($post_types == 'attachment'){
					$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$post_types' AND post_status = 'inherit' ", ARRAY_A);
				}
				else if($post_types == 'all'){
					$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE (post_type = 'post' OR post_type = 'page' OR post_type = 'product') AND post_status = 'publish' ", ARRAY_A);
				}
				else{
					$all_post_titles = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$post_types' AND post_status = 'publish' ", ARRAY_A);
				}
				
				$allpost_titles = array_column($all_post_titles, 'post_title');
				$allpost_ids = array_column($all_post_titles, 'ID');
				
				$all_post_title_label_value = [];
				$temp = 0;
				foreach($allpost_titles as $all_post_title_value){
					$post_title_value = $allpost_ids[$temp];
					if($type == 'link'){
						$get_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$all_post_title_value' AND post_type = '$post_types' ");
						$post_title_value = get_permalink($get_id);
					}

					$all_post_title_label_value[$temp]['label'] = $all_post_title_value;
					$all_post_title_label_value[$temp]['value'] = $post_title_value;
					$temp++;
				}
               
                $final_array[$temps]['label'] = $post_types;
                $final_array[$temps]['options'] =  $all_post_title_label_value;
                $temps++;
            }
		}
        return $final_array;
	}

	public function filter_by_taxonomy($get_post_type, $get_taxo_types){
		global $wpdb;
	
		$get_taxo_types = array_column($get_taxo_types, 'value');
		foreach($get_taxo_types as $get_taxo_type){
			$get_taxoid[] = $wpdb->get_var("SELECT term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy WHERE term_id = $get_taxo_type ");
		}
		$get_taxo_ids = implode(',', $get_taxoid);
	
		if(empty($get_post_type) || $get_post_type == 'undefined'){
			
			$get_taxo_posts = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id IN ($get_taxo_ids) ", ARRAY_A);
		
			if(!empty($get_taxo_posts)){
				$get_taxo_post_ids = array_column($get_taxo_posts, 'object_id');

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

			return $response['taxo_fields'];
			
		}
		else{

			$get_taxo_posts = $wpdb->get_results("SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id IN ($get_taxo_ids) ", ARRAY_A);
			$get_taxo_posts_id = array_column($get_taxo_posts, 'object_id');
			$postTypes = array_column($get_post_type, 'value');
		
			$final_array = [];
			$temp = 0;
			foreach($postTypes as $postType){
				$postType = strtolower($postType);
				$get_all_posts = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->prefix}posts WHERE post_type = '$postType' AND post_status = 'publish' ", ARRAY_A);
			
				$get_all_post_title = array_column($get_all_posts, 'post_title');
				$get_all_post_ids = array_column($get_all_posts, 'ID');

				$get_all_post_title_ids = array_combine($get_all_post_ids, $get_all_post_title);
				
				$get_posts_taxos = array_intersect($get_taxo_posts_id, $get_all_post_ids);
			
				$all_post_taxos_label_value = [];
				$temps = 0;
				foreach($get_posts_taxos as $get_posts_taxos_ids){
					$all_post_taxos_label_value[$temps]['label'] = $get_all_post_title_ids[$get_posts_taxos_ids];
					$all_post_taxos_label_value[$temps]['value'] = $get_posts_taxos_ids;
					$temps++;
				}
				
				$final_array[$temp]['label'] = $postType;
				$final_array[$temp]['options'] = $all_post_taxos_label_value;
				$temp++;
			}

			return $final_array;
		}
	}

	public function smack_get_taxonomies(){
		$taxo_arr = [];
		$temps = 0;
        $taxo = get_taxonomies();
        foreach($taxo as $tax){ 
            $terms_arg = array('taxonomy' => $tax,
                                'hide_empty' => false,
                            );
            $get_terms_arg = get_terms($terms_arg);
        
			$get_related_terms = [];
			$temp = 0;
            foreach($get_terms_arg as $term_arg){
				$get_related_terms[$temp]['label'] = $term_arg->name;
				$get_related_terms[$temp]['value'] = $term_arg->term_id;
				$temp++;
            }
			//$taxo_arr[$tax] = $get_related_terms;
			$taxo_arr[$temps]['label'] = $tax;
			$taxo_arr[$temps]['options'] = $get_related_terms;
			$temps++;
		}
		return $taxo_arr;
	}

	public static function render_sub_fields($screen, $current_screen, $smack_field_name, $smack_field_label, $smack_field_value, $postID, $field, $page_type, $source){

		$field[] = isset($array['default_value']) ? $array['default_value'] : '';
		if($field['type'] == 'textfield' || $field['type'] == 'textarea' || $field['type'] =='number' || $field['type'] == 'email' || $field['type'] == 'url' || $field['type'] == 'colorpicker' || $field['type'] == 'range' || $field['type'] == 'radiobutton' || $field['type'] == 'wysiwyg' || $field['type']=='oEmbed' || $field['type']=='password' || $field['type']=='file'){
			if($smack_field_value == '' || empty($smack_field_value)){
				$smack_field_value = isset($field['default_value'])?$field['default_value']:'';
			}
		}
		if($field['type'] == 'truefalse'){
			if($smack_field_value == ''){
				$check = gettype($smack_field_value);
				if($check == 'boolean'){
					$smack_field_value = isset($field['default'])?$field['default']:'';
				}
			}
		}
		if($field['type'] == 'textfield' || $field['type'] == 'textarea' || $field['type'] =='number' || $field['type'] == 'email' || $field['type'] == 'url' || $field['type'] == 'password'){
			$smack_field_placeholder = $field['placeholder'];
		}

		if($field['type'] == 'textfield' || $field['type'] == 'range' || $field['type'] =='number' || $field['type'] == 'email' || $field['type'] == 'password' || $field['type'] == 'clone'){
			$smack_field_prepend = isset($field['prepend']) ? $field['prepend'] : " ";
			$smack_field_append = isset($field['append']) ? $field['append'] : " ";
		}

		if($field['type'] == 'number' || $field['type'] == 'range') {
			$smack_field_min = isset($field['minimum_value']) ? $field['minimum_value'] : " ";
			$smack_field_max = isset($field['maximum_value']) ? $field['maximum_value'] : " ";
			$smack_field_stepsize = isset($field['step_size']) ? $field['step_size'] : " ";
		}

		if($field['type'] == 'textfield'){
			$field_result = self::$smackTextInst->render_text_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $field['required'], false, $smack_field_placeholder, $smack_field_prepend, $smack_field_append, $page_type, $source );
		}
		if($field['type'] == 'textarea'){
			$field_result = self::$smackTextareaInst->render_textarea_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_placeholder, $page_type, $source);
		}
		if($field['type'] =='number'){
			$field_result = self::$smackNumberInst->render_number_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_placeholder, $smack_field_prepend, $smack_field_append, $smack_field_min, $smack_field_max, $smack_field_stepsize, $page_type, $source);
		}
		if($field['type'] == 'range'){
			$field_result = self::$smackRangeInst->render_range_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_prepend, $smack_field_append, $smack_field_min, $smack_field_max, $smack_field_stepsize, $page_type, $source);
		}
		if($field['type'] == 'email'){
			$field_result = self::$smackEmailInst->render_email_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_placeholder, $smack_field_prepend, $smack_field_append, $page_type, $source);
		}
		if($field['type'] == 'url'){
			$field_result = self::$smackUrlInst->render_url_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_placeholder, $page_type, $source);
		}
		if($field['type'] == 'password'){
			$field_result = self::$smackPasswordInst->render_password_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_placeholder, $smack_field_prepend, $smack_field_append, $page_type, $source);
		}
		if($field['type'] == 'datepicker'){
			$field_result = self::$smackDateInst->render_date_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type);
		}
		if($field['type'] == 'datetimepicker'){
			$field_result = self::$smackDateTimeInst->render_datetime_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'timepicker'){
			$field_result = self::$smackTimeInst->render_time_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type);
		}
		if($field['type'] == 'colorpicker'){
			$field_result = self::$smackColorInst->render_color_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'select'){
			$field_result = self::$smackSelectInst->render_select_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'radiobutton'){
			$field_result = self::$smackRadioButtonInst->render_radiobutton_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'truefalse'){
			$field_result = self::$smackTrueFalseInst->render_truefalse_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'buttongroup'){
			$field_result = self::$smackButtonGroupInst->render_buttongroup_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'checkbox'){
			$field_result = self::$smackCheckboxInst->render_checkbox_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'googlemap'){
			$field_result = self::$smackGoogleMapInst->render_googlemap_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'clone'){
			$field_result = self::$smackCloneInst->render_clone_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $smack_field_prepend, $smack_field_append, $page_type, $screen, $current_screen, $postID);
		}
		if($field['type'] == 'postobject' || $field['type'] == 'pagelink' || $field['type'] == 'user'){
			global $wpdb;
			$field_array = [];
			$smack_field_title = '';
			$temps = 0;
			if(!empty($smack_field_value) && is_array($smack_field_value)){
				foreach($smack_field_value as $field_value){
					if($field['type'] == 'pagelink'){
						$smack_field_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $field_value ");
					}
					if($field['type'] == 'postobject'){				
						if(is_numeric($field_value)) {
							$smack_field_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $field_value ");
						}
					}
					if($field['type'] == 'user'){
						if(is_numeric($field_value)){
						$smack_user_login = $wpdb->get_var("SELECT user_login FROM {$wpdb->prefix}users WHERE ID = $field_value ");
						$smack_display_name = $wpdb->get_var("SELECT display_name FROM {$wpdb->prefix}users WHERE ID = $field_value ");
						$smack_field_title = $smack_user_login .'('. $smack_display_name .')';
						}
					}	
					if($smack_field_title) {
						$field_array[$temps]['label'] = $smack_field_title;
						$field_array[$temps]['value'] = $field_value;
						$temps++;
					}
				}

				//For Repeater
				foreach($smack_field_value as $repkey => $field_value) {
					if($field['type'] == 'postobject'){
						if(is_string($field_value) && strstr($field_value,'{')){
							$rep_post = str_replace(array('{','"','}'),'',$field_value);
							$rep_post = explode(",",$rep_post);
							foreach($rep_post as $repdata) {
								$repval[$repkey][] = substr($repdata,strpos($repdata,':')+1);
							}
							//Final data
							foreach($repval as $index => $final_data) {
								if(is_array($final_data)) {
									foreach($final_data as $finalkey => $finalval) {
										if($finalkey == 0)
										$field_array[$index][0]['label'] = $finalval;
										if($finalkey == 1)
										$field_array[$index][0]['value'] = $finalval;
									}
								}
							}
						}
						else {
							if(is_array($field_value)) {
								$temps = 0;
								foreach($field_value as $id) {
									if(is_numeric($id)) {
										$smack_field_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $id ");
										$field_array[$repkey][$temps]['label'] = $smack_field_title;
										$field_array[$repkey][$temps]['value'] = $id;
										$temps++;
									}
								}
							}
						}
					}

					if($field['type'] == 'user'){
						if(is_array($field_value)){
							$temps = 0;
							foreach($field_value as $id) {
								if(is_numeric($id)) {
									$smack_user_login = $wpdb->get_var("SELECT user_login FROM {$wpdb->prefix}users WHERE ID = $id ");
									$smack_display_name = $wpdb->get_var("SELECT display_name FROM {$wpdb->prefix}users WHERE ID = $id ");
									$smack_field_title = $smack_user_login .'('. $smack_display_name .')';
									$field_array[$repkey][$temps]['label'] = $smack_field_title;
									$field_array[$repkey][$temps]['value'] = $id;
									$temps++;
								}
							}
						}
					}

				}

			}
			
			//** Set the value for single select  */
			if($smack_field_value != '' && !is_array($smack_field_value)){
				if($field['type'] == 'postobject' || $field['type'] == 'user'){
					$smack_field_value = str_replace(array('{','"','}'),'',$smack_field_value);
					$smack_field_value = explode(",",$smack_field_value);
					foreach($smack_field_value as $field_value) {
						$value[] = substr($field_value,strpos($field_value,':')+1);
					}
					foreach($value as $index => $field_value) {
						if($index == 0)
							$field_array[0]['label'] = $field_value;
						if($index == 1)
							$field_array[0]['value'] = $field_value;
					}
				}
			}

			if($field['type'] == 'postobject'){
				$field_result = self::$smackPostObjectInst->render_postobject_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $field_array, $page_type, $source);
			}
			if($field['type'] == 'pagelink'){
				$field_result = self::$smackPageLinkInst->render_pagelink_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $field_array, $page_type, $source);
			}
			if($field['type'] == 'user'){
				$field_result = self::$smackUserInst->render_user_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $field_array, $page_type, $source);
			}
		}

		if($field['type'] == 'taxonomy'){
			$field_result = self::$smackTaxonomyInst->render_taxonomy_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type']== 'image'){
			$field_result = self::$smackImageInst->render_image_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type']== 'gallery'){
			$field_result = self::$smackGalleryInst->render_gallery_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'file'){
			$field_result = self::$smackFileInst->render_file_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'wysiwyg'){
			$field_result = self::$smackSiwygInst->render_siwyg_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type']=='oEmbed'){
			$field_result = self::$smackEmbedInst->render_embed_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'link'){
			$field_result = self::$smackLinkInst->render_link_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type,$source);
		}
		if($field['type'] == 'relationship'){
			$field_result = self::$smackRelationshipInst->render_relationship_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $source);
		}
		if($field['type'] == 'message'){
			$field_result = self::$smackMessageInst->render_message_field($field, $smack_field_label, $page_type, $source);
		}
		if($field['type'] == 'group'){
			$field_result = self::$smackGroupInst->render_group_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $screen, $current_screen, $postID);
		}
		if($field['type'] == 'repeater'){
			$field_result = self::$smackRepeaterInst->render_repeater_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $field['instructions'], $page_type, $screen, $current_screen, $postID, $source);
		}

		if($source == 'via_group'){
			return $field_result;
		}
	}
}