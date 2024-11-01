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
class Comments {
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
        add_action( 'admin_enqueue_scripts',			array( self::$instance, 'admin_enqueue_scripts' ) );
        //add_filter('comment_form_field_comment',		array(self::$instance, 'comment_form_field_comment'), 999, 1);
        add_action( 'edit_comment', 					array( self::$instance, 'save_comment' ), 10, 1 );
		add_action( 'comment_post', 					array( self::$instance, 'save_comment' ), 10, 1 );
    }

    public static function validate_page() {
        global $pagenow;
        if( $pagenow == 'comment.php' ) { 
            return true;
        }
        // return
        return false;		
    }

    public static function admin_enqueue_scripts() {    
        // validate page
        if( ! self::$instance->validate_page() ) {
            return;
        }
        // actions
        // add_action('admin_footer',				array($this, 'admin_footer'), 10, 1);
        add_action('add_meta_boxes_comment', 	array(self::$instance, 'edit_comment'), 10, 1);
    }

    public static function edit_comment( $comment ) {
          
        $post_id = "comment_{$comment->comment_ID}";
       
        $field_groups = self::$groupInst->getFieldGroupsView(array(
            'comment' => get_post_type( $comment->comment_post_ID )
        ));
        
        if( !empty($field_groups) ) {

            foreach( $field_groups as $field_group ) {
                foreach($field_group['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

                    $posttypes = $fieldGroupValues[0]['param'];
                    $operator = $fieldGroupValues[1]['operator'];
                    $posttype = $fieldGroupValues[2]['value']; 

                    if($operator=='is equal to') {
                        if($posttypes=='Comment'){
                            if($posttype=='all' || $posttype=='Post'){
                                $fields = self::$fieldInst->getFields( $field_group );
                        
                                if($posttypes=='Comment'){
                                    // vars
                                    $o = array(
                                        'id'			=> 'smack-'.$field_group['ID'],
                                        'key'			=> $field_group['key'],
                                        //'style'			=> $field_group['style'],
                                        'label'			=> $field_group['label_placement'],
                                        'edit_url'		=> '',
                                        'edit_title'	=> __('Edit field group', 'tools-engine'),
                                        //'visibility'	=> $visibility
                                    );
                                
                                    if( $field_group['ID']  ) {
                                        
                                        $o['edit_url'] = admin_url('post.php?post=' . $field_group['ID'] . '&action=edit');
                                        
                                    }
                                    
                                    ?>
                                    <div id="smack-<?php echo esc_attr($field_group['ID']); ?>" class="stuffbox">
                                        <h3 class="hndle"><?php echo esc_html($field_group['title']); ?></h3>
                                        <div class="inside">
                                            <?php self::$postInst->renderFields( $fields, $post_id, 'div', 'label' ); ?>
                                        
                                            <script type="text/javascript">
                                            if( typeof smack !== 'undefined' ) {
                                                    
                                                smack.newPostbox(<?php echo json_encode($o); ?>);	
                                            
                                            }
                                            </script>
                                        </div>
                                    </div>
                                    <?php
                                
                                }
                            }
                        }
                    } 
                    else{
                        if($posttypes=='Comment'){
                            if($posttype=='Page'){
                                $fields = self::$fieldInst->getFields( $field_group );
                        
                                if($posttypes=='Comment'){
                                    // vars
                                    $o = array(
                                        'id'			=> 'smack-'.$field_group['ID'],
                                        'key'			=> $field_group['key'],
                                        //'style'			=> $field_group['style'],
                                        'label'			=> $field_group['label_placement'],
                                        'edit_url'		=> '',
                                        'edit_title'	=> __('Edit field group', 'tools-engine'),
                                        //'visibility'	=> $visibility
                                    );
                                
                                    if( $field_group['ID']  ) {
                                        
                                        $o['edit_url'] = admin_url('post.php?post=' . $field_group['ID'] . '&action=edit');
                                        
                                    }
                                    
                                    ?>
                                    <div id="smack-<?php echo esc_attr($field_group['ID']); ?>" class="stuffbox">
                                        <h3 class="hndle"><?php echo esc_html($field_group['title']); ?></h3>
                                        <div class="inside">
                                            <?php self::$postInst->renderFields( $fields, $post_id, 'div', 'label' ); ?>
                                        
                                            <script type="text/javascript">
                                            if( typeof smack !== 'undefined' ) {
                                                    
                                                smack.newPostbox(<?php echo json_encode($o); ?>);	
                                            
                                            }
                                            </script>
                                        </div>
                                    </div>
                                    <?php
                                
                                }
                            }
                        }
                    }  
                }
            }
        }
    }

}