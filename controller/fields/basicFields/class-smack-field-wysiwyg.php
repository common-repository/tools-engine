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

class SmackFieldSiwyg
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	
	public function render_siwyg_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		$siwyg_id = "tools-engine-" . $field['field_index'];	

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

		$siwyg_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'default_value' => $default_value,
			'field_required' => $field['required'],
			'media_buttons' => $field['show_media_upload_buttons'],
			'field_instructions' => $smack_field_instructions,
			'field_required_alert' => $required_message,
			'field_pagetype' => $page_type,
		);
		
		if($source == 'via_group'){
			return $siwyg_field_array;
		}

		?><div 
				id="<?php echo esc_attr($siwyg_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($siwyg_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php

		
		// if($field['show_media_upload_buttons']){
		// 	$show_media = true;
		// }
		// else{
		// 	$show_media = false;
		// }

		// $settings = array(
		// 	'editor_height' => 230, // In pixels, takes precedence and has no default value
		// 	'textarea_rows' => 10,
		// 	'media_buttons' => $show_media,
		// 	//'quicktags' => false  // Has no visible effect if editor_height is set, default is 20
		// );
		
	}
}