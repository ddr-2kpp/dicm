<?PHP

namespace ddr\log;

class v_file extends \ddr\base_class implements \ddr\log\v_interface {

    private $controller;

    private function filename($o) {
        $sep = $this->controller->model->get('seperator');
        return $o ['dirname'] . $sep['directory'] . $o ['filename'] . "." . $o ['extension'];
    }

    
    public function __construct(\ddr\log\c $controller) {

        parent::__construct($controller);
        $this->controller = &$controller;

        $props = array('handle' => null,
            'job' => 0,
            'options' => array(),
            'e' => array(
                'not_declared' => true,
                'wrong_type' => false
            )
        );
        $this->push("default", $props);
        $this->push("props", $props);

        $this->prepare_options();
    }

    public function __destruct() {

        $this->done(true);
    }

    public function prepare($job) {

        switch ($job) {
            case \ddr\log\constants::$jobs['msg']:
                $this->prepare_container();
                $this->prepare_handler();
                break;
            case \ddr\log\constants::$jobs['limit']:
                $this->put('job', 'limit');
                break;
            default:
                throw new \LogicException("job not awailable");
                break;
        }
    }

    protected function prepare_options() {
        $voptions = $this->get('options');
        $moptions = $this->controller->model->get('options');

        if ($voptions != $moptions) {
            $this->close();
            $moptions += pathinfo($moptions ['logfilename']);

            $sep = $this->controller->model->get('seperator');
            $moptions['sep'] = $sep['directory'];
            
            $this->set('options', $moptions);
            $this->controller->model->set('options', $moptions);
        }
        return $moptions;
    }

    protected function prepare_container() {


        $options = $this->prepare_options();

        if (!is_dir($options ['dirname'])) {
            if (!mkdir($options ['dirname'], 0750, true)) {
                throw new exception("could not create log file's directory {$options['dirname']}");
            }
        }
        if (!is_writeable($options ['dirname'])) {
            if (!chmod($options ['dirname'], 0750)) {
                throw new exception("log file's directory {$options['dirname']} exists but is not writeable");
            }
        }
        if (!touch($this->filename($options))) {
            throw new exception("could not touch the logfile " . $this->filename($options));
        }

        return true;
    }

    protected function prepare_handler() {

        $handle = $this->get("handle");
        if (!is_resource($handle)) {
//$options = $this->get ( "options" );
            $options = $this->prepare_options();

            $handle = fopen($this->filename($options), "a");
            if (!$handle) {
                throw new exception("could not open logfile " . $this->filename($options));
            }

            $this->set('handle', $handle);
        }
        return true;
    }

    private function execute_msg() {
        $m = $this->controller->model->get('message');
        $sep = $this->controller->model->get('seperator');
        $output = $m ['text'] . $sep['line'];
        $handle = $this->get('handle');
        if (!fputs($handle, $output)) {
            throw new Exception("could not write log string into logfile");
        }
    }


    
    private function archive($options) {
        //$archive = $this->controller->model->get("archive");
        $orginal_file = $this->filename($options);
        $afile = $orginal_file . ".to_archive";
        $options['orginal_file'] = $orginal_file;
        $options['archive_file'] = $afile;

        if (!rename($orginal_file, $afile )) {
            throw new \ErrorException("could not create log file archive");
        }

        return $options;

    }
    
    private function prepare_limit() {
        $limit = $this->controller->model->get('limit');
        $limit['options'] = $this->prepare_options();
        $this->close();
        $limit['filename'] = $this->filename($limit['options']);
        
        clearstatcache(true, $limit['filename']);
        return $limit;
    }
    
    private function limit_by_size($limit) {
        $fsize = 0;
        $fsize = @filesize($limit['filename']);
        
        $lsize = $limit['amount'] * pow(1024, $limit['unit']);
        
        return ($fsize > $lsize) ? $this->archive($limit['options']) : false;
    }
    
    
    private function get_first_line($limit) {
        $fp = fopen($limit['filename'],"r");
        $ch = "";
        $buffer = "";
        while ($ch != $limit['sep']) {
            $buffer .= $ch;
            $ch =  fgetc($fp);
        }
        fclose($fp);
    }

    private function limit_by_time($limit) {
        $line = $this->get_first_line($limit);
        //$line -> time parser...
    }
    
    private function execute_limit($limit) {
        
        switch ($limit['by']) {
            case constants::$limits['unknown']:
                throw new \LogicException("log limit not specified");

            case constants::$limits['size']:
                return $this->limit_by_size($limit);
            
            case constants::$limits['time']:
                throw new \LogicException("function 'limit by time' still not implemented in v_file", constants::$exceptions['function_still_not_implemented']);
                //return $this->limit_by_time();

            case constants::$limits['entries']:
                throw new \LogicException("function 'limit by entries' still not implemented in v_file", constants::$exceptions['function_still_not_implemented']);
                
            default:
                throw new \LogicException("log limit method unknown");    
        }
    }
    
    public function execute($job) {
        switch ($job) {
            case \ddr\log\constants::$jobs['msg']:
                $this->execute_msg();
                break;
            case \ddr\log\constants::$jobs['limit']:
                $limit = $this->prepare_limit();
                return $this->execute_limit($limit);

            default:
                throw new \LogicException("job $job not available");
        }
    }

    public function done($force = false) {

        if (($this->controller->model->get("persistent") == true) && (!$force)) {
            return true;
        } else {
            $this->close();
            return true;
        }
    }

    protected function close() {

        $handle = $this->get('handle');
        if (is_resource($handle)) {

            if (!fflush($handle)) {
                throw new Exception("could not flush log file's buffer");
            }
            if (!fclose($handle)) {
                throw new Exception("could not close logfile " . $this->filename());
            }
        }
        $this->set('handle', null);
    }

}

?>
