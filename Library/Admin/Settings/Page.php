<?php

require_once 'Abstract/Class.php';
require_once 'Admin/Settings/Field.php';

/**
 * @author Laurent Dinclaux <laurent@knc.nc>
 * @copyright Gecka Apps
 */
class Gecka_Admin_Settings_Page extends Gecka_Abstract_Class {
	

	/**
     * @var Gecka_Settings
	 */
	private $Settings;

	private $page_id;
	
	private $page_name;
	
	private $page_menu_label;
	
	private $page_capability = 'manage_options';
	
	private $add_to = 'options-general.php';
	
	private $hook;
	
	private $tabs = array();
	
	private $sub_tabs  = array();
	
	private $fields 	  = array();
	
	private $fields_order = array();
	
	/**
	 * Constructor
	 * @param string $page_id
	 * @param string $page_name
	 * @param string $capability
	 * @param Gecka_Settings $settings
	 */
	public function __construct ($page_id, $page_name, Gecka_Settings $settings, $to='options-general.php', $capability='manage_options') {
		
		$this->page_id = $page_id;
		
		$this->page_name = 
		$this->page_menu_label = $page_name;
		
		$this->Settings = $settings;
		
		$this->add_to = $to;
		
		$this->page_capability = $capability;
		
		// adds the page submenu to admin
		$this->add_action( 'admin_menu', 'admin_menu');
		$this->add_filter( 'option_page_capability_' . $page_id, $capability );
		
		$this->add_action( 'admin_enqueue_scripts', 'admin_enqueue_scripts' );

		$this->add_action('wp_ajax_gk_settings_save_' . $page_id, 'save');
	}
	
	/**
	 * Allows to chnage the menu label
	 * @param string $label
	 */
	public function set_menu_label ($label) {
		$this->page_menu_label = $label;
	}
	
	/**
	 * Allows to set the $hook global variable value for which we will insclude css and js
	 * @param string $label
	 */
	public function set_hook ($hook) {
		$this->hook = $hook;
	}
	
	/**
	 * Returns the page capability
	 * @return string
	 */
	function page_capability( ) {
		return  $this->page_capability;
	}
	
	/**
	 * Add a settings tab to Nautile Framework Theme Options page
	 *
	 * @param string $parent_id the parent setting id
	 * @param string $id the setting id
	 * @param string $name the setting nam
	 * @param string|array $callback the setting page callback
	 * @param int $position Optional - the setting page position
	 */
	public function add_tab ( $id, $name, $callback=null, $position='' ) {
	
		$this->tabs[$id] = array(  $name, $callback, $position );
	
		$this->fields[$id] = $this->fields_order[$id] = array();
	
	}
	/**
	 * Add a sub settings tab to Nautile Framework Theme Options page
	 *
	 * @param string $parent_id the parent setting id
	 * @param string $id the setting id
	 * @param string $name the setting nam
	 * @param string|array $callback the setting page callback
	 * @param int $position Optional - the setting page position
	 */
	public function add_subtab ( $parent_id, $id, $name, $callback=null, $position='' ) {
	
		if( ! isset($this->sub_tabs[$parent_id]) ) $this->sub_tabs[$parent_id] = array();
	
		$this->sub_tabs[$parent_id][$id] = array(  $name, $callback, $position );
	
	}
	
	/**
	 * Returns settings
	 * Enter array the settings
	 */
	public function settings_tabs ( ) {
		return $this->tabs;
	}
	
	/**
	 * Returns subsettings tabs
	 * @return array the subsettings
	 */
	public function subsettings_tabs ( ) {
		return $this->sub_tabs;
	}
	
	/**
	 * Add a field to a setting tab
	 * @param string $tab_id
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @param string $default
	 * @param string $description
	 * @param array $values
	 * @param array $args
	 * @return boolean|Gecka_Admin_Settings_Field
	 */
	public function add_field ( $tab_id, $type, $name='', $label='', $description='',$values=array(), $args=array() ) {
	
		//if( !isset($this->tabs[$tab_id]) ) return false;
		
		$var = $tab_id . '_' . $name;
		$default = $this->Settings->$var;		
		
		$this->fields[$var] = new Gecka_Admin_Settings_Field( $type, $var, $label, $default, $description, $values, $args );
		$this->fields_order[$tab_id][] = $var;
		
		return $this->fields[$var];
		
	}
	
	/**
	 * Returns settings fields id, in order, for specified tab
	 * @return array the fields ids
	 */
	public function fields_ordered ( $id ) {
		return isset($this->fields_order[$id]) ? $this->fields_order[$id] : null;
	}
	
	/**
	 * Returns settings fields for specified tab
	 * @return array the fields ids
	 */
	public function field ( $name ) {
		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}
	
	/**
	 * Hook to adds the page submenu to admin
	 * Runs on the admin_menu action hook
	 */
	public function admin_menu () {

		$theme_page = add_submenu_page(
			$this->add_to,
			$this->page_name,    // Name of page
			$this->page_menu_label,    // Label in menu
			$this->page_capability,                 // Capability required
			$this->page_id,             // Menu slug, used to uniquely identify the page
			$this->callback('render_settings_page') // Function that renders the settings page
		);

		if ( ! $theme_page )
			return;

		/*
		$help = '<p>' . __( 'TODO: add some help text', 'nautile' ) . '</p>';
		
		add_contextual_help( $theme_page, $help );
		*/
	
	}
	
	/**
	 * Renders the page
	 * Callback registred in the add_theme_page() function
	 */
	public function render_settings_page () {
		
		
		if( ! current_user_can( $this->page_capability()) ) die;
		do_action($this->page_id.'-before');
		include GK_PATH . '/Views/Admin-Settings-Page.php';
		do_action($this->page_id.'-after');
	}
	
	public function admin_enqueue_scripts ($hook) {
		
		if( !$this->hook ) trigger_error("You should specify the hook value for which to load needed settings page CSS and JS", E_USER_NOTICE);
		
		if( $this->hook && $this->hook !== $hook ) return;
		
		wp_enqueue_script('admin-settings-page');
		wp_enqueue_style('admin-settings-page');
		
	}

	private function menu () {
		
		echo '<ul>';
		
		foreach ( $this->tabs as $setting => $params) {
			
			list($name, $callback, $position) = $params;
			
			echo '<li class="setting-' . $setting . '">';
			
			/*if($callback) $href="#setting-$setting";
			else $href = '#';*/
			$href="#setting-$setting";
			
			echo '<a href="'. $href . '">';
			esc_html_e($name);
			echo '</a>';
			
			if( ! empty($this->sub_tabs[$setting]) ) {
				
				echo '<ul class="sub">';
				
				foreach ( $this->sub_tabs[$setting] as $_setting => $_params) {
					list($_name, $_callback, $_postion) = $_params;
			
					echo '<li class="setting-' . $setting . '-' . $_setting . '"> ';
			
					echo '<a href="#setting-' . $setting . '-' . $_setting . '">';
					echo esc_html($_name);
					echo '</a>';
					
					echo '</li>';
				}
				
				echo '</ul>';
				
			}
			
			echo '</li>';
			
		}
		
		echo '</ul>';
		
		
	}
	
	private function content () {
		
		foreach ( $this->tabs as $setting => $params) {
			
			list($name, $callback, $position) = $params;
			
			echo '<div id="setting-' . $setting . '" style="display:none">';
			echo '<h2 class="screen-reader-text">' . esc_html($name) .'</h2>';
				
			if( $callback )	call_user_func($callback);

			$fields = $this->fields_ordered($setting);
			
			foreach ((array)$fields as $field) {
				$this->field($field)->render();
			}
			
			echo '</div>';
			
			
			if( ! empty($this->sub_tabs[$setting]) ) {
				
				foreach ( $this->sub_tabs[$setting] as $_setting => $_params) {
					list($_name, $_callback, $_postion) = $_params;
					
					echo '<div id="setting-' . $setting . '-' . $_setting . '" style="display:none">';
					echo '<h2 class="screen-reader-text">' . esc_html($_name) .'</h2>';
					if( $_callback )	call_user_func($_callback);

					
					$fields = $this->fields_ordered($_setting);
			
					foreach ((array)$fields as $field) {
						$this->field($field)->render();
					}
					echo '</div>';
				}
			}
		}
		
	}
	
	public function save() {

		foreach ( $this->fields as $name=>$field ) {
			
			if( isset($this->Settings->$name) ) { // has to be in settings object

				// bool fields get special treatment
				if( $field->type() === 'bool' ) {
					
					if( isset($_POST[$name]) ) $var = true;
					else $var = false;
					
				}
				
				// we didn't receive any value for the specified field
				else if ( ! isset($_POST[$name]) ) continue; 
				
				else {
					// strip slashes, damn you wordpress
					if( is_array($_POST[$name]) ) $_POST[$name] =  array_map('stripslashes_deep', $_POST[$name]);
					$_POST[$name] = stripslashes($_POST[$name]);
					
					if( $field->validate($_POST[$name]) === false ) continue;
					$var =  $field->sanitize($_POST[$name]);
				}
				
				$this->Settings->$name = $var;
				
			}
			
		}
		
		do_action ( 'gk-settings-validate-' . $this->page_id, $this->Settings );
		
		$this->Settings->save();
		
		wp_redirect( wp_get_referer() );
	
	}
	
}