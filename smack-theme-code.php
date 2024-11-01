<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;

class SmackThemeCode {
    private static $instance = null;

    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function smack_theme_code($grp_id, $source = null){
        global $wpdb;
        $field_code = [];
        if($source == 'group'){
            $get_function_name = 'get_te_sub_field';
            $the_function_name =  'the_te_sub_field';
        }
        elseif($source == 'repeater'){
            $get_function_name = 'get_te_repsub_field';
            $the_function_name =  'the_te_repsub_field';
            
        }
        elseif($source == 'message'){
            $get_function_name = 'get_te_msg_field';
            $the_function_name =  'the_te_msg_field';
        }
        else{
            $get_function_name = 'get_te_field';
            $the_function_name =  'the_te_field';
        }

        $get_fields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $grp_id AND post_type = 'smack-field' ORDER BY menu_order ASC ", ARRAY_A);
        $i = 0;
        $clonefd_array = [];
        foreach($get_fields as $get_field){
            $get_content = $get_field['post_content'];
            $get_content_details = unserialize($get_content);
            $field_code[$i] = self::$instance->smack_field_code($get_field,$get_function_name,$the_function_name,$i);
            $i = $i+ 1;
        }      

        return $field_code;
    }

    public function smack_field_code($get_field,$get_function_name,$the_function_name){        
        global $wpdb;      

        $get_content = $get_field['post_content'];
        $get_content_details = unserialize($get_content);
        
        if($get_content_details['type'] == 'textfield' || $get_content_details['type'] == 'textarea' || $get_content_details['type'] == 'number' || $get_content_details['type'] == 'email' || $get_content_details['type'] == 'url' || $get_content_details['type'] == 'range' || $get_content_details['type'] == 'wysiwyg' || $get_content_details['type'] == 'radiobutton' || $get_content_details['type'] == 'buttongroup' || $get_content_details['type'] == 'datepicker' || $get_content_details['type'] == 'datetimepicker' || $get_content_details['type'] == 'timepicker' || $get_content_details['type'] == 'colorpicker' || $get_content_details['type'] == 'googlemap'){        
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = "<?php \Smackcoders\TOOLSENGINE\TEFunctions::".$the_function_name."( '" . $get_field['post_name'] . "' ); ?>";            
        }

        if ($get_content_details['type'] == 'oEmbed'){

            $oEmbed_code = '';
                if ($get_content_details['embed_size'] && $get_content_details['embed_size'][0]['width']!= null && $get_content_details['embed_size'][1]['height'] != null){
                    $oEmbed_code .= "<?php $".$get_field['post_title']." = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                    $oEmbed_code .= "<?php if ($".$get_field['post_title'].") : ?>\n";
                    $oEmbed_code .= "<?php  echo wp_oembed_get($".$get_field['post_name'].", array('width' =>".$get_content_details['embed_size'][0]['width'].",'height' => ".$get_content_details['embed_size'][1]['height'].")); ?>\n";
                    $oEmbed_code .= "<?php endif; ?>\n";
                }
                else {
                    $oEmbed_code .= "<?php $".$get_field['post_title']." = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                    $oEmbed_code .= "<?php if ($".$get_field['post_title'].") : ?>\n";
                    $oEmbed_code .= "<?php  echo wp_oembed_get($".$get_field['post_name']."); ?>\n";
                    $oEmbed_code .= "<?php endif; ?>\n";
                }
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] =  $oEmbed_code;
        }

        if($get_content_details['type'] == 'password'){
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = "<?php $" . $get_field['post_excerpt'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$the_function_name."( '" . $get_field['post_name'] . "' ); ?>";
        }

        if($get_content_details['type'] == 'image'){
            $image_code = '';
            if($get_content_details['return_format'][0]['imageArray']){
                $image_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $image_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $image_code .= "    <img src=\"<?php echo esc_url($". $get_field['post_name']. "['url']); ?>\" alt=\"<?php echo esc_attr($". $get_field['post_name']."['alt']); ?>\" />\n";
                $image_code .= "<?php endif; ?>";
            }
            elseif($get_content_details['return_format'][1]['imageUrl']){
                $image_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '".$get_field['post_name']."' )) : ?>\n";
                $image_code .= "    <img src=\"<?php \Smackcoders\TOOLSENGINE\TEFunctions::". $the_function_name."( '". $get_field['post_name'] ."'); ?>\" />\n";
                $image_code .= "<?php endif; ?>";
            }
            else{
                $image_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name. "( '" . $get_field['post_name'] . "' ); ?>\n";
                $image_code .= "<?php $"."size = 'full'; ?>\n";
                $image_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $image_code .= "    <?php echo wp_get_attachment_image( $". $get_field['post_name']. ", $" ."size ); ?>\n";
                $image_code .= "<?php endif; ?>";
            }
            // $field_code[$get_field['post_name']] = $image_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $image_code;
        }

        if($get_content_details['type'] == 'file'){
            $file_code = '';
            if($get_content_details['return_format'][0]['fileArray']){
                $file_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $file_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $file_code .= "     <a href=\"<?php echo esc_url($". $get_field['post_name']. "['url']); ?>\"><?php echo esc_html( $".$get_field['post_name']."['filename']); ?></a>\n";
                $file_code .= "<?php endif; ?>";
            }
            elseif($get_content_details['return_format'][1]['fileUrl']){
                $file_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '".$get_field['post_name']."' )) : ?>\n";
                $file_code .= "     <a href=\"<?php \Smackcoders\TOOLSENGINE\TEFunctions::".$the_function_name."( '". $get_field['post_name'] ."'); ?>\">Download File</a>\n";
                $file_code .= "<?php endif; ?>";
            }
            else{   
                $file_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name. "( '" . $get_field['post_name'] . "' ); ?>\n";
                $file_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $file_code .= "     <?php $"."url = wp_get_attachment_url( $". $get_field['post_name']. " ); ?>\n";
                $file_code .= "     <a href=\"<?php echo esc_url( $"."url ); ?>\">Download File</a>\n";
                $file_code .= "<?php endif; ?>";
            }
            // $field_code[$get_field['post_name']] = $file_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $file_code;
        }

        if($get_content_details['type'] == 'select' || $get_content_details['type'] == 'checkbox'){
            $choice_code = '';
            $choice_code .= "<?php $" . $get_field['post_title'] . "_values = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name. "( '" . $get_field['post_name'] . "' ); ?>\n";
            $choice_code .= "<?php if ( $".$get_field['post_title']."_values ) : ?>\n";
            $choice_code .= "<?php if( is_array($".$get_field['post_name']."_values) ) : ?>\n";                    
            $choice_code .= "   <?php foreach ( $".$get_field['post_title']."_values as $". $get_field['post_name']."_value ) : ?>\n";
            $choice_code .= "       <?php echo esc_html( $".$get_field['post_name']."_value ); ?>\n";
            $choice_code .= "   <?php endforeach; ?>\n";
            $choice_code .= " <?php else : ?>\n";
            $choice_code .= "<?php echo esc_html($".$get_field['post_name']."_values ); ?>\n";
            $choice_code .= "<?php endif; ?>\n";
            $choice_code .= "<?php endif; ?>";

            // $field_code[$get_field['post_name']] = $choice_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $choice_code;
        }

        if($get_content_details['type'] == 'truefalse'){
            $truefalse_code = '';

            $truefalse_code .= "<?php $". $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name." ( '" . $get_field['post_name'] . "' ); ?>\n";
            $truefalse_code .= "<?php if ( $". $get_field['post_title'] . " == 1 || $". $get_field['post_excerpt'] . " == true) : ?>\n";
            $truefalse_code .= "    <?php echo 'true'; ?>\n";
            $truefalse_code .= "<?php else : ?>\n";
            $truefalse_code .= "    <?php echo 'false'; ?>\n"; 
            $truefalse_code .= "<?php endif; ?>";

            // $field_code[$get_field['post_name']] = $truefalse_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $truefalse_code;
        }

        if($get_content_details['type'] == 'link'){
            $link_code = '';            
            if($get_content_details['return_value'][0]['linkarray']){
            $link_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
            $link_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
            $link_code .= "     <a href=\"<?php echo esc_url($". $get_field['post_name'] ."['url'] ); ?>\" target=\"<?php echo esc_attr( $". $get_field['post_name'] ."['target'] ); ?>\"><?php echo esc_html( $". $get_field['post_name'] ."['title'] ); ?></a>\n";
            $link_code .= "<?php endif; ?>";
            }
            if($get_content_details['return_value'][1]['linkurl']){
                $link_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $link_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $link_code .= "     <a href=\"<?php echo esc_url($". $get_field['post_name'] ."); ?>\"><?php echo esc_html( $". $get_field['post_name'] ."); ?></a>\n";
                $link_code .= "<?php endif; ?>";
            }

            // $field_code[$get_field['post_name']] = $link_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $link_code;
        }

        if($get_content_details['type'] == 'pagelink'){
            $pagelink_code = '';
            $pagelink_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
            $pagelink_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
            $pagelink_code .= "     <a href=\"<?php echo esc_url( $". $get_field['post_name'] ."); ?>\"><?php echo esc_html( $". $get_field['post_name'] ." ); ?></a>\n";
            $pagelink_code .= "<?php endif; ?>";

            // $field_code[$get_field['post_name']] = $pagelink_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $pagelink_code;
        }

        if($get_content_details['type'] == 'user'){
            $user_code = '';
            if($get_content_details['return_format'][0]['userarray']){
                $user_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $user_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $user_code .= "     <?php foreach ( $".$get_field['post_title']." as $". $get_field['post_name']."_value ) : ?>\n";
                $user_code .= "         <a href=\"<?php echo get_author_posts_url( $". $get_field['post_name'] ."_value['ID'] ); ?>\"><?php echo esc_html( $". $get_field['post_name'] ."_value['display_name'] ); ?></a>\n";
                $user_code .= "     <?php endforeach; ?>\n";
                $user_code .= "<?php endif; ?>";
            }
            elseif($get_content_details['return_format'][1]['userobject']){
                $user_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $user_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $user_code .= "     <?php foreach ( $".$get_field['post_title']." as $". $get_field['post_name']."_value ) : ?>\n";
                $user_code .= "         <a href=\"<?php echo get_author_posts_url( $". $get_field['post_name'] ."_value->ID ); ?>\"><?php echo esc_html( $". $get_field['post_name'] ."_value->display_name ); ?></a>\n";
                $user_code .= "     <?php endforeach; ?>\n";
                $user_code .= "<?php endif; ?>";
            }
            else{
                $user_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $user_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $user_code .= "   <?php foreach ( $".$get_field['post_title']." as $"."user_id ) : ?>\n";
                $user_code .= "         <?php $". "user_data = get_userdata( $"."user_id ); ?>\n";
                $user_code .= "         <?php if ( $". "user_data ) : ?>\n";
                $user_code .= "             <a href=\"<?php echo get_author_posts_url( $"."user_id ); ?>\"><?php echo esc_html( $"."user_data->display_name ); ?></a>\n";
                $user_code .= "         <?php endif; ?>";
                $user_code .= "    <?php endforeach; ?>\n";
                $user_code .= "<?php endif; ?>";
            }

            // $field_code[$get_field['post_name']] = $user_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $user_code;
        }

        if($get_content_details['type'] == 'taxonomy'){
            $taxonomy_code = '';
            if(!empty($get_content_details['taxonomy']['value'])){
                $taxonomy_type = $get_content_details['taxonomy']['value'];
            }
            else{
                $taxonomy_type = 'category';
            }

            if($get_content_details['return_value'][0]['termobject']){
                $taxonomy_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $taxonomy_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $taxonomy_code .= "   <?php foreach ( $".$get_field['post_title']." as $"."term ) : ?>\n";
                $taxonomy_code .= "        <a href=\"<?php echo esc_url( get_term_link( $"."term ) ); ?>\"><?php echo esc_html( $". "term->name ); ?></a>\n";
                $taxonomy_code .= "   <?php endforeach; ?>\n";
                $taxonomy_code .= "<?php endif; ?>";
            }
            else{
                $taxonomy_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $taxonomy_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $taxonomy_code .= "     <?php $"."get_terms_args = array(\n";
                $taxonomy_code .= "         'taxonomy' => '".$taxonomy_type."',\n";
                $taxonomy_code .= "         'hide_empty' => 0,\n";
                $taxonomy_code .= "         'include' => $" . $get_field['post_title'] . ",\n";
                $taxonomy_code .= "     ); ?>\n";
                $taxonomy_code .= "     <?php $". "terms = get_terms( $"."get_terms_args ); ?>\n";
                $taxonomy_code .= "     <?php if ( $"."terms ) : ?>\n";
                $taxonomy_code .= "         <?php foreach ( $"."terms as $". "term ) : ?>\n";
                $taxonomy_code .= "             <a href=\"<?php echo esc_url( get_term_link( $"."term ) ); ?>\"><?php echo esc_html( $". "term->name ); ?></a>\n";
                $taxonomy_code .= "         <?php endforeach; ?>\n";
                $taxonomy_code .= "     <?php endif; ?>";
                $taxonomy_code .= "<?php endif; ?>";
            }
            // $field_code[$get_field['post_name']] = $taxonomy_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $taxonomy_code;
        }

        if($get_content_details['type'] == 'gallery'){
            $gallery_code = '';
            if($get_content_details['return_format'][0]['galleryArray']){
                $gallery_code .= "<?php $" . $get_field['post_title'] . "_images = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $gallery_code .= "<?php if ( $".$get_field['post_title']."_images ) : ?>\n";
                $gallery_code .= "      <?php foreach ( $".$get_field['post_title']."_images as $". $get_field['post_name']."_image ) : ?>\n";
                $gallery_code .= "          <a href=\"<?php echo esc_url($". $get_field['post_name']. "_image['url']); ?>\">\n";
                $gallery_code .= "              <img src=\"<?php echo esc_url($". $get_field['post_name']. "_image['url']); ?>\" alt=\"<?php echo esc_attr($". $get_field['post_name']."_image['alt']); ?>\" />\n";
                $gallery_code .= "          </a>\n";
                $gallery_code .= "          <p><?php echo esc_html( $".$get_field['post_name']."_image['caption'] ); ?></p>\n";
                $gallery_code .= "      <?php endforeach; ?>\n";
                $gallery_code .= "<?php endif; ?>";
            }
            elseif($get_content_details['return_format'][1]['galleryUrl']){
                $gallery_code .= "<?php $" . $get_field['post_title'] . "_urls = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $gallery_code .= "<?php if ( $".$get_field['post_title']."_urls ) : ?>\n";
                $gallery_code .= "      <?php foreach ( $".$get_field['post_title']."_urls as $". $get_field['post_name']."_url ) : ?>\n";
                $gallery_code .= "          <img src=\"<?php echo esc_url($". $get_field['post_name']. "_url); ?>\" />\n";
                $gallery_code .= "      <?php endforeach; ?>\n";
                $gallery_code .= "<?php endif; ?>";
            }
            else{
                $gallery_code .= "<?php $" . $get_field['post_title'] . "_ids = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $gallery_code .= "<?php $"."size = 'thumbnail'; ?>\n";
                $gallery_code .= "<?php if ( $".$get_field['post_title']."_ids ) : ?>\n";
                $gallery_code .= "      <?php foreach ( $".$get_field['post_title']."_ids as $". $get_field['post_name']."_id ) : ?>\n";
                $gallery_code .= "          <?php echo wp_get_attachment_image( $". $get_field['post_name']. "_id, $" ."size ); ?>\n";
                $gallery_code .= "      <?php endforeach; ?>\n";
                $gallery_code .= "<?php endif; ?>";
            }
            // $field_code[$get_field['post_name']] = $gallery_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $gallery_code;                
        }

        if($get_content_details['type'] == 'postobject' || $get_content_details['type'] == 'relationship'){
            $postobject_code = '';
            if($get_content_details['return_format'][0]['postobject']){
                $postobject_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $postobject_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $postobject_code .= "   <?php foreach ( $".$get_field['post_title']." as $"."post ) : ?>\n";
                $postobject_code .= "       <?php setup_postdata ( $". "post ); ?>\n";
                $postobject_code .= "       <a href=\"<?php the_permalink(); ?>\"><?php the_title(); ?></a>\n";
                $postobject_code .= "   <?php endforeach; ?>\n";
                $postobject_code .= "   <?php wp_reset_postdata(); ?>";
                $postobject_code .= "<?php endif; ?>";
            }
            else{
                $postobject_code .= "<?php $" . $get_field['post_title'] . " = \Smackcoders\TOOLSENGINE\TEFunctions::".$get_function_name."( '" . $get_field['post_name'] . "' ); ?>\n";
                $postobject_code .= "<?php if ( $".$get_field['post_title']." ) : ?>\n";
                $postobject_code .= "   <?php foreach ( $".$get_field['post_title']." as $"."post_ids ) : ?>\n";
                $postobject_code .= "       <a href=\"<?php echo get_permalink($"."post_ids); ?>\"><?php get_the_title($"."post_ids); ?></a>\n";
                $postobject_code .= "   <?php endforeach; ?>\n";
                $postobject_code .= "<?php endif; ?>";
            }
            // $field_code[$get_field['post_name']] = $postobject_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $postobject_code; 
        }
      {/*  if($get_content_details['type'] == 'message'){
            $message_code = '';
            $message_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::check_rows('".$get_field['post_name']."') ) : ?>\n";

            $final_result = self::$instance->smack_theme_code($get_field['ID'], 'message');
            foreach($final_result as $final_value){
                $message_code .=  "  ".$final_value."\n";
            }
        
            $message_code .= "<?php else : ?>\n";
            $message_code .= "<?php // no rows found ?>\n";
            $message_code .= "<?php endif; ?>";
            $field_code[$get_field['post_title']] = $message_code;
        }*/}
        if($get_content_details['type'] == 'message'){
            $message_code = '';
            $message_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::check_rows('".$get_field['post_name']."') ) : ?>\n";

            $final_result = self::$instance->smack_theme_code($get_field['ID'], 'message');
            foreach($final_result as $final_value){
                $message_code .=  "  ".$final_value['theme_code']."\n";
            }
        
            $message_code .= "<?php endif; ?>";
            // $field_code[$get_field['post_name']] = $group_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $message_code;
        }
        if($get_content_details['type'] == 'repeater'){
            $rep_code = '';
            $rep_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::check_rows('".$get_field['post_name']."') ) : ?>\n";

            $final_result = self::$instance->smack_theme_code($get_field['ID'], 'repeater');
            foreach($final_result as $final_value){
                $rep_code .=  "  ".$final_value['theme_code']."\n";
            }
        
            $rep_code .= "<?php endif; ?>";
            // $field_code[$get_field['post_name']] = $group_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $rep_code;
        }

        if($get_content_details['type'] == 'group'){
            $group_code = '';
            $group_code .= "<?php if ( \Smackcoders\TOOLSENGINE\TEFunctions::check_rows('".$get_field['post_name']."') ) : ?>\n";

            $final_result = self::$instance->smack_theme_code($get_field['ID'], 'group');
            foreach($final_result as $final_value){
                $group_code .=  "  ".$final_value['theme_code']."\n";
            }
        
            $group_code .= "<?php endif; ?>";
            // $field_code[$get_field['post_name']] = $group_code;
            $field_code['theme_code_name'] = $get_field['post_title'];
            $field_code['theme_code'] = $group_code;
        }

        if($get_content_details['type'] == 'clone'){
            $clone_index = 0;   //Used for sub fields
            $temp = 0;         //Used for total number of fields selected in clone

            $field_code['theme_code_name'] = $get_field['post_title'];
            foreach($get_content_details['fields'] as $subfields){
                foreach($subfields as $clone_fields){
                    $clonefd_array[$temp] = explode(" ",$clone_fields);                    
                  
                    //Field
                        if(count($clonefd_array[$temp]) == 2){
                            $clone_postexcerpt = $clonefd_array[$temp][0];
                            $clone_fieldtype = str_replace(array('(',')'),"",$clonefd_array[$key][1]);               
                            $getfield_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_excerpt = '$clone_postexcerpt' AND post_type = 'smack-field' ORDER BY menu_order ASC ", ARRAY_A);                
                            foreach($getfield_data as $key => $get_field){                                        
                                $clone_field_code[$clone_index]  = self::$instance->smack_field_code($get_field,$get_function_name,$the_function_name,$i);
                                $clone_index++;                                    
                            }        
                        }
                        
                    //Group (Group name does not have the space in it)
                        if(count($clonefd_array[$temp]) == 6){
                            $clone_postexcerpt = $clonefd_array[$temp][3];
                            $groupid = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_excerpt = '$clone_postexcerpt' AND post_type = 'tools-engine'");                
                            $subfields = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = $groupid AND post_type = 'smack-field' ORDER BY menu_order ASC ", ARRAY_A);                
                            foreach($subfields as $get_field){                                                
                                $clone_field_code[$clone_index]  = self::$instance->smack_field_code($get_field,$get_function_name,$the_function_name,$i);
                                $clone_index++;                                    
                            }                             
                        }
                    $temp++;
                    break;
                }
            }
           
            //Combined theme code for all fields that are selected under clone.
            foreach($clone_field_code as $data){
                $field_code['theme_code'] .=  "  ".$data['theme_code']."\n";
            }
                        
        }        
        return $field_code;
    }
}