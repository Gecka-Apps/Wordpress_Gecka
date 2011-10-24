<?php


require_once 'Abstract/Options.php';

class Gecka_Tools_ItemSelector extends Gecka_Abstract_Options {
	
	protected $id;
	
	/**
	 * Constructor
	 * @param string $id Selector ID of your choice
	 * @param array $options Selector options
	 */
    public function __construct ( $id, $options = array() ) { 
		
    	$this->id = $id;
    	
    	$this->set_options( $options, $this->default_options() ); 
		
		$this->add_action('wp_ajax_gui-select-item-'.$id, 'select_item_ui');
		
		wp_enqueue_script('jquery-ui-dialog');

        wp_enqueue_style('gui-select-item');    	    	
	    wp_enqueue_script('gui-select-item');
    		
    }
    
    /**
     * Shows the selector and selected items if any
     * @param string $field_id
     * @param array $value
     * @param string $button_label
     * @param array $options
     */
	public function show ( $field_id, $value, $button_label='', $options=array() ) {
		
		// default button label
		if( empty($button_label) && $this->get('multiple') ) $button_label = __('Add', 'gecka');
	    else $button_label = __('Select', 'gecka');  
	    
	    // options
	    $options = wp_parse_args( $options, (array)$this->get_options() );
	    extract($options);
	    
	    // callbacks
	    $onCancel = isset($onCancel) ? $onCancel : null;
    	$onSelect = isset($onSelect) ? $onSelect : null;
    		    
    	// js function args
	    $args = json_encode( compact( 'onSelect', 'onCancel', 'multiple', 'link', 'internal', 'title' ) );
	    
	    // output selected items
	    ?>
		<div class="gecka-select-items" id="gecka_items_selector-<?php echo $field_id ?>">
				
			<div style="display: none" id="<?php echo $field_id; ?>-toclone" class="item">
				<input type="hidden" value="" class="the_item" name="<?php echo $field_id . ($multiple ? '[]' : '') ?>" disabled="disabled" />
				<input type="hidden" value="" class="the_type" name="<?php echo $field_id ?>-type<?php echo $multiple ? '[]' : '' ?>" disabled="disabled" />
				<span class="title"></span>
				<span class="infos"></span>
				<span class="delete"><a href="#" onclick="jQuery(this).parents('.item').remove(); return false;"><?php _e('Remove', 'gecka')?></a></span>
			</div>
			
			<div id="<?php echo $field_id; ?>-items" class="items" >
			
				<?php 
				if(!$multiple) $value = array($value);
				foreach ( (array) $value as $_id => $item ) :
						if( !is_a($item, 'stdClass')) $item = json_decode($item);
						
						if( !$item ) continue;
				
						switch ( $type = $item->type ) {
				
						case 'post':
							$p = get_post($item->id);
							if( !$p ) continue 2;
							
							$_post_type = get_post_type_object( $p->post_type );
														
							$id = $p->ID;
							$title = esc_html($p->post_title);
							$post_type = $p->post_type;
														
							$infos  = ($_post_type ? $_post_type->labels->singular_name : $post_type) . ' (' . $type .')';
							
							break;
					
						case 'taxonomy':
						
							$taxonomy = $item->taxonomy;
							$_taxonomy = get_taxonomy($item->taxonomy);
							
							$t = get_term($item->id, $item->taxonomy);
							if( !$t ) continue 2;
							
							$id = $t->term_id;
							$title = esc_html($t->name);
							
							$infos = ($_taxonomy ? $_taxonomy->labels->singular_name : $taxonomy) . '(' . $type . ')';							
														
							break;
						
						case 'url':
							
							$title 	= '<a href="'.$item->url.'" target="_blank">' . $item->title . '</a>';
							$id  	= $item->id;
							$new_window = $item->new_window;
							$infos  = $new_window ? __('Opens in a new window', 'gecka') : __('Opens in same window', 'gecka');
							break;
					}
							
					?>
					<div class="item">
						<input type="hidden" value="<?php echo esc_attr( json_encode($item) ) ?>" name="<?php echo $field_id . ( $multiple ? '[]' : '' ) ?>" />
						<span class="title"><?php echo $title ?></span>
						<span class="infos"><?php echo $infos ?></span>
						<span class="delete"><a href="#" onclick="jQuery(this).parents('.item').remove(); return false;"><?php _e('Remove', 'gecka')?></a></span>
					</div>
			
				<?php endforeach; ?>
			</div>
			
			<p>
				<a onclick="jQuery.gui.SelectItem.select( '<?php echo $this->id ?>' , '<?php echo $field_id ?>', <?php echo esc_js($args) ?> ); return false;" href="#" title="<?php echo $button_label ?>" ><?php echo $button_label ?></a>	
			</p>
		</div>
	    	<?php
    	
    }
    
    /**
     * Shows the item select UI and the items query result
     */
	public function select_item_ui () {
    	
		extract( (array) $this->get_options() );
		
		/* no nonce, we show the selector ui */
    	if( !isset($_POST['_ajax_select_items_nonce']) ) {
    		require_once GK_PATH . '/Views/Select-items-ui.php';
    		exit;
    	}
    	
    	/* processes search */
    	check_ajax_referer( 'select-items', '_ajax_select_items_nonce' );
	
		$args = array();
		
		if ( isset( $_POST['search'] ) )
			$args['s'] = stripslashes( $_POST['search'] );
		
		$args['type'] = 'post';
		if(isset( $_POST['type']) && in_array($_POST['type'], array('post', 'taxonomy')))
			$args['type'] = $_POST['type'];

		
				
		$args['pagenum'] = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	
		$results = $this->select_items_query( $args );
	
		if ( ! isset( $results ) )
			die( '0' );
	
		echo json_encode( $results );
		echo "\n";
	
		exit;
    }
    
    /**
     * Queries item to select
     * @param array() $args
     * @return boolean|array
     */
	public function select_items_query( $args = array() ) {
		
		switch($args['type']) {
			
			case 'post':

				$query = array(
					'post_type' => $this->get('post_types'),
					'suppress_filters' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'post_status' => 'publish',
					'order' => 'DESC',
					'orderby' => 'post_date',
					'posts_per_page' => 20,
				);
				
				$args['pagenum'] = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;
			
				if ( isset( $args['s'] ) )
					$query['s'] = $args['s'];
			
				$query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;
			
				// Do main query.
				$get_posts = new WP_Query;
				$posts = $get_posts->query( $query );
				// Check if any posts were found.
				if ( ! $get_posts->post_count )
					return false;
			
				$pts = get_post_types( array(), 'objects' );
				
				// Build results.
				$results = array();
				foreach ( $posts as $post ) {
					if ( 'post' == $post->post_type )
						$info = mysql2date( __( 'Y/m/d' ), $post->post_date );
					else
						$info = $pts[ $post->post_type ]->labels->singular_name;
			
					$results[] = array(
						'id' => $post->ID,
						'title' => trim( esc_html( strip_tags( get_the_title( $post ) ) ) ),
						'permalink' => get_permalink( $post->ID ),
						'info' => $info,
						'type' => $args['type'],
						'post_type' => $post->post_type
					);
				}
			
				return $results;
				
			case 'taxonomy':
		
				$txs = get_taxonomies( array(), 'objects');
				$tx_names = array_keys( $txs );
		
				$query = array(
					'number' => 20,
				);
			
				$args['pagenum'] = isset( $args['pagenum'] ) ? absint( $args['pagenum'] ) : 1;
			
				$query['hide_empty'] = false;
				
				if ( isset( $args['s'] ) )
					$query['name__like'] = $args['s'];
			
				//$query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;
			
				// Do main query.
				
				$terms = get_terms( $this->get('taxonomies'), $query );
				
				// Check if any taxonomies were found.
				if ( !$terms )
					return false;
			
				// Build results.
				$results = array();
				
				foreach ( $terms as $term ) {
					
					$info = $txs[ $term->taxonomy ]->labels->singular_name;
			
					$results[] = array(
						'id' => $term->term_id,
						'title' => trim( esc_html( strip_tags( $term->name ) ) ),
						'permalink' => get_term_link( $term ),
						'info' => $info,
						'type' => $args['type'],
						'taxonomy' => $term->taxonomy
					);
				}
			
				return $results;
		}
	}
	
	/**
	 * Default options
	 */
	protected function default_options () {

		return array( 'link' => true,
					  'internal' => true,
					  'multiple' => false,
					  'post_types' => array('post', 'page'),
					  'taxonomies' => array('category', 'post_tag'),
					  'title' => __('Select', 'gecka'),
					);
	}
}