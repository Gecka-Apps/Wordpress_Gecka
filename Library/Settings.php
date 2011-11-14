<?php

require_once 'Abstract/Class.php';

/**
 * Manage settings
 *
 * @author Laurent Dinclaux <laurent@knc.nc>
 * @copyright Gecka Apps
 */
class Gecka_Settings extends Gecka_Abstract_Class {

	private $wp_options_var = '';

	/**
	 * @var ArrayObject
	 */
	private $settings;

	/**
	 * Constructor
	 * loads settings from database, over user defined, over defaults
	 * @param array $settings User defined settings to overload default settings
	 */
	public function __construct ( $wp_options_var, $settings = array() ) {

		$this->wp_options_var = $wp_options_var;

		$settings = wp_parse_args( $settings, $this->defaults() );

		$settings = wp_parse_args( get_option( $wp_options_var ), $settings );

		$this->settings = new ArrayObject( $settings, ArrayObject::ARRAY_AS_PROPS );

	}

	/**
	 * Return a setting value
	 * @param string $name The name of the setting value to retrieve
	 * @return mixed The setting value
	 */
	public function get ( $name ) {
		return isset($this->settings->$name) ? $this->settings->$name : null;
	}

	/**
	 * Overload - Return a setting value
	 * @param string $name The name of the setting value to retrieve
	 * @return mixed The setting value
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

	/**
	 * Set a setting value
	 * @param string $name The value key
	 * @param string $value The value
	 * @return gecka_settings
	 */
	public function set ( $name, $value ) {
		$this->settings->$name = $value;
		return $this;
	}

	/**
	 * Overload - Set a setting value
	 * @param string $name The value key
	 * @param string $value The value
	 * @return gecka_settings
	 */
	public function __set( $name, $value ) {
		return $this->set( $name, $value );
	}
	
	public function __isset($name) {
		if( isset($this->settings->$name) ) return true;
		return false;
	}

	/**
	 * Save all settings to databse
	 * @return Gecka_Settings
	 */
	public function save () {
		update_option( $this->wp_options_var, (array)$this->settings);
		return $this;
	}

	/**
	 * Returns all settings
	 * @param string $type
	 * @return ArrayObject|array
	 */
	public function get_all ($type = 'array') {
		if( 'object' == $type ) return clone $this->settings;
		else return (array) $this->settings;
	}

	/**
	 * Merge the provided setings with current settings
	 * @param array $settings
	 * @return Gecka_Settings
	 */
	public function set_all ($settings) {
		foreach ($settings as $setting => $val) {
			$this->set($setting, $val);
		}
		return $this;
	}

	/**
	 * Returns an array of available settings names
	 */
	public function available () {
		return array_keys( (array) $this->settings );
	}

	/**
	 * Injects default settings
	 * @param array $default
	 */
	public function inject_defaults ( $default ) {
		$this->settings = new ArrayObject( wp_parse_args((array)$this->settings, $default), ArrayObject::ARRAY_AS_PROPS );
	}

	/**
	 * Returns the default settings
	 * @return array The default settings
	 */
	protected function defaults () {
		
		$settings = array ( );

		return apply_filters( $this->wp_options_var . '-default-settings', $settings);
	}

	/**
	 * Returns the capability needed for users to manage settings
	 */
	public function capability () {
		return apply_filters( $this->wp_options_var . '-settings-capability', 'manage_options');
	}

}