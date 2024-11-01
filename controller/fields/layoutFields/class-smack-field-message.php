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

class SmackFieldMessage
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_message_field($field, $smack_field_label, $page_type, $source){		
		$message_id = "tools-engine-" . $field['field_index'];
		$smack_field_message = $field['message'];

		if($field['escape_html']){
			$smack_field_escape_html = 'false';
		}
		else{
			$smack_field_escape_html = 'true';
		}
		if(!empty($field['new_lines']) && is_array($field['new_lines'])) {

           if($field['new_lines']['label'] == 'Automatically add <br>') 
                    {
                        $get_value = wpautop($field['message']);
                        $smack_field_message = str_replace(array('<p>','</p>'),"",$get_value);
                    }
			if($field['new_lines']['label'] ==  'Automatically add paragraphs') 
                    {
						$get_value = $field['message'];
                        $smack_field_message = wpautop($get_value);
					}
        }

		$message_field_array = array(
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_message,
			'field_escapehtml' => $smack_field_escape_html,
			'field_pagetype' => $page_type,
		);

		if($source == 'via_group'){
			return $message_field_array;
		}

		?><div 
				id="<?php echo esc_attr($message_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($message_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}
}