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

class SmackFieldRelationship
{
	protected static $instance = null;
	protected static $helperInst = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$helperInst = SmackFieldHelper::getInstance();
		}
		return self::$instance;
	}
	
	public function render_relationship_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		
		$relational_id = "tools-engine-" . $field['field_index'];
		$get_taxonomies = self::$helperInst->smack_get_taxonomies();
		$get_filer_array = self::$helperInst->get_all_post_titles('relationship', $field['filter_by_post_type'], $field['filter_by_taxonomy']);
	
		$get_post_types = get_post_types();
		$other_taxo = array('nav_menu','action-group','product_type','product_visibility','product_shipping_class','wpsc-variation','wpsc_log_type','post_format','link_category', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'smack-field', 'tools-engine');
		$post_types = [];
		$temp = 0;
		foreach($get_post_types as $get_post_type){
			if(!in_array($get_post_type, $other_taxo)){
				$post_types[$temp]['label'] = $get_post_type;
				$post_types[$temp]['value'] = $get_post_type;
				$temp++;
			}
		}

		$index = 0;
		$post_value = array();		
		if(is_array($smack_field_value)){
		foreach($smack_field_value as $post_values){
			if(array_key_exists(0,$post_values) && is_array($post_values[0]) && !empty($post_values[0]))
			$post_value[$index] = $post_values[0][0];
			$index++;
		}
	}

		$index_1 = 0;
		$taxo_value = array();
		if(is_array($smack_field_value)){
		foreach($smack_field_value as $taxo_values){
			if(array_key_exists(1,$taxo_values) && is_array($taxo_values[1]) && !empty($taxo_values[0]))
			$taxo_value[$index_1] = $taxo_values[1][0];
			$index_1++;
		}
	}

		$index_2 = 0;
		$temps_2 = 0;
		$filter_value = array();
		$sorted_filter = array();
		foreach($smack_field_value as $fields_value){
			global $wpdb;
			if(array_key_exists(2,$fields_value) && is_array($fields_value[2])){
			foreach($fields_value[2] as $values){
				$index_2 = 0;
				foreach ($values as $vals){
					$get_filter_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $vals");
					$filter_value[$index_2]['label'] = $get_filter_title;
					$filter_value[$index_2]['value'] = $vals;
					$index_2++;
				}
				if (!empty($filter_value)){
					$sorted_filter[$temps_2] = $filter_value;
					$temps_2++;
					$filter_value = [];
				}
			}
		}
		}
		
		$smack_relationship_maximum_posts = 0;
		$smack_relationship_minimum_posts = '';
		if(!empty($field['maximum_posts'])){
			$smack_relationship_maximum_posts = $field['maximum_posts'];
		}
		if(!empty($field['minimum_posts'])){
		 	$smack_relationship_minimum_posts = $field['minimum_posts'];
		 }

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$relational_field_array = array (
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'post_value' => json_encode($post_value),
			'taxo_value' => json_encode($taxo_value),
			'filter_values' => json_encode($sorted_filter),
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_taxos' => json_encode($get_taxonomies),
			'field_filters' => json_encode($get_filer_array),
			'field_maximum_posts' => $smack_relationship_maximum_posts,
			'field_minimum_posts' => $smack_relationship_minimum_posts,
			'field_page_type' => $page_type,
			'field_options' => json_encode($post_types),
		);		

		if($source == 'via_group'){
			return $relational_field_array;
		}

		?><div 
				id="<?php echo esc_attr($relational_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($post_types), ENT_QUOTES, 'UTF-8'); ?>" 
				data-name="<?php echo esc_attr($smack_field_name) ?>" 
				data-label="<?php echo esc_attr($smack_field_label) ?>" 
				post-value="<?php echo htmlspecialchars(json_encode($post_value), ENT_QUOTES, 'UTF-8'); ?>"
				taxo-value="<?php echo htmlspecialchars(json_encode($taxo_value), ENT_QUOTES, 'UTF-8'); ?>"
				filter-values="<?php echo htmlspecialchars(json_encode($sorted_filter), ENT_QUOTES, 'UTF-8'); ?>"
				data-instructions="<?php echo esc_attr($smack_field_instructions) ?>"
				data-taxos="<?php echo htmlspecialchars(json_encode($get_taxonomies), ENT_QUOTES, 'UTF-8'); ?>"
				data-filters="<?php echo htmlspecialchars(json_encode($get_filer_array), ENT_QUOTES, 'UTF-8'); ?>"
				data-maximum="<?php echo esc_attr($smack_relationship_maximum_posts) ?>"
				data-minimum="<?php echo esc_attr($smack_relationship_minimum_posts) ?>"
				data-page-type="<?php echo esc_attr($page_type) ?>" 
				>
			</div>
		<?php	
	}
}