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

class SmackFieldPostObject
{
	protected static $instance = null;
	protected static $helperInst = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$helperInst = SmackFieldHelper::getInstance();
		}
		return self::$instance;
	}

	public function render_postobject_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $field_array, $page_type, $source){	
		$postobject_id = "tools-engine-" . $field['field_index'];
		$get_titles = self::$helperInst->get_all_post_titles('postobject', $field['filter_by_post_type'], $field['filter_by_taxonomy']);
		$select_multiple = $field['select_multiple_values'] ? true : false;
		if($field['allow_null'] == '' && empty($field_array)){
			if(!is_array($smack_field_value)){
			if(isset($get_titles[0]['options'])){
				$first_level = $get_titles[0]['options'];
				if(isset($first_level[0])){
					$field_array[0] = $first_level[0];
				}
			}
		}
			//For repeater
			if(strstr($smack_field_name,'wp-smack-repeaterField')){
				if(isset($get_titles[0]['options'])){
					$first_level = $get_titles[0]['options'];
					if(isset($first_level[0])){
						$field_array[0][0] = $first_level[0];
					}
				}
			}
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$postobject_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $field_array,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_multiple' => $select_multiple,
			'field_null' => $field['allow_null'],
			'field_params' => $get_titles,
			'field_pagetype' => $page_type
		);
		if($source == 'via_group'){
			return $postobject_field_array;
		}

		?><div 
				id="<?php echo esc_attr($postobject_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($postobject_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}