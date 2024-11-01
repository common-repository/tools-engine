<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class FieldType
 * @package Smackcoders\TOOLSENGINE
 */
class FieldType
{
	protected static $instance = null,$plugin;

	/**
	 * FieldType constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * FieldType Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	/**
	 * FieldType Hooks
	 */
	public static function doHooks(){
		add_action('wp_ajax_getFieldTypes', array(self::$instance,'getFieldTypes'));
	}

	/**
	 * Get field types
	 */
	public static function getFieldTypes(){
		$fieldTypes = array(array('basic'=> array(
			'Text' => 'text',
			'Text Area' => 'textarea',
			'Number' => 'number',
			'Range' => 'range',
			'Email' => 'email',
			'Url' => 'url',
			'Password' => 'password'
		)),array('content'=> array(
			'Image' => 'image',
			'File' => 'file',
			'Wysiwyg Editor' => 'wysiwyg_editor',
			'oEmbed' => 'oembed',
			'Gallery' => 'gallery'
		)),array('choice'=>array(
			'Select' => 'select',
			'Checkbox' => 'checkbox',
			'Radio Button' => 'radio',
			'Button Group' => 'button_group',
			'True / False' => 'true_false'
		)),array('relational'=>array(
			'Link' => 'link',
			'Post Object' => 'post_object',
			'Page Link' => 'page_link',
			'Relationship' => 'relationship',
			'Taxonomy' => 'taxonomy',
			'User' => 'user'
		)),array('jquery' => array(
			'Google Map' => 'google_map',
			'Date Picker' => 'date_picker',
			'Color Picker' => 'color_picker',
			'Date Time Picker' => 'date_time_picker',
			'Time Picker' => 'time_picker'
		)),array('layout' => array(
			'Message' => 'message',
			'Accordion' => 'accordion',
			'Tab' => 'tab',
			'Group' => 'group',
			'Repeater' => 'repeater',
			'Flexible Content' => 'flexible_content',
			'Clone' => 'clone'
		)
	));
		$wordpress_value = self::$instance->formattedArray($fieldTypes);
		$response = $wordpress_value ;
		echo wp_json_encode($response);
		wp_die();
	}

	/**
	 * FieldType Instances
	 */
	public function formattedArray($staticValue){
		if (is_array($staticValue) || is_object($staticValue)){
			foreach($staticValue as $sKey=>$sVal){
				foreach($sVal as $tmpKey=>$tmpVal){
					foreach($tmpVal as $fKey=>$fValues){
						$formattedArray[$tmpKey][$fKey] = array('label' => $fKey,
							'name' => $fValues			
						);
					}
				}
			}
		}
		return $formattedArray;
	}

}