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

class SmackFieldGallery
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

	public function render_gallery_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		global $wpdb;
		$default_allowed_file_type = array();
		$default_allowed_file_type[0]["label"] = 'jpg|jpeg|jpe';
		$default_allowed_file_type[0]["value"]= 'image/jpeg';
		$default_allowed_file_type[1]["label"] = 'png';
		$default_allowed_file_type[1]["value"]= 'image/png';
    	$default_allowed_file_type[2]["label"] = 'gif';
		$default_allowed_file_type[2]["value"]= 'image/gif';
		$attachment_ids[2]['attach_id'] = 'test2';

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}
		
		if(!empty($field['preview_size']['value'])){
			if($field['preview_size']['value'] == 'thumbnail'){
				$gallery_width = $gallery_height = '150px';
			}
			elseif($field['preview_size']['value'] == 'medium'){
				$gallery_width = $gallery_height = '300px';
			}
			elseif($field['preview_size']['value'] == 'large'){
				$gallery_width = $gallery_height = '1024px';
			}
			elseif($field['preview_size']['value'] == 'medium_large'){
				$gallery_width = '768px';
				$gallery_height = 'none';
			}
			elseif($field['preview_size']['value'] == 'post_thumbnail'){
				$gallery_width = '1200px';
				$gallery_height = '9999px';
			}
			elseif($field['preview_size']['value'] == 'full_size'){
				$gallery_width = $gallery_height = 'none';
			}
		}
		else{
			$gallery_width = $gallery_height = '150px';
		}

		$gallery_file_types = '';
		
		if(!empty($field['allowed_file_types'])){
			$allowed_file_types = array();
			for($i = 0; $i < sizeof($default_allowed_file_type); $i++) {			
				$count = 0;
				for($j = 0; $j < sizeof($field['allowed_file_types']); $j++) {			
					if($default_allowed_file_type[$i]['value'] === $field['allowed_file_types'][$j]['value'] ) {
						$count = $count + 1;						
					}
				}
				if($count > 0) {
					array_push($allowed_file_types, $default_allowed_file_type[$i]);
				}
			}
			for($i = 0; $i < sizeof($field['allowed_file_types']); $i++) {
				$allowed_types[$allowed_file_types[$i]['label']] = $allowed_file_types[$i]['value'];
			} 
			update_option('wp_smack_restricted_file_types', $allowed_types);
			$gallery_file_types = array_column($field['allowed_file_types'], 'value');
			$gallery_file_types = implode(',', $gallery_file_types);
		}
		
		if(!empty($smack_field_value)){
			//For repeater
			if(is_array($smack_field_value)) {
				$get_gallery_url = [];
				$smackrep_field_values = array(array());
				foreach($smack_field_value as $repkey => $value){
					$field_values = explode(',',$value);
					foreach($field_values as $field_value){
						$smackrep_field_values[$repkey][] = intval($field_value);
						if($field_value){
							$get_gallery_url[$repkey][] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $field_value");
						}
					}
				}
				$smack_field_values = $smackrep_field_values;
			}
			//For normal fields
			else {
				$field_values = explode(',', $smack_field_value);
				$get_gallery_url = [];
				$smack_field_values = [];
				foreach($field_values as $field_value){
					$smack_field_values[] = intval($field_value);
					$get_gallery_url[] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $field_value");
				}
			}
			
		}else{
			$get_gallery_url = [];
			$smack_field_values = [];
		}

		$gallery_id = "tools-engine-" . $field['field_index'];
		$gallery_upload_id = 'gallery-upload'. $field['field_index'];
		$gallery_hidden_id = 'gallery-hidden'. $field['field_index'];

        $gallery_field_array = array(
			'field_upload_id' => $gallery_upload_id,
			'field_hidden_id' => $gallery_hidden_id,
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_values,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'image_source' => $get_gallery_url,
			'field_instructions' => $smack_field_instructions,
			'field_width' => $gallery_width,
			'field_height' => $gallery_height,
			'field_pagetype' => $page_type,
			'file_types' => $gallery_file_types,
		);

		if($source == 'via_group'){
			return $gallery_field_array;
		}

        ?>
            <div 
                id="<?php echo esc_attr($gallery_id) ?>" 
                data-params="<?php echo htmlspecialchars(json_encode($gallery_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
        <?php
	}
}