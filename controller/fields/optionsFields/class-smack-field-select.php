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

class SmackFieldSelect
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_select_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){						
		$select_id = "tools-engine-" . $field['field_index'];
		$select_multiple = $field['select_multiple_values'] ? true : false;
		$current_screen = get_current_screen();
		if (strpos($field['choices'], '<br />') !== false) {
			$get_choices = explode("<br />", $field['choices']);
		}else{
			$get_choices = explode("\n", $field['choices']);
		}
		
		$temp = 0;
		foreach($get_choices as $get_choice){
			$select_choices[$temp]['label'] = $get_choice;
			$select_choices[$temp]['value'] = $get_choice;
			$temp++;
		}

		$default_value = explode("\n", $field['default_value']);
		$default_array = array();		
		if (!($default_value === null) && is_array($default_value)){
			$inc = 0;
			foreach($default_value as $default){
				if (!empty($default) ){
					$default_array[$inc]['label'] = $default;
					$default_array[$inc]['value'] = $default;
					$inc++;
				}
			}
		}

		if (!empty($field['default_value'])){
			$default_array = $default_array;
		}
		elseif (!empty($field['allow_null'])){
			$default_array = "";
		}
		else{
			$default_array[0]['label'] = $get_choices[0];
			$default_array[0]['value'] = $get_choices[0];
		}
		
		if(!empty($field['default_value']) && empty($smack_field_value)){
			$smack_field_value = explode("\n", $field['default_value']);
		}

		$field_array =array();
		$field_arrays =array();
		$temps = 0;
		$tem = 0;
		if(!empty($smack_field_value) && is_array($smack_field_value)){			
			foreach($smack_field_value as $field_value){
				$stemp =0;
				if(!($field_value === null) && is_array($field_value)){
					//For repeater field
					foreach($field_value as $value){
						$field_arrays[$stemp]['label'] = $value;
						$field_arrays[$stemp]['value'] = $value;						
						if(array_key_exists('label',$field_value)){
							break;
						}
						else {
							$stemp++;	
						}						
					}
					if (!empty($field_arrays)){
						$field_array[$tem]=$field_arrays;
						$tem++;	
						$field_arrays = [];
					}
				}
				else if (strpos($field_value, 'label') !== false) {
					$value = explode('"',$field_value);
					$field_array[$temps]['label'] = $value[3];
					$field_array[$temps]['value'] = $value[3];
					$temps++;	
				}
	
				else{	
					// Basic select field
						$field_array[$temps]['label'] = $field_value;
						$field_array[$temps]['value'] = $field_value;
						//Single select
						if(array_key_exists('label',$smack_field_value)){
							break;
						}	
						//Multi select
						else {
							$temps++;	
						}						
				}
			}
		}	
//** Set the value for single select  */
		if($smack_field_value != '' && !is_array($smack_field_value)){			
			if(strpos($smack_field_value, '[{"label"') !== false){
				$temp =0;
				$field_arrayy=array();
				$smack_field_value = str_replace(array('[','{','"','}',']'),'',$smack_field_value);
				$smack_field_value = explode(",",$smack_field_value);	
				foreach($smack_field_value as $field_value) {
					if(strpos($field_value, 'value:') !== false){
						$value[] = substr($field_value,strpos($field_value,':')+1);
					}
					
					
				}
				foreach($value as $field_value) {
					$field_arrayy[$temp]['label'] = $field_value;
					$field_arrayy[$temp]['value'] = $field_value;
					$temp++;
				}
				$field_array= $field_arrayy;
			}
			else{
				$smack_field_value = str_replace(array('{','"','}'),'',$smack_field_value);
				$smack_field_value = explode(",",$smack_field_value);
				foreach($smack_field_value as $field_value) {
					$value[] = substr($field_value,strpos($field_value,':')+1);
				}
				foreach($value as $field_value) {
					$field_array[0]['label'] = $field_value;
					$field_array[0]['value'] = $field_value;
					break;
				}
			}
		}
				
		if($smack_field_value == '' && $field['allow_null'] == '') {
			$field_array[0]['label'] = $get_choices[0];
			$field_array[0]['value'] = $get_choices[0];
		}
		//For repeater
		if(is_array($smack_field_value) && $field['allow_null'] == ''){
			foreach($smack_field_value as $repkey => $value){
				if($value == ''){
					$field_array[$repkey]['label'] = $get_choices[0];
					$field_array[$repkey]['value'] = $get_choices[0];
				}
			}
		}
		
		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$select_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $field_array,
			'default_value' => $default_array,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_multiple' => $select_multiple,
			'field_params' => $select_choices,
			'field_pagetype' => $page_type
		);		
		
		if($source == 'via_group'){
			return $select_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($select_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($select_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}