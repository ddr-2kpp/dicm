<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ddr\log;

/**
 * Description of limits
 *
 * @author ddr
 */
abstract class constants extends \ddr\base_constants {

    // what jobs need to be available
    static $jobs = array(
        'log' => 0,
        'msg' => 0,
        'limit' => 1,
    );
    
    // where to save the log
    static $types = array(
        'none' => 0,
        'memory' => 1,
        'file' => 2,
        'mysql' => 4,
    );
    
    //log levels
    static $levels = array(
        'always' => 0,
        'debug' => 1,
        'info' => 2,
        'warning' => 4,
        'error' => 8,
    );
    
    //parts a log entry has to have
    static $parts = array(
        'date' => 1,
        'time' => 2,
        'ms' => 4,
        'name' => 8,
        'level' => 16,
        'msg' => 32,
        'all' => 63,
        'standard' => 55,
        'min' => 34,
    );
    
    // limit the log by
    static $limits = array(
        'unknown' => 0,
        'size' => 1,
        'time' => 2,
        'entries' => 4,
    );

    // units this limits need
    static $units = array(
        'unknown' => -1,
        'byte' => 0,
        'kbyte' => 1,
        'mbyte' => 2,
        'gbyte' => 3,
        
        'day' => 4,
        'week' => 5,
        'month' => 6,
        'year' => 7,
        
        'date' => 9,
        'time' => 10,
        
        'entries' => 8
    );

    // check limits when
    static $checks = array(
        'never' => 0,
        '@msg' => 1,
        '@init' => 2,
        '@done' => 4
    );
    
    // what to do, if limits reached
    static $archive = array(
        'undefined' => -1,
        'compress_extern' => 0,
        'compress_intern' => 1,     // maybe used 
        'delete' => 2,
        'copy' => 4
    );

    // exceptions, which may occure
    // negative one's should cancel execution
    // positive one's sould give an 'always'-log entry
    static $exceptions = array(
        'none' => 0,
        'unlink_file' => 1,
        'copy_new_archive_file' => 2,
        'function_still_not_implemented' => 3,
    );
}
