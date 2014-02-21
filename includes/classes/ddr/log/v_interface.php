<?PHP
namespace ddr\log {

  interface v_interface extends \ddr\base_interface {

    public function __construct(\ddr\log\c $controller);

    public function prepare($job);
    public function execute($job);
    
    // now in base_interface - to make sure, everything is getting cleaned at the end
    //public function done($force = false);
    
    // now in base_interface - to make sure, everything is getting cleaned at the end
    //public function __destruct();
  }
}
?>
