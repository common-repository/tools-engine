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

class SmackFieldCheckbox
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_checkbox_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		$checkbox_id = "tools-engine-" . $field['field_index'];
		$current_screen = get_current_screen();

		if(empty($smack_field_value)){
			$smack_field_value = [];
		}

		if (!empty($field['default_value'])){
			$default_value = explode("\n", $field['default_value']);
		}
		else{
			$default_value = [];
		}

		if(!is_array($smack_field_value)){
			$smack_field_value = str_replace(array('[','"',']'),'',$smack_field_value);
			$smack_field_value = explode(",",$smack_field_value);
		}

		if(!empty($field['default_value']) && empty($smack_field_value)){
			$smack_field_value = explode("\n", $field['default_value']);
		}

		if($field['layout'][1]['horizontal']){
			$smack_field_layout = "true";
		}
		else{
			$smack_field_layout = "false";
		}

		if (strpos($field['choices'], '<br />') !== false) {
			$get_choices = explode("<br />", $field['choices']);
		}else{
			$get_choices = explode("\n", $field['choices']);
		}
		// $get_choices = explode("\n", $field['choices']);
		if($field['toggle']) {
			array_unshift($get_choices,'Toggle all');
			$smack_field_toggle = "Yes";
		}
		else {
			$smack_field_toggle = "No";
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}
		
		$checkbox_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_layout' => $smack_field_layout,
			'field_params' => $get_choices,
			'create_options' => $field['allow_custom'],
			'field_pagetype' => $page_type
		);
		
		if($source == 'via_group'){
			return $checkbox_field_array;
		}

		?><div 
				id="<?php echo esc_attr($checkbox_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($checkbox_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}