<?php

class Gecka_Abstract_Options {
    
    protected $Options;
    
    public function set_options ($Options, $DefaultOptions = array()) {
        $Options = wp_parse_args($Options, $DefaultOptions);
        $this->Options = new ArrayObject ( $Options, ArrayObject::ARRAY_AS_PROPS );
    }
    
    public function set($name,$value) {
        
        $this->Options->$name = $value;
        
    }
    
    public function get_options () {
    	return $this->Options;
    }
    
    public function get($name) {
        
        if( !isset($this->Options->$name) ) return null;
        
        return $this->Options->$name;
    
    }
        
}