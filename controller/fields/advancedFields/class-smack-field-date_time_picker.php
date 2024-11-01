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

class SmackFieldDateTime
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_datetime_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		
		if($field['display_type'][0]['date']){
			$display_type = 'date';
		}
		elseif($field['display_type'][1]['time']){
			$display_type = 'time';
		}
		elseif($field['display_type'][2]['both']){
			$display_type = 'datetime';
		}
		else{
			$display_type = 'datetime';	
		}

		$index = explode('-', $field['field_index']);

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}	

		if($display_type == 'datetime'){
			//$datetimepicker_id = "tools-engine-" . $field['field_index'];
			$datetimepicker_id = "tools-engine-datetimepicker-" . $index[0];

			if(empty($smack_field_value)){
				$currentDate = date("Y-m-d");
				$currentTime = date("H:i:s");
				$smack_field_value =  date("Y-m-d H:i:s", strtotime($currentDate . $currentTime));
			}

			if(!empty($field['display_format']) && !empty($smack_field_value)){ 
				if(is_array($smack_field_value)){
					
					foreach($smack_field_value as $smackkey => $smackvalue){
					
						$date_timestamp = strtotime($smackvalue);
						$smack_field_value[$smackkey] = date($field['display_format'], $date_timestamp);
					}
					// $date_timestamp = strtotime($smack_field_value[0]);
				}
				else{
					$date_timestamp = strtotime($smack_field_value);
					$smack_field_value = date($field['display_format'], $date_timestamp);
				}
				
			}
			if(!empty($field['display_format'])){
				if($field['display_format'] == 'd/m/y g:i a'){
					$datetimepicker_display_date = 'DD/MM/YY';
					$datetimepicker_display_time = 'h:mm a';
				}
				elseif($field['display_format'] == 'm/d/y g:i a'){
					$datetimepicker_display_date = 'MM/DD/YY';
					$datetimepicker_display_time = 'h:mm a';
				}
				elseif($field['display_format'] == 'F j, Y g:i a'){
					$datetimepicker_display_date = 'MMMM DD, yyyy';
					$datetimepicker_display_time = 'h:mm a';
				}
				elseif($field['display_format'] == 'Y-m-d H:i s'){
					$datetimepicker_display_date = 'yyyy-MM-DD';
					$datetimepicker_display_time = 'H:mm:ss';
				} 
			}else{
				$datetimepicker_display_date = 'MM/DD/yyyy';
				$datetimepicker_display_time = 'h:mm a';
			}

			$datetime_field_array = array(
				'field_name' => $smack_field_name,
				'field_label' => $smack_field_label,
				'field_value' => $smack_field_value,
				'field_instructions' => $smack_field_instructions,
				'field_required' => $field['required'],
				'field_required_alert' => $required_message,
				'field_display_date' => $datetimepicker_display_date,
				'field_display_time' => $datetimepicker_display_time,
				'field_display_type' => $display_type,
				'field_pagetype' => $page_type,
			);
	
			if($source == 'via_group'){
				return $datetime_field_array;
			}

			?><div 
					id="<?php echo esc_attr($datetimepicker_id) ?>" 
					data-params="<?php echo htmlspecialchars(json_encode($datetime_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
				</div>
			<?php
		}

		elseif($display_type == 'date'){
			//$datepicker_id = "tools-engine-" . $field['field_index'];
			$datepicker_id = "tools-engine-datepicker-" . $index[0];

			if(!empty($field['display_format']) && !empty($smack_field_value)){ 
				if(is_array($smack_field_value)){
					
					foreach($smack_field_value as $smackkey => $smackvalue){
					
						$date_timestamp = strtotime($smackvalue);
						$smack_field_value[$smackkey] = date($field['display_format'], $date_timestamp);
					}
				}
				else{
					$date_timestamp = strtotime($smack_field_value);
					$smack_field_value = date($field['display_format'], $date_timestamp);
				}
			}
	
			if(!empty($field['display_format'])){
				if($field['display_format'] == 'd/m/y'){
					$datepicker_display_format = 'dd/MM/yyyy';
				}
				elseif($field['display_format'] == 'm/d/y'){
					$datepicker_display_format = 'MM/dd/yyyy';
				}
				elseif($field['display_format'] == 'F j, Y'){
					$datepicker_display_format = 'MMMM d, yyyy';
				}
				elseif($field['display_format'] == 'Ymd'){
					$datepicker_display_format = 'yyyy/MM/dd';
				}
					
			}else{
				$datepicker_display_format = 'MM/dd/yyyy';
			}
	
		
			$date_field_array = array(
				'field_name' => $smack_field_name,
				'field_label' => $smack_field_label,
				'field_value' => $smack_field_value,
				'field_required' => $field['required'],
				'field_required_alert' => $required_message,
				'field_instructions' => $smack_field_instructions,
				'field_display_format' => $datepicker_display_format,
				'field_pagetype' => $page_type,
			);
	
			if($source == 'via_group'){
				return $date_field_array;
			}

			?><div 
					id="<?php echo esc_attr($datepicker_id) ?>" 
					data-params="<?php echo htmlspecialchars(json_encode($date_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
				</div>
			<?php
		}

		elseif($display_type == 'time'){
			//$timepicker_id = "tools-engine-" . $field['field_index'];
			$timepicker_id = "tools-engine-timepicker-" . $index[0];

			if(empty($smack_field_value)){
				$smack_field_value = '12:00 am';
			}
	
			if(!empty($field['display_format']) && !empty($smack_field_value)){ 
				if(is_array($smack_field_value)){
					
					foreach($smack_field_value as $smackkey => $smackvalue){
					
						$date_timestamp = strtotime($smackvalue);
						$smack_field_value[$smackkey] = date($field['display_format'], $date_timestamp);
					}
					
				}
				else{
					$smack_field_value = date($field['display_format'], strtotime($smack_field_value));
				}
				
			}
	
			if(!empty($field['display_format'])){
				if($field['display_format'] == 'g:i a'){
					$timepicker_display_format = 'h:mm a';
				}
				elseif($field['display_format'] == 'H:i:s'){
					$timepicker_display_format = 'H:mm:ss';
				}
				
			}else{
				$timepicker_display_format = 'h:mm a';
			}

			$time_field_array = array(
				'field_name' => $smack_field_name,
				'field_label' => $smack_field_label,
				'field_value' => $smack_field_value,
				'field_instructions' => $smack_field_instructions,
				'field_display_format' => $timepicker_display_format,
				'field_pagetype' => $page_type,
			);
	
			if($source == 'via_group'){
				return $time_field_array;
			}

			?><div 
					id="<?php echo esc_attr($timepicker_id) ?>" 
					data-params="<?php echo htmlspecialchars(json_encode($time_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
				</div>
			<?php
		}
	}
}