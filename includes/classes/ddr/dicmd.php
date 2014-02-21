<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr;


/**
 * Description of dicmd
 *
 * @author ddr
 * @version 2.0.0b
 */
class dicmd extends \ddr\dicmd\base_class {
    
    protected $cmd_line;

    public $config_file;

    
    protected $cam;
    
    private function set_logging() {
        $logging = $this->config_file->get("logging");

        $log_config = array('name' => 'ddr_log',
                'type' => \ddr\log\constants::$types['file'],
                'options' => array('logfilename' => $logging['path'] . "/" . $logging['name']),
                'level' => \ddr\log\constants::$levels[$logging['level']],

                'limit' => array (
                    'by' => \ddr\log\constants::$limits[$logging['limit_by']],
                    'unit' => \ddr\log\constants::$units[$logging['limit_unit']],
                    'amount' => $logging['limit_amount'],
                    'check' => \ddr\log\constants::$checks['@init']
                ),
            );
        if ($this->cmd_line->is_set("--debug")) {
            $log_config['level'] = \ddr\log\constants::$levels['debug'];
        }
        $this->log = new \ddr\log($log_config);
        
    }


    public function __construct() {
        parent::__construct();

        $this->config_file = new \ddr\config(CONFIG_PATH . "/dicmd.conf.ini");

        $short = "?hl::c::dv";
        $long = array("help","hlp","debug","verbose","log::","config::");
        $this->cmd_line = new \ddr\cmd_line($short, $long);

        $this->set_logging();
        
        $this->log->msg("ddr's ip cam monitor loaded", $this->LL_ALWAYS);
    }

    
    public function init() {
        $this->log->msg("initializing ddr's ip cam monitor", $this->LL_ALWAYS);
        $this->cam = new \ddr\dicmd\CCam($this);
    }
    
    
    public function run() {
        while (1) {
            $this->cam->dl_img();
            $this->cam->save_img();
            //sleep(1);
        }
    }

}

