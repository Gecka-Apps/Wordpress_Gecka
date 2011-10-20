<?php

require 'Abstract/Class.php';

class Gecka_Abstract_Singleton extends Gecka_Abstract_Class {

    protected static $instance = array();
    
    protected function __construct () { }

    public static function instance () {
		$class = get_called_class();
		
    	if( ! isset(self::$instance[$class]) ) {
    		
    		$args = func_get_args();
    		
    		$class = get_called_class();
    		self::$instance[$class] = new $class($args);
    	}
    	
    	return self::$instance[$class];
    }
    
}