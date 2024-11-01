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
class Attachments {
	var $view = 'add';
    protected static $instance = null,$plugin,$helperInst,$groupInst,$postInst,$fieldInst,$userInst;

	
	public function __construct()
	{

	}

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$groupInst = FieldGroup::getInstance();
            self::$helperInst = UltimateHelper::getInstance();
            self::$fieldInst=UltimateFields::getInstance();
            self::$postInst=PostView::getInstance();
            self::$userInst=Users::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
    }

	public static function doHooks(){
		add_action('admin_enqueue_scripts',			array(self::$instance, 'admin_enqueue_scripts'));
		add_filter('attachment_fields_to_edit', 	array(self::$instance, 'edit_attachment'), 10, 2);
		add_filter('attachment_fields_to_save', 	array(self::$instance, 'save_attachment'), 10, 2);
	}

	public static function admin_enqueue_scripts() {
			
		// bail early if not valid screen
		if( !self::$userInst->isScreen(array('attachment', 'upload')) ) {
			return;
		}
				
		// actions
		// if(self::$userInst->isScreen('upload') ) {
		// 	add_action('admin_footer', array(self::$instance, 'admin_footer'), 0);
		// }
	}

	public static 	function edit_attachment( $form_fields, $post ) {
			
		// vars
		$is_page = self::$userInst->isScreen('attachment');
		$post_id = $post->ID;
		$el = 'tr';
		$args = array(
			'attachment' => $post_id
		);
			
		// get field groups
		$fieldGroups = self::$groupInst->getFieldGroupsView( $args );
			
		// render
		if( !empty($fieldGroups) ) {
			
			// get smack_form_data
			ob_start();
			
			foreach( $fieldGroups as $fieldGroup ) {
				// skip current loop if group is inactive
				if(!$fieldGroup['active']){
					continue;
				}
				elseif($fieldGroup['type_rule'] == 'advanced'){
				foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

					$posttypes = $fieldGroupValues[0]['param'];
					$operator = $fieldGroupValues[1]['operator'];
					$posttype = $fieldGroupValues[2]['value']; 

					if($operator=='is equal to'){
						if($posttypes=='Attachment'){
							$fields = self::$fieldInst->getFields( $fieldGroup );
					
							// override instruction placement for modal
							if( !$is_page ) {
								$fieldGroup['instruction_placement'] = 'field';
							}
							
							// render			
							self::$postInst->renderFields( $fields, $post_id, $el, 'label' );
						}
					}
				}
			}
			}
			
			// close
			echo '<tr class="compat-field-smack-blank"><td>';
			
			$html = ob_get_contents();
			
			ob_end_clean();
			
			$form_fields[ 'smack-form-data' ] = array(
				'label' => '',
				'input' => 'html',
				'html' => $html
			);			
		}
		// return
		return $form_fields;	
	}
}