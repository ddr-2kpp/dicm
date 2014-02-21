<?PHP

namespace ddr;

interface base_interface {
	
	// to make sure, everything is getting cleaned at the end
	public function __destruct();

	// to make sure, everything is getting cleaned at the end
	public function done($force = false);

}

?>
