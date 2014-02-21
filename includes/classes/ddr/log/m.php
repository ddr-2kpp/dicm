<?PHP

namespace ddr\log {

    class m extends \ddr\base_class {

        // prootected $defaults; - defined in class ddr_model

        private $controller;

        public function __construct(\ddr\log\c $controller, $defaults) {

            parent::__construct($controller);
            $this->controller = &$controller;

            $default = array(
                'name' => "ddr_log",
                'date' => "",
                'time' => "",
                'ms' => "0",
                'type' => \ddr\log\constants::$types['none'],
                'options' => array(),
                'persistent' => false,
                'level' => \ddr\log\constants::$levels['debug'],
                'msg' => "ddr_log initiated",
                'updated' => false,
                'format' => array(
                    'date' => "[Y-m-d]",
                    'time' => "[H:i:s]",
                    'ms' => "5"
                ),
                'parts' => \ddr\log\constants::$parts['all'],
                'sequence' => array(
                    \ddr\log\constants::$parts['date'],
                    \ddr\log\constants::$parts['time'],
                    \ddr\log\constants::$parts['ms'],
                    \ddr\log\constants::$parts['name'],
                    \ddr\log\constants::$parts['level'],
                    \ddr\log\constants::$parts['msg']
                ),
                'seperator' => array(
                    'directory' => "/",
                    'field' => " ",
                    'line' => "\n"
                ),
                'message' => array(),
                'limit' => array (
                    'by' => \ddr\log\constants::$limits['size'],
                    'unit' => \ddr\log\constants::$units['mbyte'],
                    'amount' => 10,
                    'check' => \ddr\log\constants::$checks['@init']
                ),
                'archive' => array (
                    'method' => \ddr\log\constants::$archive['compress_extern'],
                    'count' => 5,
                    'options' => array(
                        'command' => "/usr/bin/xz",
                        'params' => "-z -9 -e {src}", //{src} will be replaced with source file, the same with {des}, if given
                        'extension' => "xz",
                    ),
                    'in_background' => false,
                ),
            );
            $this->push("default", $default);
            //$this->apply ( "default", $default );

            $this->props = $this->default;

            try {

                $this->apply("default", $defaults);
                $this->apply("props", $defaults);
            } catch (exception $e) {

                $this->controller->exception->handle($e);
            }
        }

// eof __construct(ddr_log_c $controller, $defaults)

        public function execute($process) {
            // $process = array('job' => "", 'msg' => "", 'level' => log_levels:: ...)
            switch ($process ['job']) {
                case "msg" :
                    $this->set("msg", $process ['msg']);
                    $this->set("level", $process ['level']);

                    $format = $this->get("format");

                    $this->set("date", date($format ['date']));
                    $this->set("time", date($format ['time']));

                    $ms = explode(" ", microtime());
                    $ms = (int) ($ms [0] * pow(10, (int)$format ['ms']));
                    $this->set("ms", sprintf("%0{$format['ms']}d", $ms) );

                    $this->process_job();
            }
            return;
        }

// eof execute($process)

        private function process_job() {

            $level = $this->get("level");
            if (($level == \ddr\log\constants::$levels['always']) || ($level >= $this->get_default("level"))) {
                $message = array('index' => array(), 'assoc' => array(), 'text' => "");
                $sep = $this->get("seperator");

                $this->set("updated", true);

                $parts = $this->get("parts");
                foreach ($this->get("sequence") as $idx) {
                    if ($parts == ($idx | $parts)) {
                        $var = \ddr\log\constants::get_name("parts", $idx);
                        $val = $this->get($var);

                        switch ($var) {
                            case "level":
                                $val = \ddr\log\constants::get_name("levels", $val);

                                break;
                        }
                        $message ['index'] [] = $message ['assoc'] [$var] = $val;
                        $message ['text'] .= $val . $sep['field'];
                    }
                }
                $message ['text'] = substr($message['text'], 0, strlen($sep['field']) * -1);
                $this->set("message", $message);
            } else {
                $this->set("updated", false);
            }
        }


        public function done($force = false) {
            $process = array('job' => "msg", 'msg' => "logging finished.", 'level' => \ddr\log\constants::$levels['always']);
            $this->execute($process);
            
            if ($force) {
                unset($this->controller);
            }
        }

    }

    // eoc ddr_log_m extends ddr_model(ddr_log_c $controller, $defaults)
} // eon ddr\log

