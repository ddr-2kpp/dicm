<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr;


/**
 * Description of cmd_line
 *
 * @author ddr
 * @version 0.0.1b
 */
class cmd_line {

    private $data;
    private $options;
    private $switches;

    
    private function parse() {
        $data = getopt($this->options['short'], $this->options['long']);
        if (is_array($data)) {
            $this->data += $data;
        }
    }

    
    public function __construct($o_short, $o_long) {
        global $argv;
        $this->data['argv'] = $argv;
        $this->options = array('short' => false, 'long' => false);
        $this->options['short'] = $o_short;
        $this->options['long'] = $o_long;

        $this->parse();
    }
    
    public function get($arg) {
        return (array_key_exists($arg, $this->data)) ? $this->data[$arg] : false;
    }

    
    public function is_set($arg) {
        return array_key_exists($arg, $this->data);
    }
    
    
    public function is_one_of_set($args) {
        foreach ($args as $arg) {
            if ($this->isset($arg)) {
                return true;
            }
        }
        return false;
    }
}

