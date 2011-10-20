<?php

require_once 'Abstract/Singleton.php';

/**
 * Abstract class for plugins main class
 *
 * @author Laurent Dinclaux
 * @copyright Gecka (R)
 * @package Gecka Library
 * @since 1.0
 */
abstract class Gecka_Plugin_Abstract extends Gecka_Abstract_Singleton {

	protected $Prefix;
	
    protected $TextDomain;
       
    protected $Path;
    
    // post types vars
    protected $post_types = array();
    
    protected $registred_post_types;
    
    protected $post_types_dir = '/PostTypes';
    
    /**
     * The constructor
     * 
	 * To extend this abstract class, you have to create your own constructor like this:
	 * 
	 * public function __construct() {
     *     	// call parent constructor
     *  	parent::__construct( dirname(__FILE__) ); // absolute path to you plugin folder (mandatory)
     * }
	 *
     * @param string $plugin_path
     */
    protected function __construct ($args) {
       
    	list( $path ) = $args;
    	
    	if( ! $path ) throw new Gecka_Exception ( 'Absolute path to plugin dir is missing' );
    	
        $this->Path = $path;
        
        // if no prefix defined, we use the class name
        if( ! $this->Prefix ) $this->Prefix = get_called_class();
        
        // if no textdomain is given, we make one using the prefix
        if( ! $this->TextDomain ) $this->TextDomain = strtolower( str_replace( '_', '-', $this->Prefix ) );
        
        // plugin's init hook
        $this->add_action( 'init', '_init' );
    
    }
    
    /**
     * Based init hook
     * @access private
     */
    public function _init() {
    
        $this->init();

        //if( is_admin() ) add_action( 'admin_init', array($this, 'admin_init') );
        
        if( is_admin() ) $this->admin_init();
        else $this->user_init();
    
    }

    /**
     * Common init hook
     */
    abstract protected function init ();
    
    /**
     * Admin init hook
     */
    abstract protected function admin_init ();
    
    /**
     * Frontend only init hook
     */
    abstract protected function user_init ();
    
    /**
     * Returns the plugin TextDomain
     *
     * @return string the plugin text domain
     */
    public function TextDomain() { return $this->TextDomain; }
    
    /**
     * Returns the plugin folder absolute path
     *
     * @return string the plugin folder absolute path
     */
    public function Path() { return $this->Path; }
    
    protected function register_post_type ( $post_type ) {
    	
    	$args = func_get_args();
    	
    	foreach ( $args as $post_type ) {
    		if( !is_string($post_type) ) continue;
    		$this->post_types[] = $post_type;
    	}
    	
    }
    
    /**
     * Automatically inits registered post types
     */
    protected function InitPostTypes () {
        
        if( ! $this->post_types ) return;
       
        $post_types = explode(',', $this->post_types);
        $post_types = array_map('trim', $post_types);
        
        foreach ($post_types as $type) {
        	
        	// loads post types from the PostTypes subfolder
        	if( is_dir($this->Path . $this->post_types_dir) ) {
        		$class_path = $this->Path . $this->post_types_dir . '/' . ucfirst($type) . '.php';
            	$class_name = $this->Prefix . '_PostType_' . str_replace( '-', '_', ucfirst($type) );
        	}
        	
            if( isset($class_path) && file_exists($class_path) ) {
                
                require_once ($class_path);

                if ( class_exists($class_name) ) 
                	$this->registred_post_types[$type] = call_user_func_array( array($class_name, 'instance'), array($this) );
                
            }
        }
        
        return $this;
    
    }  

    protected function get_post_type($post_type) {
    	if( !empty($this->registred_post_types[$post_type]) ) return $this->registred_post_types[$post_type];
    	return false;
    }
}

/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if( ! function_exists('get_called_class') ) :
	function get_called_class($bt = false,$l = 1) {
	    if (!$bt) $bt = debug_backtrace();
	    if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
	    if (!isset($bt[$l]['type'])) {
	        throw new Exception ('type not set');
	    }
	    else switch ($bt[$l]['type']) {
	        case '::':
	            $lines = file($bt[$l]['file']);
	            $i = 0;
	            $callerLine = '';
	            do {
	                $i++;
	                $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
	            } while (stripos($callerLine,$bt[$l]['function']) === false);
	            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
	                        $callerLine,
	                        $matches);
	            if (!isset($matches[1])) {
	                // must be an edge case.
	                throw new Exception ("Could not find caller class: originating method call is obscured.");
	            }
	            switch ($matches[1]) {
	                case 'self':
	                case 'parent':
	                    return get_called_class($bt,$l+1);
	                default:
	                    return $matches[1];
	            }
	            // won't get here.
	        case '->': switch ($bt[$l]['function']) {
	                case '__get':
	                    // edge case -> get class of calling object
	                    if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
	                    return get_class($bt[$l]['object']);
	                default: return $bt[$l]['class'];
	            }
	
	        default: throw new Exception ("Unknown backtrace method type");
	    }
	}
endif;
