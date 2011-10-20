<?php
require_once 'Html/Field/Abstract.php';

class Gecka_Html_Field_Text extends Gecka_Html_Field_Abstract {
    
    protected $Tag = 'input';
    protected $Singular = true;
    
    
    protected function render_attributes () 
    {
        
        $html = '';
        
        $html .= $this->render_attribute('type', 'text');
        
        if( $this->Default )
            $html .= $this->render_attribute('value', $this->Default);
        
        $html .= parent::render_attributes();
        
        return $html;
    }
}