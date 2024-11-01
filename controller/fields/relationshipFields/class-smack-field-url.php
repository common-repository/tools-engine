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

class SmackFieldUrl
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_url_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $smack_field_placeholder, $page_type, $source){
		$url_id = "tools-engine-" . $field['field_index'];

		if (!empty($field['default_value'])){
			$default_value = $field['default_value'];
		}
		else{
			$default_value = "";
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}
		
		$url_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_placeholder' => $smack_field_placeholder,
			'field_pagetype' => $page_type,
		);

		if($source == 'via_group'){
			return $url_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($url_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($url_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}