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

class SmackFieldRepeater
{
	protected static $instance = null;
	protected static $smackHelperInst = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$smackHelperInst = SmackFieldHelper::getInstance();
		}
		return self::$instance;
	}
	
	public function render_repeater_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $screen, $current_screen, $postID){
        global $wpdb;
		$sub_fields = $field['sub_fields'];
		$extract_repeater_name = explode('wp-smack-', $smack_field_name);
		
		$get_repeater_rows = 0;
		if($screen == 'user'){
			global $user_id;
			$get_repeater_rows = get_user_meta( $user_id, $smack_field_name, true );
		}
		elseif($screen == 'taxonomy'){
			$get_term_id = explode('term_', $postID);
			$get_repeater_rows = get_term_meta( $get_term_id[1], $smack_field_name, true );
		}
		else{
			$get_repeater_rows = get_post_meta( get_the_ID(), $smack_field_name, true );
		}	

		$repeater_row_array = [];
		if($get_repeater_rows > 0){
			$repeater_row_array = array_fill(0, $get_repeater_rows ,0);
		}

		$sub_field_array = [];
		$sub_field_array[0] = [];
		$temp = 1;
	
		foreach($sub_fields as $sub_field){
			$field_name = "wp-smack-repeaterField--". $extract_repeater_name[1] ."--". $sub_field['name'];

			$field_value = [];
			for($i = 0; $i < $get_repeater_rows; $i++){
				$field_meta_key =  "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_" . $sub_field['name'];
				
				if($screen == 'user'){
					global $user_id;
					if ($sub_field['type'] == 'relationship'){
						$field_value[$i][] = get_user_meta( $user_id, "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_postType_" . $sub_field['name'], false );
						$field_value[$i][] = get_user_meta( $user_id, "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_taxonomy_" . $sub_field['name'], false );
						$field_value[$i][] = get_user_meta( $user_id, "wp-smack-". $extract_repeater_name[1]  . "_" . $i ."_".  $sub_field['name'], false );
					}
					else{
						$field_value[] = get_user_meta( $user_id, $field_meta_key, true );
					}
				}
				elseif($screen == 'taxonomy'){
					$get_term_id = explode('term_', $postID);
					if ($sub_field['type'] == 'relationship'){
						$field_value[$i][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_postType_" . $sub_field['name'], false );
						$field_value[$i][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_taxonomy_" . $sub_field['name'], false );
						$field_value[$i][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_repeater_name[1]  . "_" . $i ."_".  $sub_field['name'], false );
					}
					else{
						$field_value[] = get_term_meta( $get_term_id[1], $field_meta_key, true );
					}
				}
				else{
					if ($sub_field['type'] == 'relationship'){
						$field_value[$i][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_postType_" . $sub_field['name'], false );
						$field_value[$i][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_repeater_name[1]  . "_" . $i . "_taxonomy_" . $sub_field['name'], false );
						$field_value[$i][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_repeater_name[1]  . "_" . $i ."_". $sub_field['name'], false );
					}
					else{
					$field_value[] = get_post_meta( get_the_ID(), $field_meta_key, true );
					}
				}	
			}

			$sub_field_array[$temp]['id'] = "tools-engine-innerrepeater-" . $sub_field['field_index'];
			$sub_field_array[$temp]['data_params'] = self::$smackHelperInst->render_sub_fields($screen, $current_screen, $field_name, $sub_field['label'], $field_value, $postID, $sub_field, $page_type, 'via_group');

		
			$temp++;
		}

		$sub_field_array[$temp] = [];
        $repeater_id = "tools-engine-" . $field['field_index'];
		

		?><div 
				id="<?php echo $repeater_id ?>" 
				field-required =  "<?php echo $field['required'] ?>"
				repeatFieldRequiredAlert = "<?php echo $field['required'] ?>"
				data-label="<?php echo $smack_field_label ?>" 
				data-name="<?php echo $smack_field_name ?>" 
				data-page-type="<?php echo $page_type ?>"	
                data-instructions="<?php echo $smack_field_instructions ?>"
				data-subfields="<?php echo htmlspecialchars(json_encode($sub_field_array), ENT_QUOTES, 'UTF-8'); ?>"
				data-rowArray="<?php echo htmlspecialchars(json_encode($repeater_row_array), ENT_QUOTES, 'UTF-8'); ?>">
			</div>
		<?php
	}
}