<?php

require_once 'Abstract/Options.php';

class Gecka_Abstract_Options_Overload extends Gecka_Abstract_Options {
    
    protected $Options;
    
    function __set( $name, $value ) {
    	
    	return $this->set($name, $value);
    	
    }
    
    function _get($name) {
    	
    	return $this->get($name);
    	
    }
}