<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr\dicmd;


/**
 * Description of CCam
 *
 * @author ddr
 * @version 0.0.1b
 */
class CCam extends \ddr\dicmd\base_class {
    protected $cams;
    
    public function __construct(\ddr\dicmd $parent) {
        parent::__construct($parent);
        $this->log->msg("loading cam controller",$this->LL_INFO);
        
        $ips = $this->parent->config_file->get("ip","cameras");
        $no = 0;
        while (array_shift($ips)) {
            $this->cams[] = new \ddr\dicmd\MCam($this->parent, $no);
            $no++;
        }
        
        $this->log->msg("done",$this->LL_DEBUG);
    }
    
    public function dl_img() {
        foreach ($this->cams as &$cam) {
            $cam->dl_image();
        }
    }
    
    public function save_img() {
        foreach ($this->cams as &$cam) {
            if (!$cam->save_image()) {
                $this->log->msg("could not save image from cam #" . $cam->idx, $this->LL_ERROR);
            }
        }
    }
}

