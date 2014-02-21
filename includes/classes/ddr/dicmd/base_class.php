<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr\dicmd;


/**
 * Description of base_class
 *
 * @author ddr
 */
class base_class extends \ddr\base_class {
    //pointer to log class, defined in \ddr\dicmd
    protected $log;
        
    //pointers to log level constants
    protected $LL_ALWAYS;
    protected $LL_DEBUG;
    protected $LL_INFO;
    protected $LL_WARNING;
    protected $LL_ERROR;
    
    public function __construct(&$parent = null, $e = null) {
        parent::__construct($parent, $e);

        $this->LL_ALWAYS =& \ddr\log\constants::$types['always'];
        $this->LL_DEBUG =& \ddr\log\constants::$types['debug'];
        $this->LL_INFO =& \ddr\log\constants::$types['info'];
        $this->LL_WARNING =& \ddr\log\constants::$types['warning'];
        $this->LL_ERROR =& \ddr\log\constants::$types['error'];
        
        if (is_a($parent, "\ddr\\dicmd")) {
            if (is_a($parent->log, "\\ddr\\log")) {
                $this->log =& $parent->log;
            }
        }
    }
}

