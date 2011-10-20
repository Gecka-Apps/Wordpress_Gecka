<?php

class Gecka_Abstract_Class {

	/**
     * Wrapper to the Wordpress built in add_action()  function
     * @see add_action () 
     */
    protected function add_action ($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    	
    	 return add_action($tag, array( $this, $function_to_add ) , $priority, $accepted_args);
    	 
    }
    
    /**
     * Wrapper to the Wordpress built in add_filter()  function
     * @see add_filter () 
     */
	protected function add_filter ($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
    	
    	 return add_filter($tag, array( $this, $function_to_add ), $priority, $accepted_args);
    	 
    }
    
	/**
     * Returns a callback array for to the specified function of this class 
     */
	protected function callback ( $function ) {
    	
    	 return array($this, $function);
    	 
    }
    
}