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

class SmackFieldTime
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_time_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type){
		if(empty($smack_field_value)){
			$smack_field_value = '12:00 am';
		}

		if(!empty($field['display_format']) && !empty($smack_field_value)){ 
			$smack_field_value = date($field['display_format'], strtotime($smack_field_value));
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

		//$timepicker_id = "tools-engine-timepicker-" . $field['key'];
		$timepicker_id = "tools-engine-" . $field['field_index'];

		?><div 
				id="<?php echo $timepicker_id ?>" 
				data-name="<?php echo $smack_field_name ?>" 
				data-label="<?php echo $smack_field_label ?>" 
				data-value="<?php echo $smack_field_value ?>" 
				data-instructions="<?php echo $smack_field_instructions ?>" 
				data-display-format="<?php echo $timepicker_display_format ?>"
				data-page-type="<?php echo $page_type ?>">
			</div>
		<?php
	}
}