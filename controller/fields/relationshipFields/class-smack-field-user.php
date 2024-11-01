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

class SmackFieldUser
{
	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function render_user_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instructions, $field_array, $page_type, $source){
		$users_id = "tools-engine-" . $field['field_index'];
		$get_users = self::$instance->get_all_users($field['filter_by_role']);
		$select_multiple = $field['select_multiple_values'] ? true : false;
		if($field['allow_null'] == '' && empty($field_array)){
			if(!is_array($smack_field_value)){
			if(isset($get_users[0]['options'])){
				$first_level = $get_users[0]['options'];
				if(isset($first_level[0])){
					$field_array[0] = $first_level[0];
				}
			}
		}
			//For repeater
			if(strstr($smack_field_name,'wp-smack-repeaterField')){
				if(isset($get_users[0]['options'])){
					$first_level = $get_users[0]['options'];
					if(isset($first_level[0])){
						$field_array[0][0] = $first_level[0];
					}
				}
			}
		}

		if ($field['required'] === true && empty($smack_field_value)){
			$required_message = true;
		}
		else{
			$required_message = false;
		}

		$user_field_array = array(
			'field_name' => $smack_field_name,
			'field_label' => $smack_field_label,
			'field_value' => $field_array,
			'field_required' => $field['required'],
			'field_required_alert' => $required_message,
			'field_instructions' => $smack_field_instructions,
			'field_multiple' => $select_multiple,
			'field_null' => $field['allow_null'],
			'field_params' => $get_users,
			'field_pagetype' => $page_type,
		);
		
		if($source == 'via_group'){
			return $user_field_array;
		}

		?><div 
				id="<?php echo esc_attr($users_id) ?>" 
				data-params="<?php echo htmlspecialchars(json_encode($user_field_array), ENT_QUOTES, 'UTF-8'); ?>" >
			</div>
		<?php
	}

	public function get_all_users($filter_roles){
		global $wpdb;
		global $wp_roles;
        $all_roles = $wp_roles->role_names;
		$all_users_array = [];
		$temps = 0;

		//filter by roles
		if(!empty($filter_roles)){
			$filter_by_roles = array_column($filter_roles, 'value');
			$all_roles = array_intersect($all_roles, $filter_by_roles);
		}
	
		foreach($all_roles as $each_role){
			$args = array(
				'role'    => $each_role,
				'orderby' => 'ID',
				'order'   => 'ASC'
			);
			$all_users = get_users( $args );
	
			$all_users_label_value = [];
			$temp = 0;
			foreach($all_users as $per_user){
				$user_name = $per_user->data->user_login .'('. $per_user->data->display_name . ')';
				$user_id = $per_user->data->ID;

				$all_users_label_value[$temp]['label'] = $user_name;
				$all_users_label_value[$temp]['value'] = $user_id;
				$temp++;
			}

			$all_users_array[$temps]['label'] = $each_role;
			$all_users_array[$temps]['options'] =  $all_users_label_value;
			$temps++;
		}
		return $all_users_array;
	}
}