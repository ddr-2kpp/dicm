<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ddr;


/**
 * Description of config
 *
 * @author ddr - ddr-2kpp@web.de
 * @version 0.0.1b
 */
class config {

    protected $exception;

    private $data;

    
    private function chk_user($user) {
        if ($user === false) {
            return true;
        }
    }

    private function chk_group($group) {
        if ($group === false) {
            return true;
        }
    }

    private function chk_access($access) {
        if ($access === false) {
            return true;
        }
    }
    
    
    private function read_config($config_file) {
        $this->data = parse_ini_file($config_file, true);
        if (count($this->data) > 0) {
            return true;
        } else {
            $this->exception['read_config'] = "could not read any data";
            return false;
        }
    }

    
    public function __construct($config_file, $chk_user = false, $chk_group = false, $chk_access = false) {
        try {
            switch (true) {
                case !is_file($config_file):
                    throw new \ddr\config_exception("config file '$config_file' doesn't exist.");
                case !is_readable($config_file):
                    throw new \ddr\config_exception("config file '$config_file' is not readable.");
                case !$this->chk_user($chk_user):
                    throw new \ddr\config_exception("config file's owner problem: {$this->exception['chk_owner']}.");
                case !$this->chk_group($chk_group):
                    throw new \ddr\config_exception("config file's group problem: {$this->exception['chk_group']}.");
                case !$this->chk_access($chk_access):
                    throw new \ddr\config_exception("config file's permission problem: {$this->exception['chk_access']}.");
                case !$this->read_config($config_file):
                    throw new \ddr\config_exception("could not read config file's content: {$this->exception['config_content']}.");
                default:
                    return true;
            }
        } catch (Exception $ex) {
            die("error while parsing config file '$config_file':\n" . print_r($ex));
        }
    }
    
    
    public function get($prop, $section = false, $idx = false) {
        if (!$section) {
            return $this->data[$prop];
        }
        if ($idx === false) {
            return $this->data[$section][$prop];
        }    
        return $this->data[$section][$prop][$idx];
    }
    
}

