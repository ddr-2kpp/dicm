<?PHP
namespace ddr\log;

class v_none extends \ddr\base_class implements v_interface {
	public function __construct(\ddr\log\c $controller) {}
	
	public function prepare($job) {}
	public function execute() {}
	
	public function done($force = true) {}
	public function __destruct() {}
}
?>