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

class SmackFieldPageLink
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

	public function render_pagelink_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $field_array, $page_type){
		//$pagelink_id = "tools-engine-pagelink-" . $field['key'];
		$pagelink_id = "tools-engine-" . $field['field_index'];
		$get_titles = self::$helperInst->get_all_post_titles('pagelink', $field['filter_by_post_type'], $field['filter_by_taxonomy']);
		?><div 
				id="<?php echo esc_attr($pagelink_id) ?>" 
				data-name="<?php echo esc_attr($smack_field_name) ?>" 
				data-label="<?php echo esc_attr($smack_field_label) ?>" 
				data-value="<?php echo htmlspecialchars(json_encode($field_array), ENT_QUOTES, 'UTF-8'); ?>" 
				data-instructions="<?php echo esc_attr($smack_field_instructions) ?>"
				data-params="<?php echo htmlspecialchars(json_encode($get_titles), ENT_QUOTES, 'UTF-8'); ?>"
				data-page-type="<?php echo esc_attr($page_type) ?>">
			</div>
		<?php
	}
}