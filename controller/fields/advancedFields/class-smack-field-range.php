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

class SmackFieldRange
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_range_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $smack_field_prepend, $smack_field_append, $smack_field_min, $smack_field_max, $smack_field_stepsize, $page_type, $source){
		$range_id = "tools-engine-" . $field['field_index'];

		if (!empty($field['default_value'])){
			$default_value = $field['default_value'];
		}
		else{
			$default_value = "";
		}

		if (!empty($field["default_value"]) && empty($smack_field_value)){
			$smack_field_value = $field["default_value"];
		} 

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$range_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_prepend' => $smack_field_prepend,
			'field_append' => $smack_field_append,
			'field_min' => $smack_field_min,
			'field_max' => $smack_field_max,
			'field_stepsize' => $smack_field_stepsize,
			'field_pagetype' => $page_type,
		);

		if($source == 'via_group'){
			return $range_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($range_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($range_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}
