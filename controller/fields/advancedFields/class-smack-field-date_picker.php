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

class SmackFieldDate
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_date_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type){
		//$datepicker_id = "tools-engine-datepicker-" . $field['key'];
		$datepicker_id = "tools-engine-" . $field['field_index'];

		if(!empty($field['display_format']) && !empty($smack_field_value)){ 
			$date_timestamp = strtotime($smack_field_value);
			$smack_field_value = date($field['display_format'], $date_timestamp);
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
				
		}else{
			$datepicker_display_format = 'MM/dd/yyyy';
		}

		?><div 
				id="<?php echo esc_attr($datepicker_id) ?>" 
				data-name="<?php echo esc_attr($smack_field_name) ?>" 
				data-label="<?php echo esc_attr($smack_field_label) ?>" 
				data-value="<?php echo esc_attr($smack_field_value) ?>" 
				data-instructions="<?php echo esc_attr($smack_field_instructions) ?>"
				data-display-format="<?php echo esc_attr($datepicker_display_format) ?>"
				data-page-type="<?php echo esc_attr($page_type) ?>">
			</div>
		<?php
	}
}