<?PHP
namespace ddr\log {

  class types {
    const none = 0;
    const memory = 1;
    const file = 2;
    const mysql = 4;
    
    static function to_text($t) {
      $ts = array(0 => "none",
		  1 => "memory",
		  2 => "file",
		  4 => "mysql"
		  );
      
      return $ts[$t];
    } //eof function to_text($t)

  } // eoc log_types
}
?>
