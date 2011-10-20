<?php

class Gecka_MetaBox_Exception extends Exception { }

class Gecka_MetaBox {
    
    protected $Prefix = '';
    protected $Title = '';
    protected $Page = '';
    protected $Context = '';
    protected $Priority = '';
    
    protected $Autosave = false;
    
    protected $Options;
    
    protected $DefaultOptions = array();
    
    protected $Fields = array();
    protected $Fields_Names = array();
    
    protected $Html;
    protected $View;
    
    public function __construct ($Id, $Title='', $Page='', $Context='normal', $Priority='default', $Options=array() ) 
    {
        
        $this->Options = wp_parse_args( $Options, $this->DefaultOptions );
        
        $this->Id = $Id;
        $this->Prefix = isset($this->Options['Prefix']) ? $this->Options['Prefix'] : $Id;
        unset($this->Options['Prefix']);
        
        $this->Title     = $Title;
        $this->Page     = $Page;
        $this->Context     = $Context;
        $this->Priority = $Priority;
  
        add_action ('save_post', array($this, 'save_postdata') , 10, 2);
    }
    
       
    public function register () {
        
        add_meta_box( $this->Id, $this->Title, array($this, 'add_meta_box'), $this->Page, $this->Context, $this->Priority, $this->Options );
        
    }
    
    /**
     * Adds the meta box
     */
    public function add_meta_box ($post, $args) {
        
        // metabox nonce field
        wp_nonce_field($this->Id, $this->Id.'_nonce', false );
        
        // gets all post's meta fields
        $custom = get_post_custom($post->ID);
        
        // gets our metas in a Data array
        $Data = array();
        foreach ($this->Fields_Names as $Name) {
            
            // meta name to get
            $FieldName = '_' . $this->Id . '_' . $Name; 
            if ( isset($custom[$FieldName]) ) {
                    
                    // it is an array with more than one element, keeps it as an array
                    if(sizeof($custom[$FieldName])>1) $Data[$Name] = $custom[$FieldName];
                    
                    // only one element we get it
                    else $Data[$Name] = $custom[$FieldName][0];
                    
                    $Data[$Name] = maybe_unserialize($Data[$Name]);
                } 
                
                // sets a default empty value if meta doesn't exists
                else {
                    $Data[$Name] = '';
                }
                
        }
        
        // allow Data array to be filtered
        $Data = apply_filters( $this->Id.'_meta_display_filter', $Data);
        
        /* TODO dom builder view */
        /* if($this->Html) echo $this->Html->render(); */ 
        
        /* template view */
        if ($this->View) {
            extract($Data);
            include_once $this->View; 
        }              
    }
    
    /**
     * Saves the posted meta fields
     * @param int $post_id
     * @param object $post
     */
    public function save_postdata ($post_id, $post = null) {

        /* gets real post ID */
        if( $parent_id = wp_is_post_revision($post_id) ) $post_id = $parent_id;

        // check autosave
        if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE AND !$this->Autosave ) return $post_id;
        
        // make sure data come from our meta box, verify nonce
        if ( !isset($_POST[$this->Id.'_nonce']) || !wp_verify_nonce($_POST[$this->Id.'_nonce'], $this->Id) ) return $post_id;
        
        // check user permissions
        if ($_POST['post_type'] == 'page')
        {
            if (!current_user_can('edit_page', $post_id)) return $post_id;
        }
        else
        {
            if (!current_user_can('edit_post', $post_id)) return $post_id;
        }
        
        // gets our meta data in a Data array
        $Data = array(); 
        foreach ($this->Fields_Names as $Name) {
            
            $FieldName = $this->field_name($Name);
            
            $Data[$Name] = null;
            
            if( !isset($_POST[$FieldName]) ) continue;
            
            $Data[$Name] = $_POST[$FieldName];
            
        }

        // allow data to be filters
        $Data = apply_filters( $this->Id.'_meta_filter', $Data, $post_id, $post);
        
        // save the meta fields
        foreach ($Data as $key=>$val) {
            
            $meta_key = '_' . $this->field_name($key);
             
            if( !update_post_meta($post_id, $meta_key, $val) ) {
                add_post_meta($post_id, $meta_key, $val, true);
            }
            
                
        }
        
    }
    
    public function set_fields_names($names) {
        if(is_string($names)) {
            $names = explode(',', $names);
            $names = array_map('trim', $names);
        }
        $this->Fields_Names = array_merge($this->Fields_Names, $names);
        return $this;
    }
    
    public function set_view($View) {
        
        $this->View = $View;
        return $this;
        
    }
    
    public function autosave($bool) {
    	$this->Autosave = $bool ? true :false;
    }
    
    public function html () {
        
        if(!$this->Html) {
            require_once 'Html.php';
            $this->Html = new Gecka_Html();
        }
        return $this->Html;
    }
    
    private function field_name ($Name) {
        return $this->Id . '_' . $Name;
    }
}
