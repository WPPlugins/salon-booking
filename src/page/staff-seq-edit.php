<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Staff_Seq_Edit extends Salon_Page {

	private $table_data = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function check_request() {

		if (empty($_REQUEST['type'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		if ( empty($_POST['value']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		if ( empty($_POST['staff_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3 );
		}
		$msg = '';
		if (Salon_Page::serverCheck(array(),$msg) == false) {
			throw new Exception($msg,3 );
		}
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":"" }';
	}


}