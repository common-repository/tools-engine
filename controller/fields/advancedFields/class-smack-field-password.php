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

class SmackFieldPassword
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_password_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $smack_field_placeholder, $smack_field_prepend, $smack_field_append, $page_type, $source){
		$password_id = "tools-engine-" . $field['field_index'];


		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}
		
		$password_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_placeholder' => $smack_field_placeholder,
			'field_prepend' => $smack_field_prepend,
			'field_append' => $smack_field_append,
			'field_pagetype' => $page_type,
		);

		if($source == 'via_group'){
			return $password_field_array;
		}
		
		?><div 
				id="<?php echo esc_attr($password_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($password_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}