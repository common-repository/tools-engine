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

class SmackFieldButtonGroup
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_buttongroup_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){

		$buttongroup_id = "tools-engine-" . $field['field_index'];
		$current_screen = get_current_screen();
		if (strpos($field['choices'], '<br />') !== false) {
			$get_choices = explode("<br />", $field['choices']);
		}else{
			$get_choices = explode("\n", $field['choices']);
		}

		if(empty($smack_field_value)){
			if(!empty($field['default_value'])){
				$smack_field_value = $field['default_value'];
			}
		}
		$get_choice_arr = [];
		$temp = 0;
		$inc = 0;

		if($field['layout'][1]['horizontal']){
			$smack_field_layout = "true";
		}else{
			$smack_field_layout = "false";
		}

		if(empty($smack_field_value)){
			if($current_screen->action == 'add'){
				if(!empty($field['default_value'])){
					foreach($get_choices as $get_choice){
						$get_choice_arr[$temp]['label'] = $get_choice;
						if($field['default_value'] == $get_choice){
							$get_choice_arr[$temp]['selected'] = true;
						}else{
							$get_choice_arr[$temp]['selected'] = false;
						}
						$temp++;
					}
				}
				else {
					foreach($get_choices as $get_choice){
						$get_choice_arr[$temp]['label'] = $get_choice;
						if($field['allow_null'] == ''){	
							if($temp == 0){
								$get_choice_arr[$temp]['selected'] = true;
							}else{
								$get_choice_arr[$temp]['selected'] = false;
							}				
						}
						else {
							$get_choice_arr[$temp]['selected'] = false;
						}
						$temp++;
					}
				}
			}
			else {
				foreach($get_choices as $get_choice){
					$get_choice_arr[$temp]['label'] = $get_choice;
					if($field['allow_null'] == ''){	
						if($temp == 0){
							$get_choice_arr[$temp]['selected'] = true;
						}else{
							$get_choice_arr[$temp]['selected'] = false;
						}				
					}
					else {
						$get_choice_arr[$temp]['selected'] = false;
					}
					$temp++;
				}
			}				
		}
		else{			
			foreach($get_choices as $get_choice){
				$get_choice_arr[$temp]['label'] = $get_choice;
				if($smack_field_value == $get_choice){
					$get_choice_arr[$temp]['selected'] = true;
				}else{
					$get_choice_arr[$temp]['selected'] = false;
				}
				$temp++;
			}
		}

		$field_default = $field['default_value'];

		if (!empty($field['default_value'])){
			foreach($get_choices as $get_choice){
				$default_value[$inc]['label'] = $get_choice;
				if($field['default_value'] == $get_choice){
					$default_value[$inc]['selected'] = true;
				}else{
					$default_value[$inc]['selected'] = false;
				}
				$inc++;
			}
		}
		elseif (!empty($field['allow_null'])){
			foreach($get_choices as $get_choice){
				$default_value[$inc]['label'] = $get_choice;
				$default_value[$inc]['selected'] = false;
				$inc++;
			}
			$field_default = "";
		}
		else{
			$get_choice_arr[0]['selected'] = true;
			$default_value = $get_choice_arr;
			$field_default = $get_choice_arr[0]['label'];
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$button_group_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_params' => $default_value,
			'default_value' => $field_default,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_layout' => $smack_field_layout,
			'field_params' => $get_choice_arr,
			'field_pagetype' => $page_type
		);

		if($source == 'via_group'){
			return $button_group_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($buttongroup_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($button_group_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}