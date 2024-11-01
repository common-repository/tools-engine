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

class SmackFieldTaxonomy
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_taxonomy_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){	
		$taxonomys_id = "tools-engine-" . $field['field_index'];
		$smack_field_array = [];
		if(!empty($field['taxonomy']['value'])){
			$filter_taxonomy = $field['taxonomy']['value'];
		}
		else{
			$filter_taxonomy = 'category';
		}
		$taxonomy_choices = [];
		$index = 0;
		$get_choices = self::$instance->get_all_terms($filter_taxonomy);
		foreach($get_choices as $value){
			$taxonomy_choices[$index]['label'] = $value['label'];
			$taxonomy_choices[$index]['value'] = $value['id'];
			$index++;	
		}
		$temp = 0;
		switch($field['appearance']['label']){
			case 'Radio Buttons': {
				// Checkbox to radio button values
				if(!empty($smack_field_value) && is_array($smack_field_value)){
					foreach($smack_field_value as $smack_ids){
						if(is_array($smack_ids)){
							break;
						}
						else {
							if(is_int($smack_ids)){
								$smack_field_array = get_term($smack_ids)->name;
								break;
							}
						}
					}
					//For repeater
					foreach($smack_field_value as $key => $smack_ids){
						if(is_array($smack_ids)){
							foreach($smack_ids as $id) {
								$smack_field_array[$key] = get_term($id)->name;
								break;
							}
						}
						//radio button and Select values
						if(is_string($smack_ids)){
							//Select
							if(strstr($smack_ids,'{')) {
								$get_fieldvalue = json_decode($smack_ids);
								$smackData = json_decode(json_encode($get_fieldvalue),true);
								$smack_field_array[$key] = $smackData['label'];
							}
							//Radio
							else {
								$smack_field_array[$key] = $smack_ids;
							}
						}
					}
				}
				

				if($smack_field_value != '' && !is_array($smack_field_value)) {
					// Selectbox to radio button values
					if(strstr($smack_field_value,'{')){
						$get_fieldvalue = json_decode($smack_field_value);
						$smackData = json_decode(json_encode($get_fieldvalue),true);
						$smack_field_array = $smackData['label'];
					}
					else {
						//For Radio button values
					$smack_field_array = '';
					$smack_field_array = $smack_field_value;
					}
				}
				break;
			}
			case 'Select': 
			case 'Multi Select': {
				//Checkbox to select && MultiSelect values						
                if(!empty($smack_field_value) && is_array($smack_field_value)){
                    foreach($smack_field_value as $value){
						if(is_array($value))
							break;
						if(is_int($value))	{
							$get_label = get_term($value)->name;
							$smack_field_array[$temp]['label'] = $get_label;
							$smack_field_array[$temp]['value'] = $value;				
							if($field['appearance']['label'] == 'Select')
								break;
							$temp++;
						}
                    }
					
					// For repeater
					foreach($smack_field_value as $key => $smack_ids){
						$rep_temp = 0;
						if(is_array($smack_ids)) {
							foreach($smack_ids as $keys => $id) {
								if (gettype($keys) == 'integer'){
									$smack_field_array[$key][$rep_temp]['label'] = get_term( $id )->name;
									$smack_field_array[$key][$rep_temp]['value'] = $id;
									$rep_temp++;
								}
								else{
									$smack_field_array = $smack_field_value;
								}
							}
						}
						if(is_string($smack_ids)) {
							//select
							if(strstr($smack_ids,'{')){
								$get_fieldvalue = json_decode($smack_ids);
								$smack_field_value = json_decode(json_encode($get_fieldvalue),true);
								$smack_field_array[$key] = $smack_field_value;
							}
							else {
								if ($field['appearance']['label'] == 'Radio Buttons'){
									//radio button
									foreach($taxonomy_choices as $value) {
										if($value['label'] == $smack_ids){
											$smack_field_array[$key][$temp] = $value;
											break;
										}
									}
								}
							}
						}
					}

                }
				// For Select
				if($smack_field_value != '' && !is_array($smack_field_value)) {
					if(strstr($smack_field_value,'{')){
						$get_fieldvalue = json_decode($smack_field_value);
						//Convert stdclass obj to array
						$smack_field_value = json_decode(json_encode($get_fieldvalue),true);
						//Select value contains only one data (label,value)
						$smack_field_array = $smack_field_value;
					}
					//Radio to Select && MultiSelect
					else {
						foreach($taxonomy_choices as $value){
							if($value['label'] == $smack_field_value) {
								$smack_field_array[$temp] = $value;
							break;
							}
						}
					}
				}
                break;
            }
			default: {		
				// For Checkbox values
				if(!empty($smack_field_value) && is_array($smack_field_value)){
					foreach($smack_field_value as $smack_ids){
						if(is_array($smack_ids)) {
							break;
						}
						else {
							if(is_int($smack_ids)){
								$smack_field_array[$temp]['label'] = get_term( $smack_ids )->name;
								$smack_field_array[$temp]['value'] = $smack_ids;
								$temp++;
							}
						}
					}
					//For repeater
					foreach($smack_field_value as $key => $smack_ids){
						$rep_temp = 0;
						//Checkbox and MultiSelect
						if(is_array($smack_ids) ) {
							foreach($smack_ids as $keys => $id) {
                                if (gettype($keys) == 'integer'){
									$smack_field_array[$key][$rep_temp]['label'] = get_term( $id )->name;
									$smack_field_array[$key][$rep_temp]['value'] = $id;
									$rep_temp++;
								}
								else{
									$smack_field_array = $smack_field_value;
								}
                            }
						}

						// Radio button and select
						if(is_string($smack_ids)) {
							//Select
							if(strstr($smack_ids,'{')) {
								$get_fieldvalue = json_decode($smack_ids);
								$smack_field_value = json_decode(json_encode($get_fieldvalue),true);
								$smack_field_array[$key][$temp] = $smack_field_value;
							}
							else {
								//Radio
								foreach($taxonomy_choices as $value) {
									if($value['label'] == $smack_ids){
										$smack_field_array[$key][$temp] = $value;
										break;
									}
								}
							}
						}
					}
				}
				
				if($smack_field_value != '' && !is_array($smack_field_value)){
				//Radio button to checkbox values
					foreach($taxonomy_choices as $value){
						if($value['label'] == $smack_field_value) {
							$smack_field_array[$temp] = $value;
						break;
						}
					}
					//Selectbox to checkbox values
					if(strstr($smack_field_value,'{')){
						$get_fieldvalue = json_decode($smack_field_value);
						//Convert stdclass obj to array
						$smackData = json_decode(json_encode($get_fieldvalue),true);
						$smack_field_array[0]['label'] = $smackData['label'];
						$smack_field_array[0]['value'] = $smackData['value'];
					}
				}
			}
		}


		if(isset($field['taxonomy']['label']))
			$type = $field['taxonomy']['label'];
		else
			$type = 'category';
		//End Switch

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$taxo_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_array,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_params' => $taxonomy_choices,
			'field_pagetype' => $page_type,
			'create_terms' => $field['create_terms'],
			'taxonomy_type' => $type,
			'appearance' => $field['appearance']['label']
		);

		if($source == 'via_group'){
			return $taxo_field_array;
		}

		?><div 
				id="<?php echo esc_attr($taxonomys_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($taxo_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
			
		
	}

	public function get_all_terms($filter_taxonomy){
		global $wpdb;
		$terms_arg = array('taxonomy' => $filter_taxonomy,
						'hide_empty' => false,
		);
		$get_all_terms = get_terms($terms_arg);
	
		$all_terms = [];
		$temp = 0;
		foreach($get_all_terms as $each_term){
			$all_terms[$temp]['label'] = $each_term->name;
			$all_terms[$temp]['id'] = $each_term->term_id;
			$temp++;
		}
		return $all_terms;
	}
}