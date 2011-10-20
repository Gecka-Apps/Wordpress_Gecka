<?php

require_once 'Abstract/Options.php';

class Gecka_PostType extends Gecka_Abstract_Options {
    
    protected $DefaultOptions = array('Plural'=>'');
    
    protected $PostTypeOptions;
    protected $DefaultPostTypeOptions = array( 'show_ui' => true, 'rewrite'=>false, '_has_archive'=>false);
    
    protected $Taxonomies = array();
    
    protected $MetaBoxes = array();
    
    protected $Columns = array ();
    protected $ColumnsOrderBy = array();
    
    protected $TemplatesPathes = array();
    
    protected $HoldingPages = array();
    
    protected $Id;
    protected $Name;
    protected $TextDomain;
    
    protected $EditPostErrors = array();
    protected $EditPostErrorMessages = array();
    
    protected $Archives;
    
    public function __construct($Id, $Name, $TextDomain='gecka', $PostTypeOptions=array(), $Options=array() ) {
        
        $this->Id         = $Id;
        $this->Name     = $Name;
        
        $this->TextDomain = $TextDomain;
        
        // user defined options
        $this->set_options($Options, $this->DefaultOptions);
                
        /* 
         * Automatic PostType Options
         */
        
        // plural label
        $this->DefaultPostTypeOptions['label'] = ucfirst( $this->Options->Plural ? $this->Options->Plural : $Name .'s' );
        
        // metaboxes callback
        $this->DefaultPostTypeOptions['register_meta_box_cb'] = array($this, 'register_meta_box_cb');
        
        // user defined postType options
        $this->PostTypeOptions = new ArrayObject( wp_parse_args( $PostTypeOptions, $this->DefaultPostTypeOptions) , ArrayObject::ARRAY_AS_PROPS);
        
        // manages post save errors
        add_filter('wp_insert_post_data', array($this, 'check_editpost_data'), 10,2);
        add_filter('redirect_post_location', array($this, 'add_errors_to_redirect'), 10 ,2);
		add_action('add_meta_boxes_' . $this->Id, array($this, 'show_errors'));
        add_filter('nav_menu_css_class', array($this,'current_type_nav_class'), 10, 2 );
        
        //add_filter('template_include', array($this, 'template_include'));
        
        
    }
    
    /**
     * PostType options management
     */
    
    public function __set($name,$value) 
    {
        $this->set($name, $value);
    }
    
    public function __get($name) 
    {
        if(!isset($this->PostTypeOptions[$name])) return null;
        return $this->PostTypeOptions[$name];
    }
    
    /**
     * Sets a post type option
     *
     * Those options are exactly the options passed to register_post_type 
     * function. See http://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @since 0.5
     *
     * @param string $name Option name.
     * @param mixed $value The option value.
     * @return Gecka_PostType Itself.
     */
    public function set($name,$value) 
    {
        $this->PostTypeOptions[$name] = $value;
        return $this;
    }
    
    /**
     * Sets multiple post type options
     *
     * Those options are exactly the options passed to register_post_type 
     * function. See http://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @since 0.5
     *
     * @param string|array $name Options as an array or a query string.
     * @return Gecka_PostType Itself.
     */
    public function set_post_options($Options) 
    {
        $Options = wp_parse_args($Options, $this->PostTypeOptions);
        $this->PostTypeOptions = new Array_Object ( $Options, Array_Object::ARRAY_AS_PROP );
        return $this;
    }
    
    /**
     * Adds a template path.
     *
     * Added template paths will be search for templates files before searching
     * in the themes folders.
     *
     * @since 0.5
     *
     * @param string $path Absolute path to a template folder.
     * @return Gecka_PostType Itself.
     */
    public function add_template_path ($path) {
        
        $this->TemplatesPathes[] = $path;
        return $this;
        
    }
    
    /**
     * Adds an holding page.
     *
     * Added template paths will be search for templates files before searching
     * in the themes folders.
     *
     * @since 0.5
     *
     * @param string $path Absolute path to a template folder.
     * @return Gecka_PostType Itself.
     */
    public function add_holding_page ($post_id, $template_name='loop', $wpquery_args='' ) {
        if(!(int)$post_id) return false;
        $this->HoldingPages[$post_id] = array('template_name'=>$template_name, 'args' => wp_parse_args($wpquery_args) ) ;
        return $this;
        
    }
    
    /**
     * MetaBoxes
     * 
     * @return Gecka_MetaBox
     */
    
    public function add_meta_box ($Id, $Title='', $Context='normal', $Priority='default', $Options=array() ) {
        require_once 'MetaBox.php';
        
        if( strpos($Id, '_') !== 0) $Id = '_' . $Id;
        
        $MetaBox = new Gecka_MetaBox($this->Id . $Id, $Title, $this->Id, $Context, $Priority, $Options );
        $this->MetaBoxes[] = $MetaBox;
        
        return $MetaBox;
    }
    
    public function register_meta_box_cb () {
        
        foreach ($this->MetaBoxes as $MetaBox) {
            
            $MetaBox->register();
                global $wp_meta_boxes;
        }
        
    }
    /**
     * Manages post save errors
     */
    
    function check_editpost_data ($data, $postarr) {
		
    	if( ( empty($postarr['post_type']) || $postarr['post_type'] !== $this->Id ) &&
    		$this->get_current_post_type() !== $this->Id) return $data;
    	
        //if( !isset($postarr['action']) || $postarr['action'] !== 'editpost' ) return $data;
        
    	$this->EditPostErrors = apply_filters('check_post_save_'.$this->Id, $this->EditPostErrors, $data, $postarr);
		/*ob_start();
		var_dump($postarr);
		var_export($this->EditPostErrors );
		file_put_contents('/home/lox/dd/'.time(), ob_get_clean());*/
		
        if( sizeof($this->EditPostErrors) && in_array($data['post_status'], array('publish', 'future', 'pending')) )
            $data['post_status'] = empty($postarr['original_post_status']) || $postarr['original_post_status'] == 'auto-draft'? 'draft' : $postarr['original_post_status'];
        
        $data = apply_filters('wp_insert_post_data_'.$this->Id, $data, $postarr, $this->EditPostErrors);
		
		return $data;
	}
	
	function add_errors_to_redirect ($location, $post_id) {
		
		if( sizeof($this->EditPostErrors) ) {
			
			$location = add_query_arg( 'errors', implode(',', $this->EditPostErrors), $location );
			$location = remove_query_arg('message', $location);
			
		}
		return $location;
		
	}
	
	function show_errors () {

		$message = ''; $errors = array();

		if ( isset($_GET['errors']) ) $errors = explode(',', $_GET['errors']);
	  
		foreach ( $errors as $error ) {
			if( isset($this->EditPostErrorMessages[$error]))
			$message .= '<p>'.$this->EditPostErrorMessages[$error].'</p>';
		}
			
		if( !empty($message) ) {
			$GLOBALS['form_extra'] .= '<div id="notice" class="error">';
			$GLOBALS['form_extra'] .= $message;
			$GLOBALS['form_extra'] .= '</div>';
		}
	  
	  
		$GLOBALS['form_extra'] = apply_filters('form_extra_'.$this->Id, $GLOBALS['form_extra']);
	  
	}
	
	function add_editpost_error ( $code ) {
		
		$this->EditPostErrors[]=$code;
	}
	
	function set_error_messages( $errors ) {
		
		if(!is_array($errors)) return;
		
		$this->EditPostErrorMessages = $errors;
		
	}
    
    /**
     * Taxonomies management
     */
    public function add_taxonomy ($Id, $Name, $PostType='', $Args='', $PluralName = '') 
    {
        
        $PluralName = $PluralName ? $PluralName : $Name . 's';
        
        $Defaults = array();
        
        $Defaults['labels'] =  $this->GetLabels ($Name, $PluralName);
        
        $Defaults['label']            = __($PluralName, $this->TextDomain);
        $Defaults['singular_label']   = __($Name, $this->TextDomain);
        
        if( !$PostType ) $PostType = array( $this->Id );
        
        $Args = wp_parse_args($Args, $Defaults);

        $this->Taxonomies[$Id] = array($PostType, $Args);
        
        return $this;
        
    }
    
    public function create_taxonomies () 
    {
        
        foreach ($this->Taxonomies as $Id => $Options) {
            register_taxonomy($Id, $Options[0], $Options[1]);
        }
                
    }
    
    /**
     * Registers the post type
     */
    public function register () {

    	/* default values */
    	
    	// labels
    	
		$this->PostTypeOptions['labels'] = $this->Labels ? $this->Labels : $this->GetLabels ();
    	
		// labels
		if(empty($this->PostTypeOptions['template_slug']))  $this->PostTypeOptions['template_slug'] = $this->Id;
		
        // creates taxonomies if any
        if( sizeof($this->Taxonomies) ) $this->create_taxonomies();
        
        /* register post type */
        $this->register_post_type();
        
        // posts to main feed_request
        if( !empty($this->PostTypeOptions['posts_to_main_feed']) && $this->PostTypeOptions['posts_to_main_feed']) {
            add_filter('request', array($this, 'feed_request') );
        }
        
        // custom columns
        //add_filter('manage_edit-' . $this->Name . '_columns', array($this, 'custom_columns_filter') );
        //add_action('manage_posts_custom_column', array($this, 'custom_columns_action') );
        
        // manage holding page archive template
        add_filter('the_content', array($this, 'the_content'));
        
        //add_action('template_redirect', array($this, 'template_redirect') );
        
        // manage custom post type single template
        add_filter('template_include', array($this, 'template_include') );

        global $pagenow; 
       
        if($pagenow == 'edit.php' && !empty($_GET['post_type']) && $_GET['post_type'] === $this->Id) {
            // custom columns
            add_filter("manage_edit-".$this->Id."_columns", array($this,"columns_set") );
            
            add_action("manage_posts_custom_column", array($this, "column_data"), 10, 2 );
        }
        // custom rewrite rules
        //add_filter('generate_rewrite_rules', array($this, 'generate_rewrite_rules') );
        
        // add_filter('post_updated_messages', array($this, 'post_updated_messages') );
        
    }
    
    /**
     * Registers the post_type
     * @return unknown_type
     */
    public function register_post_type () 
    {
        
        register_post_type ( $this->Id, (array)$this->PostTypeOptions );
		
        $this->rewrite_rules ();
        /*if(!function_exists('is_post_type_archive'))
        	$this->archives_setup () ;*/
        
    } 
    function generate_rewrite_rules($wp_rewrite) {

    	/*$feed_rules = array(
        'index.rdf' => 'index.php?feed=rdf',
        'index.xml' => 'index.php?feed=rss2',
        '(.+).xml' => 'index.php?feed=' . $wp_rewrite->preg_index(1)
    );*/

//    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
	}


    function rewrite_rules () {
    	global $wp_rewrite;
    	
    	$post_type = $this->Id;
    	
    	if ( false !== $this->PostTypeOptions->rewrite && '' != get_option('permalink_structure') ) {
    	
    		if ( ! is_array( $this->PostTypeOptions->rewrite ) )
    			$this->PostTypeOptions->rewrite = array();
    		if ( empty( $this->PostTypeOptions->rewrite['slug'] ) )
    			$this->PostTypeOptions->rewrite['slug'] = $post_type;
    		if ( ! isset( $this->PostTypeOptions->rewrite['with_front'] ) )
    			$this->PostTypeOptions->rewrite['with_front'] = true;
    		if ( ! isset( $this->PostTypeOptions->rewrite['pages'] ) )
    			$this->PostTypeOptions->rewrite['pages'] = true;
    		if ( ! isset( $this->PostTypeOptions->rewrite['feeds'] ) || ! $this->PostTypeOptions->_has_archive )
    			$this->PostTypeOptions->rewrite['feeds'] = (bool) $this->PostTypeOptions->_has_archive;
    		
    		foreach ($this->HoldingPages as $page_id => $options) {
           
	        	$archive_slug = get_page_uri($page_id);
	        	
	    		//$wp_rewrite->add_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
	    		
	    		if ( $this->PostTypeOptions->rewrite['feeds'] && $wp_rewrite->feeds ) {
	    			$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
	    			$wp_rewrite->add_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$this->Id" . '&feed=$matches[1]', 'top' );
	    			$wp_rewrite->add_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$this->Id" . '&feed=$matches[1]', 'top' );
	    		}
	    		
	    		if ( $this->PostTypeOptions->rewrite['pages'] )
	    			$wp_rewrite->add_rule( "({$archive_slug})/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", 'index.php?pagename=$matches[1]' . '&paged=$matches[2]', 'top' );
	    	
    		}
    	}
    	
    }
    
    
function current_type_nav_class($classes, $item) {
   $post_type = get_post_type();
    
    if ( $post_type == $this->Id && $item->object == 'page' ) {
    	
    	if( isset($this->HoldingPages[$item->object_id]))
        	array_push($classes, 'current-menu-item');
        else if($key = array_search('current_page_parent', $classes)) {
        	
        	unset($classes[$key]);
        	
        }
    }
    
    
    return $classes;
}
    
    
    public function archives_setup () {
    	
    	global $wp_rewrite;
    	
    	$post_type = $this->Id;
    	
    	if ( false !== $this->PostTypeOptions->rewrite && '' != get_option('permalink_structure') ) {
    		if ( ! is_array( $this->PostTypeOptions->rewrite ) )
    			$this->PostTypeOptions->rewrite = array();
    		if ( empty( $this->PostTypeOptions->rewrite['slug'] ) )
    			$this->PostTypeOptions->rewrite['slug'] = $post_type;
    		if ( ! isset( $this->PostTypeOptions->rewrite['with_front'] ) )
    			$this->PostTypeOptions->rewrite['with_front'] = true;
    		if ( ! isset( $this->PostTypeOptions->rewrite['pages'] ) )
    			$this->PostTypeOptions->rewrite['pages'] = true;
    		if ( ! isset( $this->PostTypeOptions->rewrite['feeds'] ) || ! $this->PostTypeOptions->has_archive )
    			$this->PostTypeOptions->rewrite['feeds'] = (bool) $this->PostTypeOptions->has_archive;

    		if ( $this->PostTypeOptions->hierarchical )
    			$wp_rewrite->add_rewrite_tag("%$post_type%", '(.+?)', $this->PostTypeOptions->query_var ? "{$this->PostTypeOptions->query_var}=" : "post_type=$post_type&name=");
    		else
    			$wp_rewrite->add_rewrite_tag("%$post_type%", '([^/]+)', $this->PostTypeOptions->query_var ? "{$this->PostTypeOptions->query_var}=" : "post_type=$post_type&name=");

    		if ( $this->PostTypeOptions->has_archive ) {
    			$archive_slug = $this->PostTypeOptions->has_archive === true ? $this->PostTypeOptions->rewrite['slug'] : $this->PostTypeOptions->has_archive;
    			$wp_rewrite->add_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
    			if ( $this->PostTypeOptions->rewrite['feeds'] && $wp_rewrite->feeds ) {
    				$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
    				$wp_rewrite->add_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
    				$wp_rewrite->add_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
    			}
    			if ( $this->PostTypeOptions->rewrite['pages'] )
    			$wp_rewrite->add_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );
    		}

    		$wp_rewrite->add_permastruct($post_type, "{$this->PostTypeOptions->rewrite['slug']}/%$post_type%", $this->PostTypeOptions->rewrite['with_front'], $this->PostTypeOptions->permalink_epmask);
    	}
    	
    }
    
    
    public function the_content($content) {
    	
    	foreach ($this->HoldingPages as $page_id => $options) {
            
    		/* WPML support */
            if( function_exists('icl_object_id') ) {
                
                global $sitepress;
                
                /* not default language */
                if(ICL_LANGUAGE_CODE !== $sitepress->get_default_language() ) {
                    $translated_page_id = icl_object_id($page_id, 'page');
                    $page_id = $translated_page_id ? $translated_page_id : $page_id;
                }
            }
            /* */
            
            if(!is_admin() && is_page($page_id)) {
				 	
            	if($this->Archives) return $content .$this->Archives;
            	
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                
                global $wp_query;
                
                $_wp_query = clone $wp_query; 
                
                $args = array('post_type'=>$this->Id);
                if($paged) $args['paged'] = $paged;
                $args = wp_parse_args( apply_filters($this->Id.'_archive_query_args', $options['args']) , $args);
                
                $wp_query = apply_filters($this->Id.'_archive_query', new WP_Query($args), $args );

                ob_start();

                if($template = $this->locate_template($options['template_name'], $this->template_slug))  {
                	global $post, $id, $authordata, $currentday, $currentmonth, $page, $pages, $multipage, $more, $numpages;
                	include $template;
                }
                
                $this->Archives = ob_get_clean();                
                
                $wp_query = clone $_wp_query; 
                wp_reset_postdata();
                
                break;
            }
            
    	}
        
        
        return $content . $this->Archives;
        
    }
    
    /**
     * Template display
     */
    
    public function template_redirect()
    {
        if ( get_query_var( 'post_type' ) === $this->Id ) {
            global $wp_query;
            $wp_query->is_home = false;
        }
        
    }
        
    public function template_include($template) 
    {
        
        if ( get_query_var('post_type') == $this->Id 
             || isset($this->Taxonomies[get_query_var('taxonomy')])) {
              
                if ( is_single() ) {
                    $name = $this->Id;
               
                    // strip prefix if any
                    if(strpos($name, '_')) $name=substr($name, strpos($name, '_')+1);
                    
                	if($_template = $this->locate_template('single', $name)) return $_template;
                	
                }
                else  {
                    $name = $this->Id;
                
                    // strip prefix if any
                    if(strpos($name, '_')) $name=substr($name, strpos($name, '_')+1);
                
                    if($_template = $this->locate_template('index', $name)) return $_template;
                }
                
            }
            
            return $template;
    
    }
    
    function locate_template ($name, $slug='') {
        return Gecka::locate_template (strtolower($this->TextDomain), $name, $slug, $this->TemplatesPathes);
    }
    
    function get_template( $slug, $name = null ) {
        global $wp_query;
        
        if( $template = $this->locate_template($this->Id, $slug, $name, $this->TemplatesPathes) ) {
            require $template;
        }
    }
    
    /**
     * Feeds
     */
     
    /**
     * Callback to add post_type's posts to the main feed
     */
    public function feed_request($q) {
    
        if (isset($q['feed']) ) {
            if(empty($q['post_type'])) $q['post_type'] = array('post');
            $q['post_type'][] = $this->Id;
        }
        return $q;

    }

    
    
    public function add_col ($id, $name) {
        
        $this->Columns[$id] = $name;
        return $this;
    }
    
    public function remove_col ($id) {
        
        if( isset($this->Columns[$id]) ) unset($this->Columns[$id]);
        return $this;
        
    }
    
    public function column_data ($column, $post_id) 
    {
        
        $data = apply_filters("manage_".$this->Id."_columns_data_" . $column, $post_id );
        
        if($data === false) return;
        
        if($data !== $post_id) {
            
            echo $data; return;
            
        }
        
        if(strpos($column, 'tax_') === 0) {
            
            $taxname = substr($column, 4);
            $categories = wp_get_object_terms( $post_id, $taxname );
            
            if ( !empty( $categories ) ) {
                $out = array();
                foreach ( $categories as $c )
                
                    $out[] = "<a href='edit-tags.php?action=edit&amp;taxonomy=$taxname&amp;post_type={$this->Id}&amp;tag_ID={$c->term_id}'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
                    echo join( ', ', $out );
            } else {
                _e('Uncategorized');
            }
            
        }
        
        else {

            echo get_post_meta($post_id, $column, 'true');
        }
    }
    
    public function columns_set ($columns) 
    {
        unset($columns['author']);
        unset($columns['date']);
        //unset($columns['comments']);
        return wp_parse_args($this->Columns, $columns);
        /*
        global $wp_query;
        
        $char = '-'; $dir='DESC';
        if( isset($_GET['orderby'])  && $_GET['orderby'] == 'meta_value' 
         && isset($_GET['meta_key']) && $_GET['meta_key'] == '_winnerdetails_datetime' ) {
            
            if($wp_query->query_vars['order'] == 'DESC') {
                $dir = 'ASC';
                $char = '&#8743;';
            }
            
            else $char = '&#8744;';
            
        }
        
        
        $columns['_winnerdetails_kiosk']="<a href=\"\">$char</a><span>&nbsp;</span>Kiosk";
        return $columns;*/
    }
     
     /**
     * Returns default labels
     */
    protected function GetLabels ($Name='', $PluralName='') 
    {
        
        $Name     = $Name ? $Name : $this->Name;
        $Name = __($Name, $this->TextDomain);
        
        $Plural = $PluralName ? $PluralName : $this->Plural;
        $Plural = $Plural ? $Plural : $Name .'s';
        $Plural = __($Plural, $this->TextDomain);
        
        if( $this->Feminin) {       
        
	        return array(
				'name' =>  ucfirst(  $Plural ),
		        'singular_name' => $Name,
				'add_new' => _x('Add New', 'feminin', 'gecka'),
				'add_new_item' => sprintf( _x('Add New %s',  'feminin', 'gecka'), $Name),
				'edit_item' => sprintf( _x('Edit %s',  'feminin', 'gecka'), $Name),
		        'new_item' => sprintf( _x('New %s',  'feminin', 'gecka'), $Name),
		        'view_item' => sprintf( _x('View %s',  'feminin', 'gecka'), $Name),
		        'search_items' => sprintf( _x('Search %s',  'feminin', 'gecka'),$Plural ),
		        'not_found' => sprintf( _x('No %s found',  'feminin', 'gecka'),$Plural ),
		        'not_found_in_trash' => sprintf( _x('No %s found in Trash.',  'feminin', 'gecka'),$Plural ),
		        'parent_item_colon' => sprintf( _x('Parent %s',  'feminin', 'gecka'), $Name),
			);
	    }
	    
        return array(
			'name' =>  ucfirst(  $Plural ),
	        'singular_name' => $Name,
			'add_new' => __('Add New', 'gecka'),
			'add_new_item' => sprintf( __('Add New %s', 'gecka') , $Name),
			'edit_item' => sprintf( __('Edit %s', 'gecka') , $Name),
	        'new_item' => sprintf( __('New %s', 'gecka') , $Name),
	        'view_item' => sprintf( __('View %s', 'gecka') , $Name),
	        'search_items' => sprintf( __('Search %s', 'gecka') ,$Plural ),
	        'not_found' => sprintf( __('No %s found', 'gecka') ,$Plural ),
	        'not_found_in_trash' => sprintf( __('No %s found in Trash.', 'gecka'),$Plural ),
	        'parent_item_colon' => sprintf( __('Parent %s', 'gecka'), $Name),
		);
    }
    
    /**
     * Return default messages for custom posttype administration
     */
    protected function GetMessages ($Name, $Plural) 
    {

        $Name     = $this->Name;
        $Plural = $this->Plural ? $this->Plural : $Name .'s';
        
        global $post;
        $post_ID = $post->ID;

        return  array(  0 => '', // Unused. Messages start at index 1.
                          1 => ucfirst($Name) . __('updated.', $this->TextDomain) . '<a href="' . esc_url( get_permalink($post_ID) ) . '">'. __('View', $this->TextDomain). $name .'</a>',
                          2 => __('Custom field updated.', $this->TextDomain),
                          3 => __('Custom field deleted.', $this->TextDomain),
                          4 => ucfirst($Name) . __('updated.', $this->TextDomain),
                          /* translators: %s: date and time of the revision */
                          5 => isset($_GET['revision']) ? ucfirst($Name) . sprintf( __(' restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                          6 => ucfirst($Name) . __('published.', $this->TextDomain) . '<a href="' . esc_url( get_permalink($post_ID) ) . '">'. __('View', $this->TextDomain). $name .'</a>',
                          7 => ucfirst($Name) .__('saved.'),
                          8 => sprintf( __('Book submitted. <a target="_blank" href="%s">Preview book</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
                          9 => sprintf( __('Book scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview book</a>'),
                            // translators: Publish box date format, see http://php.net/date
                            date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
                          10 => sprintf( __('Book draft updated. <a target="_blank" href="%s">Preview book</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
                       );
    }
    
    static function get_current_post_type() {
        
         global $post, $typenow, $current_screen;
         
         if( $current_screen && @$current_screen->post_type ) return $current_screen->post_type;
    
         elseif( $typenow ) return $typenow;
        
         elseif( !empty($_REQUEST['post_type']) ) return sanitize_key( $_REQUEST['post_type'] );
    
         elseif ( !empty($post) && !empty($post->post_type) ) return $post->post_type;
         
         elseif( ! empty($_REQUEST['post']) && (int)$_REQUEST['post'] ) {
         	$p = get_post( $_REQUEST['post'] );
         	return $p ? $p->post_type : '';
         }
         
         return '';
    }
}
