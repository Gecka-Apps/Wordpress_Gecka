<?php

// sets include path for easy inclusion
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) .'/Library');

/**
 * Library exception class
 */
class Gecka_Exception extends Exception { }

require_once 'Plugin/Abstract.php';

/**
 * Gecka Library Plugin main class
 */
class Gecka extends Gecka_Plugin_Abstract {

    protected $ShortcodesMatches = array();
    
    protected function init () {
        require_once ( 'Javascripts/script-loader.php');
    }
    
    protected function admin_init() { }
    
    protected function user_init() {
        add_action ('template_redirect', array($this, 'shortcode_init'), 40);
    }
    
    public function shortcode_init() {
        global $wp_query;
        
        if( ! empty($wp_query->post) && $post = $wp_query->post) {
            
            // result not cached
            if( empty($this->ShortcodesMatches[$post->ID]) ) {
            
                $pattern = get_shortcode_regex();
                
                if( !preg_match('/'.$pattern.'/s', $post->post_content, $regs) ) return;
                
                $this->ShortcodesMatches[$post->ID] = $regs;
                
            }
            
            do_action('shortcode_init', $post, $this->ShortcodesMatches[$post->ID]);
        }
    }
    
    static function locate_template ( $module, $name, $slug='', $more_paths=array() ) {

        /* look for a matching template file in stylesheet & theme dirs */
        $to_locate = array();
        
        if($slug) {
            $_name = $name . '-' . $slug;
            $to_locate = array( $module . '/'.$_name.'.php',
            					$module . '-' .$slug .'/'.$name.'.php',
            					$module . '/' .$slug .'/'.$name.'.php',
                                $module . '-'.$_name.'.php' );
        }
        
        $to_locate = array_merge($to_locate, array( $module . '/'.$name.'.php',
                                                    $module . '-'.$name.'.php' ) );

        if( $template = locate_template( $to_locate ) ) return $template;
           
        /* look for plugin templates in other places */
       
  		$to_locate = array();
        
        if($slug) {
            $_name = $name . '-' . $slug;
            $to_locate = array( $_name.'.php' );
        }
        
        $to_locate = array_merge($to_locate, array( $slug .'/'.$name.'.php',$name.'.php' ) );
        
        foreach ((array)$more_paths as $Path) {
            
            foreach ($to_locate as $file) {
            
                //if(strpos($file,'/') !== false) continue;
                
                $template = $Path . '/' . $file;
                
                if( file_exists( $template ) ) return $template;
                
            }
            
        }
        
        if($slug) {
            $to_locate = array( $_name.'.php' );
        }
        
        $to_locate = array_merge($to_locate, array( $name.'.php' ) );

        if( $template = locate_template( $to_locate ) ) return $template;
        
        return '';
    
    }
    
    static function get_template ( $module, $slug, $name='', $more_paths=array() ) {
        global $wp_query, $post;
    	
    	if( $template = self::locate_template($module, $slug, $name, $more_paths) )
            require ($template);
        
        return $this;
    }

	/**
     * Usefull static functions
     */ 
    static function pagination ($wp_query = '', $args = '' ) {
    
        $args = wp_parse_args($args, array('echo'=>true, 'uri'=>$_SERVER["REQUEST_URI"]));
        extract($args);
    
        $output = '';
    
        if(!$wp_query) $wp_query = $GLOBALS['wp_query'];
        
        $max_num_pages     = absint( $wp_query->max_num_pages ); 
        $current_page     = absint( $wp_query->query_vars['paged'] );    

        if($max_num_pages<=1) return '';
        
        if($current_page>$max_num_pages) $current_page = 1;
        if(!$current_page) $current_page = 1;
        
        // previous
        $previous = false;
        if($current_page > 1 &&  $current_page <= $max_num_pages) $previous = true;
        
        $next = false;
        if($current_page < $max_num_pages ) $next = true;
        
        if($previous) {
            $_uri = add_query_arg('paged', $current_page-1, $uri);
            $output .= ' <a href="'.$_uri.'" class="pprevious" ><<</a> ';
        }
        for($e=1; $e<=$max_num_pages; $e++) {
            
            $_uri = add_query_arg('paged', $e, $uri);
            $_class = $e === $current_page ? ' pcurrent' : '';
            $output .= ' <a href="'.$_uri.'" class="ppage'.$_class.'" >' . $e . '</a>';
            
        }
        if($next) {
            $_uri = add_query_arg('paged', $current_page+1, $uri);
            
            $output .= ' <a href="'.$_uri.'" class="pnext" >>></a> ';
        }
        
        if($echo) echo $output;
        else return $output;
        
    }
}