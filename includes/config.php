<?PHP
define("CONFIG_PATH", "./etc");

define("INCLUDE_PATH", "./includes");
define("CLASS_INCLUDE_PATH", INCLUDE_PATH . "/classes");


/*
 * any changes beyond this point, may affect that the script is not running script... after this info, no error reporting/ proofing is installed.
 */

function ddr_autoload($class) {

    if (!function_exists("my_log")) {
        function my_log($msg) {
            static $my_log;

            if (!$my_log) {
                if ((array_key_exists("log", $GLOBALS)) && (is_a($GLOBALS['log'], "\\ddr\\log\\c" , true))) {
                    global $log;
                    $my_log = &$log;
                } else if ((array_key_exists("ddr_log", $GLOBALS)) && (is_a($GLOBALS['ddr_log'], \ddr\log\c, true))) {
                    global $ddr_log;
                    $my_log = &$ddr_log;
                } else {
                    $my_log = false;
                }
            }

            if ($my_log) {
                $my_log->msg($msg,  \ddr\log\constants::$levels['info']);
            }
        }
    }
    $php_class = explode("\\", $class);
    $php_class = array_pop($php_class);
    if (!class_exists($php_class, false)) {
        $classfile = CLASS_INCLUDE_PATH . "/" . str_replace("\\", "/", $class) . ".php";

        my_log("try loading class $class - file: $classfile");
        
        //echo "\n\n\$class:$class\nfile:$classfile\n";
        try {

            if (is_file($classfile)) {
                include $classfile;
            } else {
                my_log("failed to load class $class - file: $classfile");
                throw new Exception("class $classfile not found!");
            }
        } catch (Exception $e) {
            ddr_catch_exception($e);
        }
        my_log("class $class successfully loaded");
    }
}

if (!spl_autoload_register("ddr_autoload")) {
    die("problem while registering autoload function");
}

function ddr_catch_exception($e) {
    while ($e) {
        printf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
        $e = $e->getPrevious();
    }
    die();
}

?>
