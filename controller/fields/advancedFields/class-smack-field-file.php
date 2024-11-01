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

class SmackFieldFile
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

	public function render_file_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type,$source){
		global $wpdb;
		//$source= isset($source) ? $source: '';
		$get_file_guid= isset($get_file_guid) ? $get_file_guid: '';
		$get_file_name= isset($get_file_name) ? $get_file_name: '';
		$file_types = [];
		$get_file_title = '';
		$get_file_path = '';
		$get_filesize_kb = '';

		if(!empty($field['allowed_file_types'])){
			$file_types = array_column($field['allowed_file_types'], 'value');
			//$file_types = implode(',', $file_types); 
		}

		if(!empty($smack_field_value)){

			if(is_array($smack_field_value)) {
				$get_file_guid= [];
				$get_file_name= [];
				$get_file_title = [];
				$get_file_path = [];
				$get_filesize_kb = [];
				//For repeater
				foreach($smack_field_value as $repkey => $id){
					if(is_numeric($id)) {
						$get_file_guid[$repkey] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $id");
						$get_file_title[$repkey] = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $id");
						$get_file_name[$repkey] = basename($get_file_guid[$repkey]);
						$get_file_path[$repkey] = get_attached_file($id);
						$get_file_size[$repkey] = filesize($get_file_path[$repkey]);
						$get_filesize_kb[$repkey] = round($get_file_size[$repkey] / 1024) . ' KB';
					}
				}
			}
			else {
				$get_file_guid = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $smack_field_value");
				$get_file_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $smack_field_value");
				$get_file_name = basename($get_file_guid);
				$get_file_path = get_attached_file($smack_field_value);
				$get_file_size = filesize($get_file_path);
				$get_filesize_kb = round($get_file_size / 1024) . ' KB';
			}
		}
	
		$file_id = "tools-engine-" . $field['field_index'];

		//restrict files not in required file size
		$get_all_file_ids = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND post_status != 'trash' ", ARRAY_A);
		$required_minimum_size = isset($field['minimum']) ? $field['minimum'] : "";
		$required_maximum_size = isset($field['maximum']) ? $field['maximum'] : "";

		$file_sizes = array();
		if(!empty($required_minimum_size) || !empty($required_maximum_size)){
			foreach($get_all_file_ids as $file_ids){
				$fileId = $file_ids['ID'];	
				$get_filePath = get_attached_file($fileId);
				$get_fileSize = filesize($get_filePath);
				//$mbytes = number_format($get_file_size / 1048576, 2) . ' MB';
				
				$get_filesize = round($get_fileSize / 1024);
				if($get_filesize < $required_minimum_size || $get_filesize > $required_maximum_size){
					$file_sizes[] = $fileId;
				}
			}
		}


		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}	
		
        $file_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'file_name' => $get_file_name,
			'file_title' => $get_file_title,
			'file_link' => $get_file_guid,
			'file_size' => $get_filesize_kb,
			'file_types' => $file_types,
			'field_pagetype' => $page_type,
			'file_sizes' => $file_sizes
		);

		if($source == 'via_group'){
			return $file_field_array;
		}

        ?>
            <div 
                id="<?php echo esc_attr($file_id) ?>" 
                data-params="<?php echo htmlspecialchars(json_encode($file_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
        <?php
	}
}