<?php

require_once 'Html/Element.php';

class Gecka_Html_Field_Abstract extends Gecka_Html_Element {
    
    protected $Name;
    protected $Description;
    protected $Default;
    protected $MultipleOptions;
    
    public function __construct($Id, $Name='', $Description='', $Default='', $Options=array()) 
    {

        parent::__construct($this->Tag, $Id, '', $Options);
        
        $this->Name = $Name;
        $this->Description = $Description;
        $this->Default = $Default;
        
        $this->MultipleOptions = $this->get('Options') ? $this->get('Options') : null;
    }

    
}