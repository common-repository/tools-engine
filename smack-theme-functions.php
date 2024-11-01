<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;

class TEFunctions {
    private static $instance = null;

    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function get_te_field($fieldname, $post_id = null){
        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
       
        $result = self::$instance->returnFieldValues($post_id, $fieldname, 'array', 'basic', $post_type);

        return $result;
    }

    public function get_post_id_type($post_id){
      
        if(!empty($post_id)){
            if(is_numeric($post_id)){
                $post_type = 'post';
            }
            else{
                //user_12, term_23, comment_67
                $get_postType = explode('_', $post_id);
                $post_type = $get_postType[0];
                $post_id = $get_postType[1];
            }
        }
        
        if(empty($post_id)){
            global $post;
            $post_id = $post->ID;
            $post_type = 'post';
        }

        if(empty($post_id)){
            $post_id = get_queried_object();
            if(is_object($post_id)){

                // user
                if( isset($post_id->roles, $post_id->ID) ) {
                    $post_id = $post_id->ID;
                    $post_type = 'user';
                
                // term
                } elseif( isset($post_id->taxonomy, $post_id->term_id) ) {
                    $post_id = $post_id->term_id ;
                    $post_type = 'term';
                
                // comment
                } elseif( isset($post_id->comment_ID) ) {
                    $post_id = $post_id->comment_ID;
                    $post_type = 'comment';
                
                // default
                } else {   
                    $post_id = 0;
                }
            }
        }

        $result['post_id'] = $post_id;
        $result['post_type'] = $post_type;
       
        return $result;
    }

    public static function the_te_field($fieldname, $post_id = null){

        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
       
        $result = self::$instance->returnFieldValues($post_id, $fieldname, 'string', 'basic', $post_type);

        if(is_array($result)){
            $result = implode(', ', $result);
        }

        return $result;
    }

    public static function get_te_fields($post_id = null){
    
        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
    
        global $wpdb;

        if($post_type == 'post'){
            $get_all_ucf_metakeys = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%wp-smack-%' AND post_id = $post_id ", ARRAY_A);
        }
        elseif($post_type == 'user'){
            $get_all_ucf_metakeys = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE '%wp-smack-%' AND user_id = $post_id ", ARRAY_A);
        }
        elseif($post_type == 'term'){
            $get_all_ucf_metakeys = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}termmeta WHERE meta_key LIKE '%wp-smack-%' AND term_id = $post_id ", ARRAY_A);
        }
        elseif($post_type == 'comment'){
            $get_all_ucf_metakeys = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}commentmeta WHERE meta_key LIKE '%wp-smack-%' AND comment_id = $post_id ", ARRAY_A);
        }
        
        if(empty($get_all_ucf_metakeys)){
            return;
        }

        $get_all_values = [];
        foreach($get_all_ucf_metakeys as $all_ucf_values){
            $get_ucf_metakey = explode('wp-smack-', $all_ucf_values['meta_key']);
            $get_ucf_metavalue = self::$instance->returnFieldValues($post_id, $get_ucf_metakey[1], 'array', 'basic', $post_type);
            $get_all_values[$get_ucf_metakey[1]] = $get_ucf_metavalue;
        } 
        return $get_all_values;
    }

    public function returnFieldValues($post_id, $field, $return_type, $source = null, $post_type = null){
        
        global $wpdb;
        
        if($source == 'basic'){
            if($post_type == 'term'){
                $get_value = get_term_meta($post_id, 'wp-smack-'.$field, true);
            }
            elseif($post_type == 'user'){
                $get_value = get_user_meta($post_id, 'wp-smack-'.$field, true);
            }
            elseif($post_type == 'comment'){
                $get_value = get_comment_meta($post_id, 'wp-smack-'.$field, true);
            }
            else{
                $get_value = get_post_meta($post_id, 'wp-smack-'.$field, true);                
            }
        }
        elseif($source == 'repeater'){
           
            $group_id = $wpdb->get_var("SELECT post_parent FROM {$wpdb->prefix}posts WHERE post_name = '$field' AND post_type = 'smack-field' ");
           
            $get_group_name = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE ID = $group_id AND post_type = 'smack-field' ");
            $temp = 'wp-smack-'.$get_group_name;

            $get_rows = get_post_meta($post_id, $temp, true);    
            for($i = 0; $i < $get_rows; $i++){
           
                $field_meta_key = 'wp-smack-'. $get_group_name . '_' . $i . '_'. $field; 
                        
                if($post_type == 'term'){
                    $get_value[$i] = get_term_meta($post_id, $field_meta_key, true);
                }
                elseif($post_type == 'user'){
                    $get_value[$i] = get_user_meta($post_id, $field_meta_key, true);
                }
                elseif($post_type == 'comment'){
                    $get_value[$i] = get_comment_meta($post_id, $field_meta_key, true);
                }
                else{
                    $get_value[$i] = get_post_meta($post_id, $field_meta_key, true);
                }
            }
        }
        elseif($source == 'group'){
            $group_id = $wpdb->get_var("SELECT post_parent FROM {$wpdb->prefix}posts WHERE post_name = '$field' AND post_type = 'smack-field' ");
            $get_group_name = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE ID = $group_id AND post_type = 'smack-field' ");
            $field_meta_key = 'wp-smack-'. $get_group_name . '_' . $field;
          
            if($post_type == 'term'){
                $get_value = get_term_meta($post_id, $field_meta_key, true);
            }
            elseif($post_type == 'user'){
                $get_value = get_user_meta($post_id, $field_meta_key, true);
            }
            elseif($post_type == 'comment'){
                $get_value = get_comment_meta($post_id, $field_meta_key, true);
            }
            else{
                $get_value = get_post_meta($post_id, $field_meta_key, true);
            }
        }
        
        if(empty($get_value)){
            return;
        }
        $get_postcontent = $wpdb->get_var("SELECT post_content FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_name = '$field' ");
        $get_content = unserialize($get_postcontent);
        $get_type = $get_content['type'];
        
        if($get_type == 'textarea') {

            switch($get_content['textAreaNewLines']['label']) {
                case 'Automatically add <br>' :
                    {
                        $get_value = wpautop($get_value);
                        $get_value = str_replace(array('<p>','</p>'),"",$get_value);
                        return $get_value;
                    }
                case 'Automatically add paragraphs' :
                    {
                        $get_value = wpautop($get_value);
                        return $get_value;
                    }
                default :
                    {
                        return $get_value;
                    }

            }            
        }
        if($get_type == 'image'){
            if(!empty($get_content['return_format'])){
                if (is_array($get_value)){
                    $get_value = $get_value[0];
                }
                if($get_content['return_format'][0]['imageArray']){
                    $image_arr['url'] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $get_value AND post_type = 'attachment' ");
                    $image_arr['alt'] = get_post_meta($get_value, '_wp_attachment_image_alt', true);
                    $image_arr['id'] = $get_value;
                    $get_value = $image_arr;
                }
                elseif($get_content['return_format'][1]['imageUrl']){
                    $get_value = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $get_value AND post_type = 'attachment' ");
                }
            }
        }
        elseif($get_type == 'checkbox'){            
            if(is_array($get_value)){ 
                foreach($get_value as $repkey => $value){
                    //For repeater
                    if(is_array($value)){
                        foreach($value as $item){
                            if(in_array("Toggle all",$value)) {
                                $key = array_search('Toggle all',$value);
                                unset($value[$key]);
                            }                       
                            $get_value[$repkey] = $value;
                        }                        
                    }    
                    else {
                 if(in_array("Toggle all",$get_value)) {
                $key = array_search('Toggle all',$get_value);
                unset($get_value[$key]);
                 }
                }
                 }
                }                   
            
            if(!empty($get_content['return_format'])){

                foreach($get_value as $repkey => $data){
                    if(is_array($data)){
                        // For repeater
                        if($get_content['return_format'][0]['return_value'] || $get_content['return_format'][1]['return_label'] ) {
                            $get_value[$repkey] = implode(',',$data); 
                        }
                        else{
                        // For repeater,Return format both (changed the value as twice in array)
                        foreach($data as $key => $item){
                            $get_value[$repkey][$key] = $item . " " . $item;                            
                        }                                    
                        }
                    }
                    else {
                        // Basic checkbox control
                        if($get_content['return_format'][0]['return_value'] || $get_content['return_format'][1]['return_label'] ) {
                            return $get_value;
                        }
                        else {
                            foreach($get_value as $item) {
                                $both[] = $item ." ".$item;
                            }
                            $get_value = $both;
                            return $get_value;
                        }
                    }
                }
                //For repeater (return format both property)
                foreach($get_value as $repkey => $changed_data){
                    if(is_array($data) && $get_content['return_format'][2]['return_both']){
                        $get_value[$repkey] = implode(',',$changed_data);
                    }
                }                                
                return $get_value;
                
                
            }
        }
        elseif($get_type == 'radiobutton' || $get_type == 'buttongroup') {
            if($get_content['return_format'][0]['return_value']) {
                return $get_value;
            }
            elseif($get_content['return_format'][1]['return_label']) {
                return $get_value;
            }
            else {
                    $get_value = $get_value ." ". $get_value;
                }
        }
        elseif($get_type == 'select') {            
            $both = [];
            if(!is_array($get_value)) {
                $selectitems = json_decode($get_value);
                // Convert stdclass object to array
                $getitems = json_decode(json_encode($selectitems),true);
            if(!empty($get_content['return_format'])){
                if($get_content['return_format'][0]['return_value']) {
                    $select_value[] = $getitems['value'];
                    return $select_value;
                }
                if($get_content['return_format'][1]['return_label']) {
                    $select_label[] = $getitems['label'];
                    return $select_label;
                }
                if($get_content['return_format'][2]['return_both']) {
                    foreach($getitems as $item) {
                        $both[] = $item ." ".$item; 
                        if(!$get_content["select_multiple_values"]) {
                            break;
                        }
                    }
                   
                    return $both;
                }
            }
            }    
            else {            
                if($get_content['return_format'][0]['return_value'] || $get_content['return_format'][1]['return_label']) {
                    foreach($get_value as $repkey => $item) {
                        //For repeater
                        if(is_array($item)){
                            if($get_content["select_multiple_values"]) {
                                $multiple_select_rep = implode(',',$item);
                                $both[$repkey] = $multiple_select_rep;
                            }
                            else {
                            foreach($item as $data) {
                                $both[$repkey] = $data;
                                break;
                            }
                        }
                        }
                        else {
                            $both[] = $item;
                            if(!$get_content["select_multiple_values"]) 
                            break;                      
                        }
                    }
                    
                    if(is_array($both) && count($both) > 1){
                        $both = implode(',',$both);
                    }
                    return $both;
                }
            
                if($get_content['return_format'][2]['return_both']) {
                    
                    foreach($get_value as $repkey => $item) {
                        //For repeater
                        if(is_array($item)){
                            if($get_content["select_multiple_values"]) {
                                foreach($item as $data){
                                    $result_array[$repkey][] = $data . " ". $data;
                                }                                
                                foreach($result_array as $key => $value){
                                     $both[$key] = implode(',',$value);
                                }
                            }
                            else {
                            foreach($item as $data) {
                                $both[$repkey] = $data . " ". $data;
                                break;
                            }
                        }
                        }
                        else {
                            $both[] = $item . " ".$item;
                            if(!$get_content["select_multiple_values"]) 
                            break;                      
                        }
                    }
                    
                    if(is_array($both) && count($both) > 1){
                        $both = implode(',',$both);
                    }   
                    return $both;                    
                }
            }
        }
        elseif($get_type == 'file'){
            //For Repeater SubFields Whose Values are placed in an array
            if (is_array($get_value)){
                $get_value = $get_value[0];
            }
            if(!empty($get_content['return_format'])){
                if($get_content['return_format'][0]['fileArray']){
                    $file_arr['url'] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $get_value AND post_type = 'attachment' ");
                    $file_arr['filename'] = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $get_value AND post_type = 'attachment' ");
                    $file_arr['id'] = $get_value;
                    $get_value = $file_arr;
                }
                elseif($get_content['return_format'][1]['fileUrl']){
                    $get_value = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $get_value AND post_type = 'attachment' ");
                }
            }
        }
        elseif($get_type == 'datepicker' || $get_type == 'datetimepicker' || $get_type == 'timepicker'){
            if(!empty($get_content['return_format'])){
                $date_timestamp = strtotime($get_value);
                $get_value = date($get_content['return_format'], $date_timestamp);
            }
        }
        elseif($get_type == 'user'){
            //For Repeater SubFields Whose Values are placed in an array
            if (is_array($get_value[0])){
                $get_value = $get_value[0];
            }
            if(!empty($get_content['return_format'])){
                if($get_content['return_format'][0]['userarray']){
                    $temp = 0;
                    foreach($get_value as $user_id){
                        $user_details = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}users WHERE ID = $user_id",ARRAY_A);
            
                        $user_arr[$temp]['user_login'] = $user_details[0]['user_login'];
                        $user_arr[$temp]['user_nicename'] = $user_details[0]['user_nicename'];
                        $user_arr[$temp]['user_email'] = $user_details[0]['user_email'];
                        $user_arr[$temp]['display_name'] = $user_details[0]['display_name'];
                        $user_arr[$temp]['ID'] = $user_details[0]['ID'];
                        $temp++;
                    }
                    $get_value = $user_arr;
                }
                elseif($get_content['return_format'][1]['userobject']){
                    $user_arr = [];
                    foreach($get_value as $user_id){
                        $user_arr[] = get_user_by('ID',$user_id);
                    }
                    $get_value = $user_arr;
                }
                else{
                    if($return_type == 'string'){
                        $get_value = implode(', ', $get_value);
                    }
                }
            }
        }
        elseif($get_type == 'link'){
            if(!empty($get_content['return_value'])){
                if($get_content['return_value'][0]['linkarray']){
                    if ($get_value[0]['url']){
                        $link_arr['url'] = $get_value[0]['url'];
                        $link_arr['title'] = $get_value[1]['title'];
                        $link_arr['target'] = $get_value[2]['target'];
                        $get_value = $link_arr;
                    }
                    else{
                        $link_arr['url'] = $get_value[0][0]['url'];
                        $link_arr['title'] = $get_value[0][1]['title'];
                        $link_arr['target'] = $get_value[0][2]['target'];
                        $get_value = $link_arr;
                    }
                }
                elseif($get_content['return_value'][1]['linkurl']){
                    if ($get_value[0]['url']){
                        $get_value = $get_value[0]['url'];
                    }
                    else{
                        $get_value = $get_value[0][0]['url'];
                    }
                }
            }
        }
        elseif($get_type == 'taxonomy'){                 
            if(!empty($get_content['return_value'])){
                if($get_content['return_value'][0]['termobject']){
                    $term_arr = [];
                    if(is_array($get_value)){
                        //Appearance select                        
                        if(array_key_exists('value',$get_value)){
                            $term_arr[] = get_term($get_value['value']);
                        }
                        else {
                            //Appearance checkbox and multiselect
                            foreach($get_value as $term_id){
                            $term_arr[] = get_term($term_id);
                           }
                        }                                                
                }
                else {
                    //Appearance radio                    
                    $id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$get_value'");
                    $term_arr[] = get_term($id);                    
                }   
                $get_value = $term_arr;                                 
                }
                elseif($get_content['return_value'][1]['termid']){
                    $get_value = $get_value;
                }
            }
        }
        elseif($get_type == 'gallery'){
            if(!empty($get_content['return_format'])){
                if($get_content['return_format'][0]['galleryArray']){ 
                    //For Repeater SubFields Whose Values are placed in an array
                    if (is_array($get_value)){
                        $get_value = $get_value[0];
                    } 
                    $get_gallery_ids = explode(',', $get_value);

                    $temp = 0;
                    foreach($get_gallery_ids as $gallery_id){
                        $gallery_arr[$temp]['url'] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $gallery_id AND post_type = 'attachment' ");
                        $gallery_arr[$temp]['alt'] = get_post_meta($gallery_id, '_wp_attachment_image_alt', true);
                        $gallery_arr[$temp]['caption'] = $wpdb->get_var("SELECT post_name FROM {$wpdb->prefix}posts WHERE ID = $gallery_id AND post_type = 'attachment' ");
                        $gallery_arr[$temp]['description'] = $wpdb->get_var("SELECT post_content FROM {$wpdb->prefix}posts WHERE ID = $gallery_id AND post_type = 'attachment' ");
                        $gallery_arr[$temp]['id'] = $gallery_id; 
                        $temp++;
                    }
                    $get_value = $gallery_arr;

                }
                elseif($get_content['return_format'][1]['galleryUrl']){
                    $get_gallery_ids = explode(',', $get_value);

                    $temp = 0;
                    foreach($get_gallery_ids as $gallery_id){
                        $gallery_url[$temp] = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $gallery_id AND post_type = 'attachment' ");
                        $temp++;
                    }
                    $get_value = $gallery_url;
                }
                else{
                    $get_value = explode(',', $get_value);
                }
            }
        }
        else{
            if(is_array($get_value)){
                if($return_type == 'string'){
                    $get_value = implode(', ', $get_value);
                }
            }
        }  
        return $get_value;
        
    }

    public static function check_rows($group_name){
        global $wpdb;
        $group_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_name = '$group_name' ");
        
        $check_sub_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'smack-field' AND post_parent = $group_id ", ARRAY_A);
        
        if(!empty($check_sub_fields) && is_array($check_sub_fields)){
            return true;
        }
        else{
            return false;
        }
    }
    
    public static function get_te_sub_field($field_name, $post_id = null){
        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
      
        $result = self::$instance->returnFieldValues($post_id, $field_name, 'array', 'group', $post_type);

        return $result;
    }
    public static function get_te_repsub_field($field_name, $post_id = null){
        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
      
        $result = self::$instance->returnFieldValues($post_id, $field_name, 'array', 'repeater', $post_type);  
        // Testing Additions
        foreach ($result as $res){
            if (is_array($res) && $res['url']){
                return $result;
            }
        }

        if ((is_array($result) && $result['url'])){
            return $result;
        }
        
        // if(is_array($result)){
        //     $result = implode(', ', $result);            
        // }

        return $result;
            
    }

    public static function the_te_sub_field($fieldname, $post_id = null){

        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        $post_id = $get_post_id_type['post_id'];
        $post_type = $get_post_id_type['post_type'];
       
        $result = self::$instance->returnFieldValues($post_id, $fieldname, 'string', 'group', $post_type);
        if(is_array($result)){
            $result = implode(',', $result);
        }        
        return $result;
        
    }
    public static function the_te_repsub_field($fieldname, $post_id = null){
        $get_post_id_type = self::$instance->get_post_id_type($post_id);
        
        $post_id = $get_post_id_type['post_id'];
        
        $post_type = $get_post_id_type['post_type'];        
       
        $result = self::$instance->returnFieldValues($post_id, $fieldname, 'string', 'repeater', $post_type);
        
        if(is_array($result)){
            $result = implode(', ', $result);            
        }
        return $result;
    }
}

TEFunctions::getInstance();