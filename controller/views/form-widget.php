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
class Widgets {
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
        add_action('admin_enqueue_scripts',		array(self::$instance, 'admin_enqueue_scripts'));
		add_action('in_widget_form', 			array(self::$instance, 'edit_widget'), 10, 3);
    	add_filter('widget_update_callback', 	array(self::$instance, 'save_widget'), 10, 4);	
    }

    public static function admin_enqueue_scripts() {
		// validate screen
		if( self::$userInst->isScreen('widgets') || self::$userInst->isScreen('customize') ) {
			// valid	
		} else {
			return;	
        }
	}

    function edit_widget( $widget, $return, $instance ) {
		
		// vars
		$post_id = 0;
		$prefix = 'widget-' . $widget->id_base . '[' . $widget->number . '][smack]';
		
		
		// get id
		if( $widget->number !== '__i__' ) {
		
			$post_id = "widget_{$widget->id}";
			
		}
		
		
		// get field groups
		$field_groups = self::$groupInst->getFieldGroupsView(array(
			'widget' => $widget->id_base
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			// wrap
			echo '<div class="smack-widget-fields smack-fields -clear">';
			
			// loop
			foreach( $field_groups as $field_group ) { 
        
				foreach($field_group['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

					$posttypes = $fieldGroupValues[0]['param'];
					$operator = $fieldGroupValues[1]['operator'];
					$posttype = $fieldGroupValues[2]['value']; 

					// load fields
					if($operator=='is equal to'){
						if($posttypes=='Widget'){
							$fields = self::$fieldInst->getFields( $field_group );
							
							if( empty($fields) ) continue;
							// render
							self::$postInst->renderFields( $fields, $post_id, 'div', 'label' );
						}
					}
				}
        	}
            echo '</div>';
            if( $widget->updated ): ?>
                <script type="text/javascript">
                (function($) {
                    
                    smack.doAction('append', $('[id^="widget"][id$="<?php echo esc_attr($widget->id); ?>"]') );
                    
                })(jQuery);	
                </script>
                <?php endif;
                
        }
	}
	
    public static function save_widget( $instance, $new_instance, $old_instance, $widget ) {
		
		if( isset($_POST['wp_customize']) || !isset($new_instance['smack'])  ) return $instance;
		
		return $instance;	
	}
}