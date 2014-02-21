<?PHP

namespace ddr\log {


    class c extends \ddr\base_class {

        public $model = null;
        public $view = null;
        public $exception = null;


        public function __construct($defaults) {
            parent::__construct();
            $this->exception = new \ddr\log\e($this);
            $this->model = new \ddr\log\m($this, $defaults);


            $model_type = $this->model->get("type");
            $view_type = \ddr\log\constants::get_name('types', $model_type);
            $view = "\\ddr\\log\\v_" . $view_type;

            $this->view = new $view($this);

            $this->limit_check(\ddr\log\constants::$checks['@init']);
        }

        
        private function limit_check($check_at) {
            $limit = $this->model->get('limit');
            if ($limit['check'] == $check_at) {
                $result = $this->view->execute(\ddr\log\constants::$jobs['limit']);
                if ($result) {
                    $this->archive($result);
                }
            }
        }




        private function send_before_unlink($file, $archive) {
            if (!array_key_exists("send_before_unlink", $archive)) {
                return;
            }
            \ddr\mail_simple::to($archive['send_before_unlink']);
            \ddr\mail_simple::from(get_current_user() . "@" . gethostname(), "ddr_log's archivator");
            \ddr\mail_simple::subject("to deleted log archive");

            $message = "archive: $file\n" .
                        "size: " . filesize($file) . "bytes\n" .
                        "created: " . date("Y-m-d - H:i.s", filemtime($file) ) . "\n";

            \ddr\mail_simple::message($message);

            \ddr\mail_simple::files($file);
            return \ddr\mail_simple::send();

        }
        
        
        private function unlink($file, $archive) {
            if ($this->send_before_unlink($file, $archive) === false) {
                $this->msg("cannot remove logfile ($file), failed to mail to " . $archive['send_before_unlink'], \ddr\log\constants::$levels['always']);
                return false;
            }
            if (unlink($file) === false) {
                throw new Exception("could not unlink old archive logfile $file", \ddr\log\constants::$exceptions['unlink_file']);
            }
            return true;
        }

        
        private function old_archives($archive) {
            $old_archives = array();
            $archive_dir = dir($archive['dirname']);
            $archive_dir->rewind();
            $max = 0;
            while (false !== ($entry = $archive_dir->read())) {
                if (($entry != ".") && ($entry != "..")) {
                    $temp = explode(".", $entry);
                    if (count($temp) != substr_count($archive['logfilename'], ".") + 3 ) {
                        continue;
                    }
                    if (($temp[0] == $archive['filename']) && ($temp[3] == $archive['options']['extension'])) {
                        if (($temp[2] == (int)$temp[2]) && ((int)$temp[2] > 0)) {
                            $old_archives[$temp[2]] = $entry;
                        }
                    }
                }
            }
            krsort($old_archives);
            return $old_archives;
        }

        
        private function archives_rename($archive) {

            $old_archives = array();
            $old_archives = $this->old_archives($archive);
            
            
            if (count($old_archives) > $archive['count']) {
                $this->log("more archives found (" . count($old_archives) . ") as configured for ({$archive['count']}) - trying to clean-up",  \ddr\log\constants::$levels['always']);
            }

            $last_file = false;

            while (count($old_archives) >= $archive['count']) {
                
                if ($this->unlink($old_archives[0], $archive)) {
                    $last_file = \array_shift($old_archives);
                }
            }
            
            $no = 0;
            if ($last_file == "") {
                $last_file = $archive['orginal_file'] . ".2." . $archive['options']['extension'];  
            }
            while ($no < count($old_archives)) {
                $current_file = array_shift($old_archives);
                if (rename($current_file, $last_file ) === false) {
                    throw new Exception ("could not rename old archive '{$old_archives[0]}' to '$last_file'",constants::$exceptions['renaming_archive']);
                }
                $last_file = \array_shift($old_archives);
            }
            return $last_file;
        }


        private function archive($options) {
            /* $options = array
             *   ['orginal_file'] = $orginal_file;
             *   ['archive_file'] = $orginal_file . ".to_archive"
             */
             
            $archive = $this->model->get("archive");
            $archive += $options;
            
            switch ($archive['method']) {
                case \ddr\log\constants::$archive['delete']:
                    $this->unlink($archive['archive_file'], $archive);
                    break;
                case \ddr\log\constants::$archive['copy']:
                case \ddr\log\constants::$archive['compress_extern']:
                    $new_archive = $this->archives_rename($archive);
                    if ($new_archive == "") {
                        $new_archive = $archive['orginal_file'] . ".1";
                    } else {
                        $new_archive = str_replace("." . $archive['options']['extension'], "", $new_archive);
                    }
                    $archive['new'] = $new_archive;
                    $this->archive_process($archive);
                    break;
                default:
                    throw new \LogicException("archiving method not implemented");
            }
        }
        
        
        private function archive_process_shutdown() {
            posix_kill(posix_getpid(), SIGHUP);
        }
        
        
        private function prepare_archive_background($archive) {
            if ($archive['in_background'] === true) {

                // try to run as daemon
                
                if ($pid = pcntl_fork()) {
                    return; // we are parent
                }
                
                //we are child
                @ob_end_clean();

                fclose(STDIN);
                fclose(STDOUT);
                fclose(STDERR);

                register_shutdown_function(array($this,"archive_process_shutdown"));

                if (posix_setsid() < 0) {
                    return;
                }
                if ($pid = pcntl_fork()) {
                    return;
                }
                
            }
        }

        
        private function archive_process($archive) {

            $this->prepare_archive_background($archive);
            
            switch ($archive['method']) {
                case \ddr\log\constants::$archive['copy']:
                    $this->archive_copy($archive);
                    break;
                case \ddr\log\constants::$archive['compress_extern']:
                    $this->archive_compress_extern($archive);
                    break;
                case \ddr\log\constants::$archive['compress_intern'];
                    $this->archive_compress_intern($archive);
                    break;
            }
            
            return;
        }

        
        private function archive_copy($archive) {
            if (rename($archive['archive_file'], $archive['new']) === false) {
                throw new \Exception("could not copy logfile '{$archive['archive_file']}' to archive file '{$archive['new']}'", \ddr\log\constants::$exceptions['copy_new_archive_file']);
            }
            return;
        }

        
        private function archive_compress_extern($archive) {
            try {
                $this->archive_copy($archive);
                $params = str_replace("{src}", $archive['new'], $archive['options']['params']);

                $cmd = $archive['options']['command'];
                $this->msg("compressing archive using command \$cmd:$cmd $params", \ddr\log\constants::$levels['debug']);

                if (pcntl_exec ($cmd, explode(" ", $params)) === false) {
                    throw new \Exception ("could not compress '{$archive['new']}' using command $cmd - $params", \ddr\log\constants::$exceptions['compress_failed']);
                }
                
            } catch (Exception $e) {
                $this->controller->e->handle($e);
            }
        }
        
        
        public function msg($msg, $level = false) {
            $this->prepare();

            $this->limit_check(\ddr\log\constants::$checks['@msg']);

            if ($level === false) {
                $level = $this->model->get_default('level');
            }
            $this->execute(array('job' => "msg", 'msg' => $msg, 'level' => $level));
            $this->output();

            $this->done();
        }


        public function prepare() {

            $this->view->prepare(\ddr\log\constants::$jobs['log']);
        }


        public function execute($process) {
            switch ($process['job']) {

                case "msg":
                    $this->model->execute($process);
                    break;
            }
        }


        public function output() {
            if ($this->model->get("updated")) {
                $this->view->execute(\ddr\log\constants::$jobs['msg']);
            }
        }


        public function done($force = false) {
            if ($force === true) {
                $this->limit_check(\ddr\log\constants::$checks['@done']);
            }

            $this->view->done($force);
        }


        public function __destruct() {
            $this->done(true);
            parent::__destruct();
        }


    }


} // eon ddr\log
?>
