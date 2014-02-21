<?php
namespace ddr\log;

class e extends \ddr\base_class {
	public $controller;
	public $pointer;
	
	public function __construct(\ddr\log\c $controller) {
		parent::__construct();
		$this->controller = &$controller;
	}
	
	public function handle(&$e) {
		$this->pointer = $e;
                $this->controller->done(true);
		while ($e) {
                    if ($e->getCode() >= 0) {
                        $this->controller->msg($e->getMessage(), \ddr\log\constants::$levels['always']);
                    } else {
                        
			printf ( "%s:%d %s (%d) [%s]\n", $e->getFile (), $e->getLine (), $e->getMessage (), $e->getCode (), get_class ( $e ) );
                    }
                        $e = $e->getPrevious ();
		}
	}
}