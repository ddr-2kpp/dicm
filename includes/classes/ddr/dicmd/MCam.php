<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr\dicmd;


/**
 * Description of MCam
 *
 * @author ddr
 * @version v0.0.1b
 */
class MCam extends \ddr\dicmd\base_class {
    const MAX_JPEG_SIZE = 40960; // 40 kByte
    const STREAM_TIMEOUT = 15; // 15 seconds
    const FN_USEC = 1000; // microtime() = 0.56211600 * 1000  
    
    public $idx;
    protected $name;
    protected $ip;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    
    protected $src;
    protected $path;
    protected $path_info;

    protected $image;

    private $sp;
    
    private function get_config() {
        $this->log->msg("getting camera config #" . $this->idx, $this->LL_INFO);       
        $props = array("ip","host","port","user","pass","src","path","name");
        foreach ($props as $prop) {
            $this->$prop = $this->parent->config_file->get($prop,"cameras", $this->idx);
        }
        $this->path_info = pathinfo($this->path);
        $this->log->msg("done." . $this->idx, $this->LL_DEBUG);
    }

    
    private function get_url() {
        $pi =& $this->path_info;
        return "http://{$this->user}:{$this->pass}@{$this->ip}:{$this->port}" . $pi['dirname'] . "/" . $pi['basename'];
    }

    
    private function chk_dirname($dir) {
        if (!is_dir($dir)) {
            $this->log->msg("storage folder '$dir' doesn't exist, trying to create it.", $this->LL_INFO);
            if (!mkdir($dir, octdec((int)$this->parent->config_file->get("path_permission","storage")),true)) {
                $this->log->msg("could not create storage folder!",$this->LL_ERROR);
                return false;
            }
        }
        if (!is_writeable($dir)) {
            $this->log->msg("storage folder '$dir' is not write able, trying to change this.", $this->LL_INFO);
            if (!chmod($dir, octdec((int)$this->parent->config->get("path_permission","storage")))) {
                $this->log->msg("could make storage folder writeable", $this->LL_ERROR);
                return false;
            }
        }
        return true;
    }

    
    private function get_livename() {
        $path = $this->parent->config_file->get("path","storage") . "/camera-{$this->idx}";
        if (!$this->chk_dirname($path)) {
            return false;
        }
        return $path . "/live.jpg";
    }
    
    private function get_filename() {
        $path = $this->parent->config_file->get("path","storage") . "/camera-{$this->idx}/" . date("Y-m-d") . "/" . date("H") . "/" . date("i");
        if (!$this->chk_dirname($path)) {
            return false;
        }
        $ms = sprintf("%04d",(int)(array_shift(explode(" ", microtime())) * self::FN_USEC));
        
        $file = date("s") . "-" . $ms . ".jpg";
        return $path . "/" . $file;
    }
    
    
    private function open_file($fname) {
  
        $this->fp = fopen($fname,"wb");
        if (!$this->fp) {
            $this->log->msg("could not open image file '$fname' for writing!", $this->LL_ERROR);
        }
    }

    
    private function open_stream() {
        $url = $this->get_url();
        $this->log->msg("trying to get image from $url", $this->LL_DEBUG);
        $this->sp = fopen($url,"rb");
        if (!$this->sp) {
            $this->log->msg("could not open image url {$this->get_url()}", $this->LL_WARNING);
        } else {
            $this->stream_timeout();
        }
    }

    
    private function stream_timeout() {
        if (!stream_set_timeout($this->sp, self::STREAM_TIMEOUT)) {
            $this->log("could not set stream time out", $this->LL_WARNING);
        }
        
    }
    
    
    private function dl_image_jpeg() {
        $this->open_stream();
        
        if ($this->sp) {
            $this->image = stream_get_contents($this->sp);
        }
        $this->close_stream();
    }
    
    
    private function dl_image_mjpeg() {
        $this->open_stream();
        $sp =& $this->sp;
        while ($line = trim(fgets($sp))) {
            if ($line == "Content-Type: image/jpeg") {
                break;
            }
        }
        $line = fgets($sp);
        $size = (int)array_pop(explode(":",$line));
        $line = fgets($sp);
 
        
        $this->image = stream_get_contents($sp, $size);
    }

    
    private function close_stream() {
        fclose($this->sp);
        $this->sp = false;
    }
    
    public function __construct(\ddr\dicmd $dicmd, $idx = 0) {
        parent::__construct($dicmd);
        $this->log->msg("loading camera model #" . $idx, $this->LL_INFO);
        
        $this->idx = $idx;
        
        $this->get_config();
        $this->log->msg("loaded.",$this->LL_INFO);
    }
    
    
    public function dl_image() {
        switch ($this->src) {
            case "jpeg":
                $this->dl_image_jpeg();
                break;
            case "mjpeg":
                $this->dl_image_mjpeg();
                break;
            default:
                $this->log->msg("getting images from source {$this->src} not implementet", $this->LL_ERROR);
                break;
        }
    }

    private function close($fname) {
        if (fclose($this->fp)) {
            $fperm = $this->parent->config_file->get("file_permission", "storage");
            if ($fperm != "") {
                $this->log->msg("file_permissions $fperm set in configuration, we're aplying them", $this->LL_INFO);
                if (!chmod ($fname, octdec((int) $fperm))) {
                    $this->log->msg("could not set file permissions to $fperm as specified.", $this->LL_WARNING);
                }
            }
        }
        return true;
    }
    
    private function save_image_file($fname) {
        $this->open_file($fname);
        if (!$this->fp) {
            return false;
        }
        if (!fwrite($this->fp, $this->image, strlen($this->image))) {
            fclose($this->fp);
            $this->log->msg("could not save image data to $fname", $this->LL_ERROR);
            return false;
        }
        
        return $this->close($fname);
    }
    
    public function save_image() {
        return $this->save_image_file($this->get_filename()) &&
               $this->save_image_file($this->get_livename());
    }
}

