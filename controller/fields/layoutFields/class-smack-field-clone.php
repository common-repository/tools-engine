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
class SmackFieldClone
{
	protected static $instance = null;
	protected static $smackCloneInst = null,$smackTextInst,$smackHelperInst;

    public function __construct()
	{
		add_action('wp_ajax_smack_clone_field',array($this,'smack_clone_field'));	
	}
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$smackCloneInst = SmackFieldClone::getInstance();
            self::$smackTextInst = SmackFieldText::getInstance();
            self::$smackHelperInst = SmackFieldHelper::getInstance();
		}
		return self::$instance;
	}

    public function smack_clone_field(){
        check_ajax_referer('smack-tools-engine-key', 'securekey');
        global $wpdb;
        
        $i =0;
        $field_array =array();
        $result = array();
        $check_for_group = $wpdb->get_results("SELECT post_title,id,post_status FROM {$wpdb->prefix}posts WHERE post_type = 'tools-engine'", ARRAY_A);
        foreach($check_for_group  as $field){
            
            $field_group = $field['post_title'];
            $id = $field['id'];
            $post_status = $field['post_status'];
            if($field_group != 'Auto Draft'  && $post_status != 'trash')
            {
                $value = array();
                $value[] = $field_group;
                $value[] ='All fields from '.$field_group.' field group';
                $check_for_field = $wpdb->get_results("SELECT post_title,post_content, id FROM {$wpdb->prefix}posts WHERE post_parent = $id", ARRAY_A);
                foreach($check_for_field as $fields){
                    $post_content = unserialize($fields['post_content']);
                    $value[] = $fields['post_title'].' '.'('.$post_content['type'].')';
                }
                $i++;
                $field_array[] =$value;
            }
           
           
        }
        $l =0;
        foreach($field_array as $field_value){
            $k= 0;
            foreach($field_value as $val){
                if($val != ''){
                    if((strpos($val,'fields') !=false)|| (strpos($val,'(') !=false)){
                    $values['label']= $val;
                    $values['value']= $val;
                    // $result[] = $values;
                    $new[$k++] =$values;
                    }
                    else{
                        $label =$val;
                    }
                }
                
            }  
            $result[$l]['label'] =$label;
            $result[$l]['options']= $new;
            $l++;
        }
		echo wp_json_encode($result);
		wp_die();		
	}
    public function smackclonefieldgroupdetails($field_group){
        global $wpdb;
        $title = $field_group;
        $fields =array();
        $check_for_field = $wpdb->get_results("SELECT post_content, id FROM {$wpdb->prefix}posts WHERE post_title ='$title' AND post_status='publish'", ARRAY_A);
        foreach($check_for_field as $field){
            $id = $field['id'];
        }
        $all_fields = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_parent ='$id' AND post_status='publish'", ARRAY_A);
        foreach($all_fields as $fieldtitle){
            $fields[] = $fieldtitle['post_title'];
        }
        return $fields;
    }

    public function smack_clone_fielddetails($label){
        global $wpdb;
        $field = $label;
        $field_title =array();
        $field_details = array();
        foreach($field as $val){

            if (preg_match('/[\[\]\'^£$%&*()}{@#~?><>,|=_+¬-]/', $val))
            {
                $ex = explode("(",$val);
                $len = strlen($ex[0]);
                $field_title[] = substr($ex[0], 0, -1);
            } else
            {
                $fieldval = substr($val,16);
                $field_group = substr($fieldval, 0, -12);
                $len = strlen($field_group);
                $value = $this->smackclonefieldgroupdetails($field_group);
                $field_title = array_merge($field_title, $value);  
            }   
        }
        foreach($field_title as $title){
            $check_for_field = $wpdb->get_results("SELECT post_content, id FROM {$wpdb->prefix}posts WHERE post_title ='$title' AND post_status='publish'", ARRAY_A);
            foreach($check_for_field as $fields){
                
                $post_content = unserialize($fields['post_content']);
                
                $id = $fields['id'];
                $post_details = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE id =$id", ARRAY_A);
                foreach($post_details as $post){
                    $field_array['ID'] = $post['ID'];
                    $field_array['key'] = $post['post_name'];
                    $field_array['label'] = $post['post_title'];
                    $field_array['name'] = "smack-field[".$post['post_name']."]";
                    $field_array['prefix'] = $post['post_type'];
                    $field_array['type'] = $post_content['type'] ;
                    $field_array['value'] = "";
                    $field_array['menu_order'] = 0;
                    $field_array['parent'] = 0;
                    $field_array['_name'] = $post['post_name'];
                    $field_array['_valid'] = 1;
                    $field_array['id'] = "smack-field-".$post['post_name'];
                    $field_array['_prepare'] = 1;
                    $field_array['0'] = "";
                    $field_details[] = array_merge($field_array,$post_content);
                }
            }
        }
        return $field_details;
    }

    public function render_clone_field($field, $smack_field_name, $smack_field_label, $smack_field_value, $smack_field_instruction, $smack_field_prepend, $smack_field_append, $page_type, $screen, $current_screen, $postID){
        $clonefields = $field['fields'];
        $label = array();
        if(is_array($clonefields)){	
            foreach($clonefields as $value){
                $label[] =$value['label'];
            }
        }
        $fields_array = $this->smack_clone_fielddetails($label);
        $extract_clone_name = explode('wp-smack-', $smack_field_name);
        $sub_field_array = [];
		$temp = 1;
        $index = 0; 
        foreach($fields_array as $sub_field){
            $sub_field['name'] = explode('[',$sub_field['name']);
            $sub_field['name'] = substr(end($sub_field['name']),0,-1);
			$field_name = "wp-smack-cloneField--". $extract_clone_name[1] ."--". $sub_field['name'];
			$field_meta_key =  "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name'];
            $display = $field['display'];
        
            $display_value =$display['label'];
            if($display_value == 'Seamless(replace this fields with selected fields)'){
                $sub_field['label'] = $field['label'].$sub_field['label'];
            }
            else{
                $sub_field['label'] = $sub_field['label'];
            }
        
			if($screen == 'user'){
				global $user_id;
                if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_user_meta( $user_id, "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_postType", false );
					$field_value[$index][] = get_user_meta( $user_id, "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_taxonomy", false );
					$field_value[$index][] = get_user_meta( $user_id, "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name'], false );

					$index++;

				}
                else{
                    $field_value = get_user_meta( $user_id, $field_meta_key, true );
                }	
			}
			elseif($screen == 'taxonomy'){
                
				$get_term_id = explode('term_', $postID);
                if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_postType", false );
					$field_value[$index][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_taxonomy", false );
					$field_value[$index][] = get_term_meta( $get_term_id[1], "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name'], false );

					$index++;

				}
                else{
                    $field_value = get_term_meta( $get_term_id[1], $field_meta_key, true );
                }
				
			}
			else{
                if ($sub_field['type'] == 'relationship'){
					$field_value[$index][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_postType", false );
					$field_value[$index][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name']."_taxonomy", false );
					$field_value[$index][] = get_post_meta( get_the_ID(), "wp-smack-". $extract_clone_name[1]."_" . $sub_field['name'], false );

					$index++;

				}
                else{
                    $field_value = get_post_meta( get_the_ID(), $field_meta_key, true );
                }
			}	
           
        //     $fieldval =array();
        //     if($field_value != ''){
              
        //         foreach($field_value as $key => $val){
        //             $fieldval[] =$val;
        //         }
        //     }
        //    else{
        //      $fieldval[$temp] = '';  
        //     }
            $sub_field_array[$temp]['id'] = "tools-engine-innerclone-" .$sub_field['type'];
			$sub_field_array[$temp]['data_params'] =self::$smackHelperInst->render_sub_fields($screen, $current_screen, $field_name, $sub_field['label'], $field_value, $postID, $sub_field, $page_type, 'via_group');
          
          
            $temp++;

		}
        $clone_id = "tools-engine-" . $field['field_index'];
        	?>
        <div 
				id="<?php echo esc_attr($clone_id) ?>"  
				data-label="<?php echo esc_attr($smack_field_label) ?>" 
                data-display = "<?php echo esc_attr($display_value) ?>"
				data-name="<?php echo esc_attr($smack_field_name) ?>" 
				data-page-type="<?php echo esc_attr($page_type) ?>"
                data-instructions="<?php echo esc_attr($smack_field_instruction) ?>"
				data-subfields="<?php echo htmlspecialchars(json_encode($sub_field_array), ENT_QUOTES, 'UTF-8'); ?>"
				>
			</div>

        

		  <?php
    }
}
