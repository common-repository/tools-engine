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
class Taxmenu{
   //var $view = 'add';
    protected static $instance = null,$plugin,$helperInst,$groupInst,$postInst,$fieldInst;


	public function __construct()
	{

	}

	public static function getInstance() { 
		if ( null == self::$instance ) { 
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
            self::$fieldInst=UltimateFields::getInstance();
			self::$groupInst = FieldGroup::getInstance();
			self::$helperInst = UltimateHelper::getInstance();
			self::$postInst=PostView::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
    }

    public static function doHooks() { 
		// actions
        add_action('admin_enqueue_scripts',		array(self::$instance, 'adminEnqueueScripts'));
        add_action('wp_update_nav_menu',		array(self::$instance, 'update_nav_menu'));
	    //	add_filter('wp_get_nav_menu_items',		array(self::$instance, 'wp_get_nav_menu_items'), 10, 3);
        add_action('wp_nav_menu_item_custom_fields',	array(self::$instance, 'wp_nav_menu_item_custom_fields'), 10, 5);
    }
   
    public static function wp_get_nav_menu_items( $items, $menu, $args ) {
		self::$instance->set_data('nav_menu_id', $menu->term_id);
		return $items;
    }

    public static function set_data( $name, $value ) {
		self::$instance->data[ $name ] = $value;
    }
    
    public static function adminEnqueueScripts() {
		// validate screen
        if( !self::$instance->isScreen('nav-menus') ) return;
       
		add_action('admin_footer', array(self::$instance, 'adminFooter'), 1);
    }
   
    public static function wp_nav_menu_item_custom_fields( $item_id, $item, $depth, $args, $id = '' ) {
		
		// vars
		$prefix = "menu-item-smack[$item_id]";
		
		// get field groups
		$fieldGroups = self::$groupInst->getFieldGroupsView(array(
			'nav_menu_item' 		=> $item->type,
			'nav_menu_item_id'		=> $item_id,
			'nav_menu_item_depth'	=> $depth
		));
		
		// render
		if( !empty($fieldGroups) ) {
			
			// open
			echo '<div class="smack-menu-item-fields smack-fields -clear">';
			
			foreach( $fieldGroups as $fieldGroup ) {

                // skip current loop if group is inactive
                if(!$fieldGroup['active']){
                    continue;
                }
                
                foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

                    $posttypes = $fieldGroupValues[0]['param'];
                    $operator = $fieldGroupValues[1]['operator'];
                    $posttype = $fieldGroupValues[2]['value']; 

                    if($operator=='is equal to'){
                        if($posttypes=='Menu Item'){
                            if($posttype=='Mobile Menu'){
                                $menu=has_nav_menu( 'mobile' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
        
                            }
                            if($posttype=='Desktop Horizontal Menu'){
                                $menu=has_nav_menu( 'primary' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Desktop Expanded Menu'){
                                $menu=has_nav_menu( 'expanded' );
        
                                if($menu==1){
                                // $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                    self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Footer Menu'){
                                $menu=has_nav_menu( 'footer' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Social Menu'){
                                $menu=has_nav_menu( 'social' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    if( empty($fields) ) continue;
                            
                                     self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                        }
                    }
                    else{
                        if($posttypes=='Menu Item'){
                            if($posttype=='Mobile Menu'){
                                $menu=has_nav_menu( 'mobile' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
        
                            }
                            if($posttype=='Desktop Horizontal Menu'){
                                $menu=has_nav_menu( 'primary' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Desktop Expanded Menu'){
                                $menu=has_nav_menu( 'expanded' );
        
                                if($menu!=1){
                                // $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Footer Menu'){
                                $menu=has_nav_menu( 'footer' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                                    if( empty($fields) ) continue;
                                    
                                    
                                self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                            if($posttype=='Social Menu'){
                                $menu=has_nav_menu( 'social' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                        
                            if( empty($fields) ) continue;
                            
                            
                        self::$postInst->renderFields( $fields, $item_id, 'div', $fieldGroup['instruction_placement']='label' );
                                }
                            }
                        }
                    }
                }
			}
			
			// close
			echo '</div>';
			if( self::$instance->isAjax('add-menu-item') ): ?>
                <script type="text/javascript">
                (function($) {
                    smack.doAction('append', $('#menu-item-settings-<?php echo esc_attr($item_id); ?>') );
                })(jQuery);
                </script>
                <?php endif;
              
		}
    }

    public static function maybeGet( $array = array(), $key = 0, $default = null ) {
        return isset( $array[$key] ) ? $array[$key] : $default;
    }

    public static function isAjax( $action = '' ) {
        // vars
        $is_ajax = false;
        
        // check if is doing ajax
        if( defined('DOING_AJAX') && DOING_AJAX ) {
            $is_ajax = true;
        }
        
        // check $action
        if( $action && self::$instance->maybeGet($_POST, 'action') !== $action ) {  
            $is_ajax = false;
        }
        
        // return
        return $is_ajax;     
    }

    public static function update_nav_menu( $menu_id ) {
		// vars
		$post_id = self::$instance->getTermPostId( 'nav_menu', $menu_id );
		
		self::$instance->update_nav_menu_items( $menu_id );
    }
    
	public static function update_nav_menu_items( $menu_id ) {
       
		// bail ealry if not set
		if( empty($_POST['menu-item-smack']) )  return;
		
		// loop
		// foreach( $_POST['menu-item-smack'] as $post_id => $values ) {

		// }
	}
	
	public static function getTermPostId( $taxonomy, $term_id ) {
        // WP < 4.4
        if( !self::$instance->issetTermmeta() ) {
            return $taxonomy . '_' . $term_id;
        }
        // return
        return 'term_' . $term_id;
    }

    public static function issetTermmeta( $taxonomy = '' ) {
        if( $taxonomy && !taxonomy_exists($taxonomy) ) return false;
        return true;
    }		

    public static function isScreen( $id = '' ) {
        
        // bail early if not defined
        if( !function_exists('get_current_screen') ) {
            return false;
		} 
		
        // vars
        $current_screen = get_current_screen();
        // no screen
        if( !$current_screen ) {
            return false;

        } elseif( is_array($id) ) { 
            return in_array($current_screen->id, $id);
        
        // string
        } else {
            $id === $current_screen->id;
            return ($id === $current_screen->id);
        }
    }

    public static function getData($name){ 
        return isset(self::$instance->data[ $name ]) ? self::$instance->data[ $name ] : null;
    }
    
    public static function adminFooter() {
		
		$nav_menu_id = self::$instance->getData('nav_menu_id');
		$post_id = self::$instance-> getTermPostId( 'nav_menu', $nav_menu_id );
		
		// get field groups
		$fieldGroups = self::$groupInst->getFieldGroupsView(array(
			//'nav_menu' => $nav_menu_id
        ));
      
        ?>
        <div id="tmpl-menu-settings" style="display: none;">
            <?php
    
        if( !empty($fieldGroups) ) {
		 
            // loop
            foreach( $fieldGroups as $fieldGroup ) {
              
                foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

                    $posttypes = $fieldGroupValues[0]['param'];
                    $operator = $fieldGroupValues[1]['operator'];
                    $posttype = $fieldGroupValues[2]['value']; 

                    if($operator=='is equal to'){
                        if($posttypes=='Menu'){
                            if($posttype=='Mobile Menu'){
                                $menu=has_nav_menu( 'mobile' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
        
                            }
                            if($posttype=='Desktop Horizontal Menu'){
                                $menu=has_nav_menu( 'primary' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Desktop Expanded Menu'){
                                $menu=has_nav_menu( 'expanded' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Footer Menu'){
                                $menu=has_nav_menu( 'footer' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Social Menu'){
                                $menu=has_nav_menu( 'social' );
        
                                if($menu==1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }   
                        }
                    }
                    else{
                        if($posttypes=='Menu'){
                            if($posttype=='Mobile Menu'){
                                $menu=has_nav_menu( 'mobile' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
        
                            }
                            if($posttype=='Desktop Horizontal Menu'){
                                $menu=has_nav_menu( 'primary' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Desktop Expanded Menu'){
                                $menu=has_nav_menu( 'expanded' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Footer Menu'){
                                $menu=has_nav_menu( 'footer' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                            if($posttype=='Social Menu'){
                                $menu=has_nav_menu( 'social' );
        
                                if($menu!=1){
                                    $fields = self::$fieldInst->getFields( $fieldGroup );
                        
                                    echo '<div class=" menu-settings -'.esc_attr($fieldGroup['style']).'">';
                                    
                                        echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
                                    
                                        echo '<div class="smack-fields -left -clear">';
                                    
                                        self:: $postInst-> renderFields( $fields, $post_id, 'div', 'label' );
                                    
                                        echo '</div>';
                                    
                                    echo '</div>';
                                }
                            }
                        }
                    }
                }
            }   
        }
    
    ?>
    </div>
    <script type="text/javascript">
    (function($) {
        
        // append html
        $('#post-body-content').append( $('#tmpl-menu-settings').html() );
        
        $(document).on('submit', '#update-nav-menu', function() {

            // vars
            var $form = $(this);
            var $input = $('input[name="nav-menu-data"]');
            
            
            // decode json
            var json = $form.serializeArray();
            var json2 = [];
            
            
            // loop
            $.each( json, function( i, pair ) {
                
                // avoid nesting (unlike WP)
                if( pair.name === 'nav-menu-data' ) return;
                
                if( pair.name.indexOf('smack[') > -1 ) return;
                            
                // append
                json2.push( pair );
                
            });
            
            
            // update
            $input.val( JSON.stringify(json2) );
            
        });
            
            
    })(jQuery);	
    </script>
    <?php
            
    }

}
?>