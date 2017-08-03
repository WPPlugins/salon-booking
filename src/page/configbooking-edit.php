<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Configbooking_Edit extends Salon_Page {

	private $table_data = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function check_request() {
		$msg = null;
		Salon_Page::serverCheck(array(),$msg);
		if (isset($_POST['config_restore']) && $_POST['config_restore'] == "true"){

		}
		else {
			$default = $this->setDefaultBookingItems();
			foreach ($default as $k1 => $d1 ) {
				if (array_key_exists($k1,$_POST["config_fields"]) === false ) {
					throw new Exception(Salon_Component::getMsg('E901',"No data[".$k1."]"),__LINE__ );
				}
			}
		}

	}


	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}