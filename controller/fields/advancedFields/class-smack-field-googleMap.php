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

class SmackFieldGoogleMap
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_googlemap_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		
		$googlemap_id = "tools-engine-" . $field['field_index'];
		
		if(!empty($smack_field_value)){
			$get_map_details = json_decode($smack_field_value, True);
		}
		else{
			$get_map_details['lat'] = '';
			$get_map_details['lng'] = '';
			$get_map_details['zoom'] = '';
			if(!empty($field['center'][0]['latitude'])){
				$get_map_details['lat'] = $field['center'][0]['latitude'];
			}
			if(!empty($field['center'][1]['longitude'])){
				$get_map_details['lng'] = $field['center'][1]['longitude'];
			}
			if(!empty($field['zoom'])){
				$get_map_details['zoom'] = $field['zoom'];
			}
		}		
		
		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}	
		$googlemap_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_pagetype' => $page_type,
            'field_latitude' => $get_map_details['lat'],
            'field_longitude' => $get_map_details['lng'],
            'field_zoom' => $get_map_details['zoom'],
            'field_height' => $field['height']
		);

		if($source == 'via_group'){
			return $googlemap_array;
		}
        

		?><div 
				id="<?php echo esc_attr($googlemap_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($googlemap_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}