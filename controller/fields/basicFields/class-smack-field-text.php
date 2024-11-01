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

class SmackFieldText
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_text_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $is_required, $required_message,$smack_field_placeholder,$smack_field_prepend, $smack_field_append, $page_type, $source){
	
		$text_id = "tools-engine-" . $field['field_index'];
		$character_limit = $field['maxlength'];

		if (!empty($field['default_value'])){
			$default_value = $field['default_value'];
		}
		else{
			$default_value = "";
		}

		if ($is_required === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$text_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_instructions' => $smack_field_instructions,
			'field_required' => $is_required,
			'field_required_alert' => $required_message,
			'field_placeholder' => $smack_field_placeholder,
			'field_prepend' => $smack_field_prepend,
			'field_append' => $smack_field_append,
			'field_pagetype' => $page_type,
			'field_limit' => $character_limit
		);
		
		if($source == 'via_group'){
			return $text_field_array;
		}

		?><div 
				id="<?php echo esc_attr($text_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($text_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}