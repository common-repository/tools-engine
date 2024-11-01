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

class SmackFieldGroup
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

	public function render_group_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $page_type, $screen, $current_screen, $postID){
		global $wpdb;
		$sub_fields = $field['sub_fields'];
		$extract_group_name = explode('wp-smack-', $smack_field_name);
		if($page_type == "post"){
			?>
				<div class="mb-3">
					
						<?php
							self::$instance->sub_group_fields_function($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions,$sub_fields,  $extract_group_name, $screen, $current_screen, $postID, $page_type);
						?>
				</div>
			<?php 
		} 	

		if($page_type == "user" || $page_type == "taxonomy-edit"){
			?>
				<table class="form-table">
					<tr>
						<th>
							<label><?php echo esc_html($smack_field_label) ?></label>
						</th>
						<td>	
							<div class="mb-3">
								<?php
									self::$instance->sub_group_fields_function($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions,$sub_fields,  $extract_group_name, $screen, $current_screen, $postID, $page_type);
								?>
							</div>
						</td>
					</tr>
				</table>
			<?php 
		}

		if($page_type == "taxonomy"){
			?>
				<div class="mb-3">
					<?php
						self::$instance->sub_group_fields_function($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions,$sub_fields,  $extract_group_name, $screen, $current_screen, $postID, $page_type);
					?>
				</div>
			<?php
		}
	}

	public function sub_group_fields_function($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions,$sub_fields,  $extract_group_name, $screen, $current_screen, $postID, $page_type){

		$sub_field_array = [];
		$temp = 1;
		$index = 0;
		foreach($sub_fields as $sub_field){
			$field_value = [];
			$field_name = "wp-smack-groupField--". $extract_group_name[1] ."--". $sub_field['name'];
			$field_meta_key =  "wp-smack-". $extract_group_name[1]  . "_" .  $sub_field['name'];
			if($screen == 'user'){
				global $user_id;
				if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_user_meta( $user_id, $smack_field_name."_".$sub_field['_name']."_postType", false );
					$field_value[$index][] = get_user_meta( $user_id, $smack_field_name."_".$sub_field['_name']."_taxonomy", false );
					$field_value[$index][] = get_user_meta( $user_id, $smack_field_name."_".$sub_field['_name'], false );

					$index++;

				}
				else{
					$field_value = get_user_meta( $user_id, $field_meta_key, true );
				}
			}
			elseif($screen == 'taxonomy'){
				$get_term_id = explode('term_', $postID);
				if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_term_meta( $get_term_id[1], $smack_field_name."_".$sub_field['_name']."_postType", false );
					$field_value[$index][] = get_term_meta( $get_term_id[1], $smack_field_name."_".$sub_field['_name']."_taxonomy", false );
					$field_value[$index][] = get_term_meta( $get_term_id[1], $smack_field_name."_".$sub_field['_name'], false );

					$index++;

				}
				else{
					$field_value = get_term_meta( $get_term_id[1], $field_meta_key, true );
				}
			}
			else{
				if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_post_meta( get_the_ID(), $smack_field_name."_".$sub_field['_name']."_postType", false );
					$field_value[$index][] = get_post_meta( get_the_ID(), $smack_field_name."_".$sub_field['_name']."_taxonomy", false );
					$field_value[$index][] = get_post_meta( get_the_ID(), $smack_field_name."_".$sub_field['_name'], false );

					$index++;

				}
				else{
					$field_value = get_post_meta( get_the_ID(), $field_meta_key, true );
				}
			}	
					
			$sub_field_array[$temp]['id'] = "tools-engine-innergroup-" . $sub_field['field_index'];
			$sub_field_array[$temp]['data_params'] = self::$smackHelperInst->render_sub_fields($screen, $current_screen, $field_name, $sub_field['label'], $field_value, $postID, $sub_field, $page_type, 'via_group');

			// self::$smackHelperInst->render_sub_fields($screen, $current_screen, $field_name, $sub_field['label'], $field_value, $postID, $sub_field, $page_type, 'via_post');
			$temp++;
		}
        $group_id = "tools-engine-" . $field['field_index'];
		
		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		?><div 
				id="<?php echo $group_id ?>"
				field_required="<?php echo $field['required'] ?>"
				field_required_alert="<?php echo $required_message ?>" 
				data-label="<?php echo $smack_field_label ?>" 
				data-name="<?php echo $smack_field_name ?>" 
				data-page-type="<?php echo $page_type ?>"	
                data-instructions="<?php echo $smack_field_instructions ?>"
				data-subfields="<?php echo htmlspecialchars(json_encode($sub_field_array), ENT_QUOTES, 'UTF-8'); ?>"
			>
			</div>
		<?php
	}
}