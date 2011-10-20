<?php

require_once 'Abstract/Options/Overload.php';

/**
 * Manage settings
 * @author Laurent Dinclaux
 * @copyright Gecka
 */
class Gecka_Settings extends Gecka_Abstract_Options_Overload {
	
	protected $settings_slug = '';
	
	public function __construct( $settings_slug, $default_settings = array() ) {

		if( empty($settings_slug) ) throw new Gecka_Exception(__CLASS__ . ' - Error : Empty settings slug');
		
		$this->settings_slug = $settings_slug;
		$this->load( $default_settings );
			
	}
	
	/**
	 * Loads settings from database
	 * @param array $defaults Default settings
	 */
	protected function load ( $defaults = array() ) {
		
		$this->set_options( get_option( $this->settings_slug . '_settings' ), $defaults);
		
	}
	
	public function save () {
		
		update_option( $this->settings_slug . '_settings' , $this->Options );
		
	}
}