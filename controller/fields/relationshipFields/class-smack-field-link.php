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

class SmackFieldLink
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

	public function render_link_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type,$source){
		$link_id = "tools-engine-" . $field['field_index'];
		if(empty($smack_field_value)){
			$smack_field_value = array(array('url' => ''),
								array('title' => ''),
								array('target' => '')
							);
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$get_titles = self::$helperInst->get_all_post_titles('link');
		$link_field_array = array (
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_value' => json_encode($smack_field_value),
			'field_instructions' => $smack_field_instructions,
			'field_page_type' => $page_type,
			'field_options' => json_encode($get_titles),
		);
		
		if($source == 'via_group'){
			return $link_field_array;
		}
		?><div 
				id="<?php echo esc_attr($link_id) ?>" 
				data-name="<?php echo esc_attr($smack_field_name) ?>" 
				data-label="<?php echo esc_attr($smack_field_label) ?>" 
				data-value="<?php echo htmlspecialchars(json_encode($smack_field_value), ENT_QUOTES, 'UTF-8'); ?>" 
				data-instructions="<?php echo esc_attr($smack_field_instructions) ?>"
				data-params="<?php echo htmlspecialchars(json_encode($get_titles), ENT_QUOTES, 'UTF-8'); ?>"
				data-page-type="<?php echo esc_attr($page_type) ?>">
			</div>
		<?php	
	}
}