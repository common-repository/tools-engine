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

class SmackFieldEmbed
{
	protected static $instance = null;

	public function __construct()
	{
		add_action('wp_ajax_smack_get_oembed_link',array($this,'smack_get_oembed_link'));	
	}

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_embed_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $source){
		$oembed_width = isset($field['embed_size'][0]['width']) ? $field['embed_size'][0]['width'] : 640;
		$oembed_height = isset($field['embed_size'][1]['height']) ? $field['embed_size'][1]['height'] : 320;
		
		$oembed_id = "tools-engine-" . $field['field_index'];

		$smack_field_frame = '';
		if(!empty($smack_field_value)){
			if(!is_array($smack_field_value)){
				$smack_field_frame = self::$instance->wp_oembed_get($smack_field_value, $oembed_width, $oembed_height);
			}
			else {
				//For repeater
				$smack_field_frame = [];
				foreach($smack_field_value as $repkey => $url){
					$smack_field_frame[$repkey] = self::$instance->wp_oembed_get($url, $oembed_width, $oembed_height);
				}
			}
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}	

        $oembed_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $smack_field_value,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_frame' => $smack_field_frame,						
			'field_instructions' => $smack_field_instructions,
			'embed_width' => $oembed_width,
			'embed_height' => $oembed_height,
			'field_pagetype' => $page_type,
		);

		if($source == 'via_group'){
			return $oembed_field_array;
		}
	
        ?>
            <div 
                id="<?php echo esc_attr($oembed_id) ?>" 
                data-params="<?php echo htmlspecialchars(json_encode($oembed_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
            </div>
        <?php
	}

	public static function wp_oembed_get( $url = '', $width = 0, $height = 0 ) {	
		$embed = '';
		$res = [];
		if($width != 0) {
			$res['width'] = $width;
		}
		if($height != 0) {
			$res['height'] = $height;
		}
		
		// get emebed
		$embed = @wp_oembed_get( $url, $res );
		
		// try shortcode
		if( !$embed ) {
			global $wp_embed;
			$embed = $wp_embed->shortcode($res, $url);
		}
		
		return $embed;
	}

	public function smack_get_oembed_link(){
		check_ajax_referer('smack-tools-engine-key', 'securekey');
		$get_embed_url = esc_url($_POST['embedUrl']);
		$embed_width = intval($_POST['embedWidth']);
		$embed_height = intval($_POST['embedHeight']);
		$embed_link = self::$instance->wp_oembed_get($get_embed_url, $embed_width, $embed_height);
		$response['embedLink'] = $embed_link;

		echo wp_json_encode($response);
		wp_die();		
	}
}