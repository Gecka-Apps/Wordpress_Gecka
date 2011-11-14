<?php

// require_once ('Abstract/Class.php');

/** 
 * @author Laurent Dinclaux <laurent@knc.nc>
 * @copyright Gecka Apps
 */
class Gecka_Admin_Settings_Field extends Gecka_Abstract_Class {
	
	private $type;
	private $name;
	private $label;
	private $default;
	private $description;
	private $values;
	private $args = array();
	
	private $sanitize = array();
	private $validate = array();
	
	public function __construct( $type, $name='', $label='', $default=null, $description='',$values=array(), $args=array() ) {
	
		$this->type 	= $type;
		$this->name 	= $name;
		$this->label 	= $label;
		$this->default 	= $default;
		$this->description = $description;
		$this->values = $values;
		
		$this->args = wp_parse_args($args, array());
		
	}
	
	public function type () {
		return $this->type;
	}
	
	public function add_sanitize ($filter_id, $filter_flags=null) {
		$this->sanitize[] = array( $filter_id, $filter_flags);
	}
	
	public function add_validation( $validate_id, $validate_flags=null) {
		$this->validate[] = array( $validate_id, $validate_flags);
	}
	
	public function sanitize ($value) {
		 
		foreach ($this->sanitize as $sanitize) {
			$value = filter_var($value, $sanitize[0], $sanitize[1]);
		}
		
		return $value;
		
	}
	
	public function validate( $value) {
		
		foreach ($this->validate as $validate) {
			if( !filter_var($value, $validate[0], $validate[1]) ) return false;
		}
		
		return true;
		
	}
	
	public function render () {
		
		// no decoration for semantic fields
		if( $this->type=='wrap' || $this->type == 'endwrap' 
			|| (isset($this->args['decorate']) && $this->args['decorate'] === false) ) {
			$this->render_field ();
			return;
		}
		
		?>
		<div class="section section-<?php echo $this->type?> field-<?php echo $this->name?>">
			<h3 class="heading"><?php echo $this->label ?></h3>
			<div class="option">
				<div class="controls">
					
					<?php $this->render_field () ?>
					
					<br>
				</div>
				<div class="explain"><?php echo $this->description ?></div>
				<div class="clear"></div>
			</div>
		</div>
		
		<?php 
	}
	
	private function render_field () {

		switch ($this->type) {

			case 'callback':
				if( ! isset($this->args['callback']) ) break;
				if( ! is_array($this->args['callback']) && ! function_exists($this->args['callback']) ) break;
				if( is_array($this->args['callback']) && ! method_exists($this->args['callback'][0], $this->args['callback'][1] ) ) break;
				
				call_user_func_array( $this->args['callback'], array($this->type, '', $this->label, $this->default, $this->description, $this->args) );
				
				break;
				
			case 'select-pages':
				
				?>
				
				<div class="select_wrapper">
					<?php 
					
					$args = array( 'name' => $this->name,
								   'id' => empty( $this->args['id'] ) ? $this->name : $this->args['id'],
								   'sort_column' => empty( $this->args['sort_column'] ) ? 'menu_order' : $this->args['sort_column'],
								   'sort_order' => empty( $this->args['sort_order'] ) ? 'ASC' : $this->args['sort_column'],
								   'selected' => $this->default,
									'show_option_none' => __('Select', 'gecka')
								);
								
					if( isset( $this->args['specific'] ) ) $args = array_merge( $args, (array) $this->args['specific'] );
					
					wp_dropdown_pages($args); ?>
				</div>
				<?php
				
				break;
			
			case 'select':
				
				$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
				$value = esc_attr($this->default);
				$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
				?>
				<span class="select_wrapper">
				<select name="<?php echo esc_attr($this->name) ?>" id="<?php echo esc_attr($this->name) ?>">
				<?php 
				
				
				foreach ($this->values as $key => $val):
				
					if(is_int($key))  $key = $val;
					?>
					<option value="<?php echo esc_html($key) ?>"<?php selected($key, $this->default) ?>><?php echo esc_html($val) ?></option>
					<?php
				endforeach;
				?>
				</select>
				</span>
				
				<?php 
				break;
			
			case 'radio':
				
				$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
				$value = esc_attr($this->default);
				$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
				foreach ($this->values as $key => $val):
				
					if(is_int($key))  $key = $val;
					
					?>
					<label>
					<span class="checkbox_wrapper">
					<input type="radio" name="<?php echo esc_attr($this->name) ?>[]" id="<?php echo esc_attr($this->name) ?>" value="<?php echo esc_attr($key) ?>"<?php checked($key, $this->default) ?> >
					</span>
					
					<?php 
					echo esc_html($val);
					?>
					</label>
					<?php
				endforeach;
				
				break;
				
			case 'bool':
				
				$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
				$value = esc_attr($this->default);
				$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
				?>
				<span class="checkbox_wrapper">
					<input type="checkbox" name="<?php echo esc_attr($this->name) ?>" id="<?php echo esc_attr($this->name) ?>" value="true"<?php checked(true, $this->default? true:false) ?> > 
				</span>
				
				<?php 
				if( !empty($this->args['toggle']) ):
				?>
				
				<script type="text/javascript">

					jQuery(document).ready( function ($) {

						if( $('#<?php echo $id?>').is(':checked') ) $('<?php echo $this->args['toggle'] ?>').show();
						else $('<?php echo $this->args['toggle'] ?>').hide();
						
						$('#<?php echo $id?>').click( function() {
							if( $(this).is(':checked') ) $('<?php echo $this->args['toggle'] ?>').fadeIn('fast');
							else $('<?php echo $this->args['toggle'] ?>').fadeOut('fast');
						});
						
					});
				
				</script>
				
				
				<?php 
				endif;
				
				
				if( !empty($this->args['togglerev']) ):
				?>
				
				<script type="text/javascript">
				
				jQuery(document).ready( function ($) {
				
				if( $('#<?php echo $id?>').is(':checked') ) $('<?php echo $this->args['togglerev'] ?>').hide();
				else $('<?php echo $this->args['togglerev'] ?>').show();
				
				$('#<?php echo $id?>').click( function() {
				if( $(this).is(':checked') ) $('<?php echo $this->args['togglerev'] ?>').fadeOut('fast');
				else $('<?php echo $this->args['togglerev'] ?>').fadeIn('fast');
				});
				
				});
				
				</script>
				
				
				<?php
				endif;
				
				break;
			
			case 'checkbox':
				
				$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
				$value = esc_attr($this->default);
				$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
				foreach ($this->values as $key => $val):
				
					if(is_int($key))  $key = $val;
					?>
					<span class="checkbox_wrapper">
					<input type="checkbox" name="<?php echo esc_attr($name) ?>[]" id="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($key) ?>"<?php checked($key, $this->default) ?> > 
					</span>
					
					<?php echo esc_html($val)?>
									
					<?php 	
					
				endforeach;
				break;
				
			case 'text':
				
				$name = esc_attr($this->name);
				$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
				$value = esc_attr($this->default);
				$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
				?>
				<input type="text" name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo $value ?>" class="<?php echo $class ?>" >
				<?php
				
				break;
				
			case 'password':
				
					$name = esc_attr($this->name);
					$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
					$value = esc_attr($this->default);
					$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
					?>
				<input type="password" name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo $value ?>" class="<?php echo $class ?>" autocomplete="off" >
				<?php
				
				break;
			
			case 'textarea':
				
					$name = esc_attr($this->name);
					$id = empty( $this->args['id'] ) ? esc_attr($this->name) : esc_attr($this->args['id']);
					$value = esc_attr($this->default);
					$class = 'nautile-input' . ( empty($this->args['class']) ? '' : ' ' . $this->args['class'] );
				
					?>
				<textarea name="<?php echo $name ?>" id="<?php echo $id ?>" class="<?php echo $class ?>" ><?php echo esc_html($value)?></textarea>
				<?php
				
				break;
				
			case 'wrap':
				?>
				<div class="<?php echo esc_attr($this->name) ?>">
				<?php
				break;
				
			case 'endwrap':
				?>
				</div>
				
				<?php
				break;
				
			case 'custom':
				echo isset($this->args['content']) ? $this->args['content'] : '';
				
				break;
				
		}
		
	}
}