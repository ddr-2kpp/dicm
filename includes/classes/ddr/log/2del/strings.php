<?PHP
namespace ddr\log {
  
  class strings {
    const date = 1;
    const time = 2;
    const ms = 4;
    const name = 8;
    const level = 16;
    const msg = 32;
    
    const all = 63;
    const standard = 55;
    const min = 34;
    
    protected function to_var($s) {
      switch ($s) {
      case self::date:
	return "date";
      case self::time:
	return "time";
      case self::ms:
	return "ms";
      case self::name:
	return "name";
      case self::level:
	return "level";
      case self::msg:
	return "msg";
      }

    } // eof to_var($s)

  } // eoc log_strings
}
?>
