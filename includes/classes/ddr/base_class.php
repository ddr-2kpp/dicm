<?PHP
namespace ddr;

//TODO: recurse applying, if an array needs to apply

class base_class implements base_interface {
    
    protected $parent;
    protected $me;
    protected $default;
    protected $props;

    protected function var_type($var) {

        $result = 0;
        if (is_array($var))
            $result += 1;
        if (is_bool($var))
            $result += 2;
        if (is_callable($var))
            $result += 4;
        if (is_float($var))
            $result += 8;
        if (is_int($var))
            $result += 16;
        if (is_null($var))
            $result += 32;
        if (is_numeric($var))
            $result += 64;
        if (is_object($var))
            $result += 128;
        if (is_resource($var))
            $result += 256;
        if (is_string($var))
            $result += 512;
        return $result;
    }

    protected function same_type($var1, $var2) {

        return $this->var_type($var1) == $this->var_type($var2);
    }

    protected function e_not_defined($prop, $val, $e_not_defined) {

        if (($e_not_defined !== null) || ($this->default ['e'] ['not_declared'])) {
            throw new \OutOfRangeException("not dedefined argument given: src:" . $prop . " - " . $val);
        }
    }

    protected function e_wrong_type($prop, $val, $e_wrong_type) {

        if (($e_wrong_type !== null) || ($this->default ['e'] ['wrong_type'])) {
            throw new \InvalidArgumentException("invalid argument type given: src:" . gettype($prop) . " - des:" . gettype($val));
        }
    }

    protected function push($des, &$props) {
        $args = func_get_args();
        array_shift($args);
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    $this->{$des}[$key] = $value;
                }
            } else {
                $arr[$arg] = "";
            }
        }
    }

    /*
     * protected function apply($des, $props, $default = false, $e_not_defined = null, $e_wrong_type = null) { return $this->_apply("props", $props, $default, $e_not_defined, $e_wrong_type); } protected function apply_default($props, $default = false, $e_not_defined = null, $e_wrong_type = null) { return $this->_apply("default", $props, $default, $e_not_defined, $e_wrong_type); }
     */

    protected function apply($des, $props, $default = false, $e_not_defined = null, $e_wrong_type = null) {

        try {

            foreach ($props as $prop => $val) {
                if (!isset($this->{$des} [$prop]) && !is_null($this->{$des} [$prop])) {
                    $this->e_not_defined($des . "[" . $prop . "]", $props[$prop], $e_not_defined);

                    if (($default) && (isset($this->default [$prop]))) {
                        $this->{$des} [$prop] = $this->default [$prop];
                    }
                } else {
                    if (!$this->same_type($this->{$des} [$prop], $props [$prop])) {
                        $this->e_wrong_type($prop, $des . "[" . $prop . "]", $e_wrong_type);
                    }
                    $this->{$des} [$prop] = $props [$prop];
                }
            }
        } catch (exception $e) {
            ddr::catch_exception($e);
        }
    }

// eof apply($src, $des, $e_non_declared = true, $e_non_wrong_type = true)

    public function get_default($prop, $default = null, $e_not_defined = null) {

        return $this->_get("default", $prop, $default = null, $e_not_defined = null);
    }

// eof get($prop, $default = null, $e_not_defined = null)

    public function get($prop, $default = null, $e_not_defined = null) {

        return $this->_get("props", $prop, $default = null, $e_not_defined = null);
    }

// eof get($prop, $default = null, $e_not_defined = null)

    protected function _get($src, $prop, $default = null, $e_not_defined = null) {

        try {

            if (!array_key_exists($prop, $this->{$src})) {
                $this->e_not_defined($src . "[" . $prop . "]", null, $e_not_defined);
            }

            if (is_null($this->{$src}[$prop])) {
                if (is_null($default)) {
                    if (array_key_exists($prop, $this->default)) {
                        return $this->default [$prop];
                    } else {
                        return null;
                    }
                } else {
                    return $default;
                }
            } else {
                return $this->{$src} [$prop];
            }
        } catch (exception $e) {
            while ($e = $e->getPrevious()) {
                printf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
            }
        }
    }

    public function set_default($prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null) {

        $this->_set("default", $prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null);
    }

// eof set($prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null)

    public function set($prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null) {

        $this->_set("props", $prop, $val, $default, $e_not_defined, $e_wrong_type);
    }

// eof set($prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null)

    protected function _set($des, $prop, $val = null, $default = null, $e_not_defined = null, $e_wrong_type = null) {

        try {
            if (!array_key_exists($prop, $this->{$des})) {
                $this->e_not_defined($des . "[" . $prop . "]", $val, $e_not_defined);
            }

            if (!$this->same_type($this->{$des}[$prop], $val)) {
                $this->e_wrong_type($des . "[" . $prop . "]", $val, $e_wrong_type);
            }

            if (is_null($val)) {
                if (is_null($default)) {
                    if (is_null($this->default [$prop])) {
                        $this->{$des} [$prop] = null;
                    } else {
                        $this->{$des} [$prop] = $this->default [$prop];
                    }
                } else {
                    $this->{$des} [$prop] = $default;
                }
            } else {
                $this->{$des} [$prop] = $val;
            }
        } catch (exception $e) {
            while ($e = $e->getPrevious()) {
                printf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
            }
        }
    }

    public function __construct(&$parent = null, $e = null) {
        $this->parent = (is_object($parent)) ? $parent : null;
        $this->me = &$this;
        $this->default = array();
        $this->props = array();

        // e = exception
        $this->default ['e'] = array(
            'not_declared' => false,
            'wrong_type' => false
        );

        if (!is_null($e)) {
            $this->apply("default", $e);
        }
    }

// eof __construct(&$parent = null, $e = null)

    public function done($force = false) {
        try {
            throw new \Exception("i need to get overloaded");
        } catch (Exception $e) {
            while ($e) {
                printf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
                $e = $e->getPrevious();
            }
        }
    }

    public function __destruct() {

        unset($this->me);
        unset($this->parent);
    }

}

// eoc \ddr\base_class(&$parent = null, $e = null)
?>
