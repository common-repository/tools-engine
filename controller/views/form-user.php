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
class Users{
	var $view = 'add';
    protected static $instance = null,$plugin,$helperInst,$groupInst,$postInst,$fieldInst;
	
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
			self::$instance->doHooks();
		}
		return self::$instance;
    }
    public static function doHooks(){ 
        add_action( 'admin_enqueue_scripts',array( self::$instance, 'adminEnqueueScripts' ) );
        add_action('login_form_register', 			array(self::$instance, 'login_form_register'));
		
		// render
		add_action('show_user_profile', 			array(self::$instance, 'render_edit'));
		add_action('edit_user_profile',				array(self::$instance, 'render_edit'));
		add_action('user_new_form',					array(self::$instance, 'render_new'));
		add_action('register_form',					array(self::$instance, 'render_register'));
		
		// save
		add_action('user_register',					array(self::$instance, 'save_user'));
		add_action('profile_update',				array(self::$instance, 'save_user'));
    }

    public static function validatePage() {
		global $pagenow;
		
		// validate page
		if( $pagenow !=='profile.php' || $pagenow !=='user-edit.php' ){	
			return true;
		}
		return false;		
    }

	public static function adminEnqueueScripts() {
		if( !self::$instance->isScreen(array('profile', 'user', 'user-edit')) ) {
			return;
		}
		// validate page
		if( !self::$instance->validatePage() ) {
			return;
		}
        //add_action('edit_user_profile',array(self::$instance,'edit_profile'),10,2);   
    }

    public static function isScreen( $id = '' ) {
        if( !function_exists('get_current_screen') ) {
            return false;
        }
        
        $current_screen = get_current_screen();
        
        if( !$current_screen ) {
            return false;
        } elseif( is_array($id) ) {
            return in_array($current_screen->id, $id);
        } else {
            return ($id === $current_screen->id);
        }
    }

    public function save_user( $user_id ) {
        self::$postInst->saveFieldsMeta($user_id, 'user');
	}
    
    public static function edit_profile(){ 
        self::$instance->render(array(
            'user_id'	=> 0,
            'view'		=> 'register',
            'el'		=> 'div'
        ));
    }

    public static function render_edit( $user ) {    
        self::$instance->render(array(
            'user_id'	=> $user->ID,
            'view'		=> 'edit',
            'el'		=> 'tr'
        ));
    }

    public static function render_new() {     
        self::$instance->render(array(
            'user_id'	=> 0,
            'view'		=> 'add',
            'el'		=> 'tr'
        ));
    }

    public static function render( $args = array() ) {
        $args = wp_parse_args($args, array(
            'user_id'	=> 0,
            'view'		=> 'edit',
            'el'		=> 'tr',
        ));
        
        // vars
        $post_id = 'user_' . $args['user_id'];
        
        // get field groups
        $field_groups = self::$groupInst->getFieldGroupsView(array(
            'user_id'	=> $args['user_id'] ? $args['user_id'] : 'new',
            'user_form'	=> $args['view']
        ));
        
        // bail early if no field groups
        if( empty($field_groups) ) {
            return;
        }
        
        $before = '<table class="form-table"><tbody>';
        $after = '</tbody></table>';
                
        if( $args['el'] == 'div') {
            $before = '<div class="smack-user-' . $args['view'] . '-fields smack-fields -clear">';
            $after = '</div>';
        }
        
        foreach( $field_groups as $fieldGroup ) {
          
            // skip current loop if group is inactive
            if(!$fieldGroup['active']){
                continue;
            }
                
            $fields = self::$fieldInst->getFields( $fieldGroup );
            if($fieldGroup['style']=='Standard (WP metabox)') {
                $fieldGroup['style']='default';
            }

            if($fieldGroup['type_rule'] == 'basic'){
                foreach($fieldGroup['location'][1]['users'] as $field_groups_value){
                    if($field_groups_value['selected']){
                        if( is_user_logged_in() ) {
                            $user = wp_get_current_user();
                            $roles = ( array ) $user->roles;
                
                            if($field_groups_value['label'] == 'Administrator'){
                                if(in_array( 'administrator', $roles, true )){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            if($field_groups_value['label'] == 'Editor'){
                                if(in_array( 'editor', $roles, true )){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            if($field_groups_value['label'] == 'Author'){
                                if(in_array( 'author', $roles, true )){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            if($field_groups_value['label'] == 'Contributor'){
                                if(in_array( 'contributor', $roles, true )){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            if($field_groups_value['label'] == 'Subscriber'){
                                if(in_array( 'subscriber', $roles, true )){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            
                        }
                    }
                }      
            }
            elseif($fieldGroup['type_rule'] == 'advanced'){
                foreach($fieldGroup['location']['group_0'][0] as $fieldGroupKeys => $fieldGroupValues){

                    $posttypes = $fieldGroupValues[0]['param'];
                    $operator = $fieldGroupValues[1]['operator'];
                    $posttype = $fieldGroupValues[2]['value']; 
                    if($operator=='is equal to') {
                        if($posttypes=='Current User'){
                            if($posttype=='logged_in'){
                                if(is_user_logged_in()){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            
                            else if($posttype=='viewing_front'){
                                if(!is_admin()){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            else if($posttype=='viewing_back'){
                                if(is_admin()){   
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                        }
                        if($posttypes=='Current User Role'){
                            if($posttype=='Administrator'){
                                if(current_user_can('administrator')) {
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            else if($posttype=='Editor'){	
                                if(current_user_can('editor')){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            else if($posttype=='Author'){
                                if( current_user_can('author') ) {
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            else if($posttype=='Contributor'){
                                if( current_user_can('contributor')){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            else if($posttype=='Subcriber'){
                                if( current_user_can('subscriber')){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                }
                            }
                            
                        }
                        if($posttypes=='User Role'){
                            $id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0 ;
                            $role = [];
                            if($posttype=='all'){ 
                                if( is_user_logged_in() ) {
                                    $user = wp_get_current_user();
                                    $roles = ( array ) $user->roles;
                                    
                                    if($roles){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                            }
                            else if($posttype=='Administrator'){ 
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = ( array ) $user->roles;
                                    
                                    if(in_array( 'administrator', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                                else {
                                    $user  = get_userdata(1);
                                    $roles = ( array ) $user->roles;

                                    if(in_array( 'administrator', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                                }
                            }
                            else if($posttype=='Editor'){                                 
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'editor', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Author'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'author', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Contributor'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'contributor', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Subscriber'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'subscriber', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Customer'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'customer', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Shop_manager'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(in_array( 'shop_manager', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                        }
                        if($posttypes=='User Form'){
                            $current_screen = get_current_screen();
                            if($current_screen->base == 'user' && $posttype == 'add'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                            if(($current_screen->base == 'profile' || $current_screen->base == 'user') && $posttype == 'add/edit'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                            if($posttype == 'all'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                        }
                    }
                    else{

                        if($posttypes=='Current User'){
                            if($posttype=='logged_in'){
                                if(!is_user_logged_in()){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    	
                                }
                            }
                            else if($posttype=='viewing_front'){  
                                if(is_admin()){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            else if($posttype=='viewing_back'){
                                if(!is_admin()){   
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                        }
                        if($posttypes=='Current User Role'){
                            if($posttype=='Administrator'){
                                if(!current_user_can('administrator')) {
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            else if($posttype=='Editor'){	
                                if(!current_user_can('editor')){ 
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            else if($posttype=='Author'){ 
                                if(! current_user_can('author') ) {
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            else if($posttype=='Contributor'){
                                if( !current_user_can('contributor')){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            else if($posttype=='Subcriber'){
                                if( !current_user_can('subscriber')){
                                    self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);    
                                }
                            }
                            
                        }
                        if($posttypes=='User Role'){
                            $id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0 ;
                            $role = [];
                            if($posttype=='all'){ 
                                if( is_user_logged_in() ) {
                                    $user = wp_get_current_user();
                                    $roles = ( array ) $user->roles;
                                    
                                    if(!$roles){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                            }
                            else if($posttype=='Administrator'){ 
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = ( array ) $user->roles;
                                    
                                    if(!in_array( 'administrator', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                                else {
                                    $user  = get_userdata(1);
                                    $roles = ( array ) $user->roles;

                                    if(!in_array( 'administrator', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }
                                }
                                }
                            }
                            else if($posttype=='Editor'){                                 
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'editor', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Author'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'author', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Contributor'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'contributor', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Subscriber'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'subscriber', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Customer'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'customer', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                            else if($posttype=='Shop_manager'){
                                if( is_user_logged_in()) {
                                    if($id != 0) {
                                    $user = get_userdata($id);
                                    $roles = (array) $user->roles;                                                                
                                    }
                                    else {
                                        if($id == 0) {                                            
                                            $user = wp_get_current_user();;
                                            $roles = (array) $user->roles;
                                        }
                                    }
                                    if(!in_array( 'shop_manager', $roles, true )){
                                        self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                                    }

                                }
                            }
                           

                        }
                        if($posttypes=='User Form'){
                            $current_screen = get_current_screen();
                            if($current_screen->base == 'user' && $posttype == 'add'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                            if(($current_screen->base == 'profile' || $current_screen->base == 'user') && $posttype == 'add/edit'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                            if($posttype == 'all'){
                                self::$instance->displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields);
                            }
                        }
                    }     
                }
            }
        }   
    }

    public function displayUserRender($fieldGroup, $before, $after, $post_id, $args, $fields){
        $location = $fieldGroup['location'];
        $test = [];
        foreach($location as $key => $type){
            if(array_key_exists('users',$type)){
                $test = $location[$key]['users'];
            }
        }
        //$test = $fieldGroup['location'][0]['users'];
        foreach ($test as $tkey=> $tval){
            $val = $tval['selected'];
            if ($val == ' ' )
            {
               // return true;
            }

        }
        if( $fieldGroup['style'] === 'default' )  {
           echo '<h2>' . esc_html($fieldGroup['title']) . '</h2>';
        }
        
        // render
        echo $before;
        self::$postInst->renderFields( $fields, $post_id, $args['el'],'label', 'user' );
        echo $after;
    }
}