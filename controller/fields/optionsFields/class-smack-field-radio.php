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

class SmackFieldRadioButton
{
	protected static $instance = null;
	
	
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_radiobutton_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		$radiobutton_id = "tools-engine-" . $field['field_index'];
		if (strpos($field['choices'], '<br />') !== false) {
			$get_choices = explode("<br />", $field['choices']);
		}else{
			$get_choices = explode("\n", $field['choices']);
		}

		if (!empty($field['default_value'])){
			$default_value = $field['default_value'];
		}
		elseif (!empty($field['allow_null'])){
			$default_value = '';
		}
		else{
			$default_value = $get_choices[0];
		}

		if(!empty($field['default_value']) && empty($smack_field_value)){
			$smack_field_value = $field['default_value'];
		}

		if($field['layout'][1]['horizontal']){
			$smack_field_layout = "true";
		}else{
			$smack_field_layout = "false";
		}
		
		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$radio_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_layout' => $smack_field_layout,
			'field_params' => $get_choices,
			'create_options' => $field['other'],
			'field_pagetype' => $page_type
		);
		if($smack_field_value == '') {
			$radio_field_array['checked']= $field['allow_null'] ? 'Yes' : 'No';
		}

		if($source == 'via_group'){
			return $radio_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($radiobutton_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($radio_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}