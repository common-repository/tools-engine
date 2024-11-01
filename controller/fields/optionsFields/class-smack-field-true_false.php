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

class SmackFieldTrueFalse
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_truefalse_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){		
		$truefalse_id = "tools-engine-" . $field['field_index'];
		$smack_field_message = $field['message'];
		
		if (isset($field['default']) && $smack_field_value == null){
				$smack_field_value = $field['default'];
		}

		$smack_check_value = false;

		if ($field['default'] == true || $field['default'] == 1){
			$default_value = $field['default'];
		}
		else{
			$default_value = false;
		}

		//For repeater
		if(is_array($smack_field_value)){
			foreach($smack_field_value as $value){

				if($value == "1" || $value == "true"){
					$smack_check_value[] = true;
				}
				else {
					$smack_check_value[] = false;
				}
			}
		}
		//Basic truefalse
		else {
		if($smack_field_value == 1 || $smack_field_value == true){
			$smack_check_value = true;
		}
		}
	
		if ($field['required'] === true && $smack_field_value == null){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$truefalse_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_message' => $smack_field_message,
			'field_check' => $smack_check_value,
			'field_pagetype' => $page_type
		);

		if($source == 'via_group'){
			return $truefalse_field_array;
		}

		?><div 
				id="<?php echo esc_attr($truefalse_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($truefalse_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}