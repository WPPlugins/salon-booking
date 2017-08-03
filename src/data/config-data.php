<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Config_Data extends Salon_Data {
	
	
	function __construct() {
		parent::__construct();
	}


	public function update ($table_data){
		$this->setConfigData($table_data);
		return true;
		
	}
	
}