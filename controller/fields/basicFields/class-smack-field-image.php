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

class SmackFieldImage
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

	public function render_image_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
	
		$source= isset($source) ? $source: '';
		global $wpdb;
		$default_allowed_file_type = array();
		$default_allowed_file_type[0]["label"] = 'jpg|jpeg|jpe';
		$default_allowed_file_type[0]["value"]= 'image/jpeg';
		$default_allowed_file_type[1]["label"] = 'png';
		$default_allowed_file_type[1]["value"]= 'image/png';
    	$default_allowed_file_type[2]["label"] = 'gif';
		$default_allowed_file_type[2]["value"]= 'image/gif';
		if(!empty($smack_field_value)){
			if(is_array($smack_field_value)){
				foreach($smack_field_value as $repkey => $value)
				{	
					if(!empty($value)){
						$get_image_url[$repkey] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $value");	
					}
					else {
						$get_image_url[$repkey] = '';
					}
				}		
			}
			else{
				$get_image_url = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $smack_field_value");
			}
		}else{
			$get_image_url = '';
		}

		if(!empty($field['preview_size']['value'])){
			if($field['preview_size']['value'] == 'thumbnail'){
				$image_width = $image_height = '150px';
			}
			elseif($field['preview_size']['value'] == 'medium'){
				$image_width = $image_height = '300px';
			}
			elseif($field['preview_size']['value'] == 'large'){
				$image_width = $image_height = '1024px';
			}
			elseif($field['preview_size']['value'] == 'medium_large'){
				$image_width = '768px';
				$image_height = 'none';
			}
			elseif($field['preview_size']['value'] == 'post_thumbnail'){
				$image_width = '1200px';
				$image_height = '9999px';
			}
			elseif($field['preview_size']['value'] == 'full_size'){
				$image_width = $image_height = 'none';
			}
		}
		else{
			$image_width = $image_height = '150px';
		}
		
		$image_file_types = '';
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
			$image_file_types = array_column($field['allowed_file_types'], 'value');
			$image_file_types = implode(',', $image_file_types); 
		}
		if(isset($field['library'])){
		$library_value = $field['library'][1]['uploadedToPost'];
		if($library_value == 1){
			$library_value = 'UploadToPost';
		}
		else{
			$library_value = 'All';
		}
	}

	if ($field['required'] === true && empty($smack_field_value)){
		$required_message = true;
	}
	else{
		$required_message = false;
	}

        $image_id = "tools-engine-" . $field['field_index'];

        $image_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_id' => $smack_field_value,
			'field_value' => $smack_field_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
            'field_url' => $get_image_url,
			'field_instructions' => $smack_field_instructions,
			'field_width' => $image_width,
			'field_height' => $image_height,
			'file_types' => $image_file_types,
			'field_pagetype' => $page_type,
		);
		if(isset($library_value)){
			$image_field_array['field_library'] = $library_value;
		}					

		if($source == 'via_group'){
			return $image_field_array;
		}

        ?>
            <div 
                id="<?php echo esc_attr($image_id) ?>" 
                data-params="<?php echo htmlspecialchars(json_encode($image_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
        <?php
	}
}