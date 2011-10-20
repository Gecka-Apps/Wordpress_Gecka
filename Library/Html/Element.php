<?php

require_once 'Abstract/Options.php';

class Gecka_Html_Element extends Gecka_Abstract_Options {
    
    protected $Tag;
    protected $Id;
    protected $Content;
    
    protected $Singular;
    
    protected $DefaultOptions;
    
    protected $Attributes = 'style,class,onclick';
    
    public function __construct($Tag, $Id, $Content, $Options) {

        $this->Tag = $Tag;
        $this->Id = $Id;
        $this->Content = $Content;
        
        $this->set_options($Options, $this->DefaultOptions);
        
        if( $this->get('Singular') === true ) $this->Singular === $true;
        
    }
    
    public function render () {
        
        $html = '';
        
        $html .= $this->render_before();
        
        $html .= $this->render_content();
        
        $html .= $this->render_after();
        return $html;
        
    }
    
    public function render_content () {
        $html = '';
        if( !$this->Singular && $this->Content ) $html .= $Content;
        return $html;
    }
    
    public function render_before () {
        
        $html = '';
        
        if($this->Tag) {
            
            $html .= '<' . $this->Tag;
            
            $html .= $this->render_attributes();
            
            
        }
        return $html;
    }
    
    public function render_after () {
        $html = '';
        if($this->Tag) {
            if($this->Singular) $html .= ' />';
            else $html .= "</$this->Tag>";
        }
        return $html;
        
    }
    
    protected function render_attributes () 
    {
        
        $html = '';
        $Attributes = explode(',', $this->Attributes);
        foreach ($Attributes as $Attribute) {

            if( $this->get($Attribute) )
                $html .= $this->render_attribute($Attribute, $this->get($Attribute));
        }
        
        return $html;
    }
    
    protected function render_attribute($name, $value) 
    {
        
        return " $name=\"$value\"";    
    }
}