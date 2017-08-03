<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Record_Edit extends Salon_Page {
	
	private $table_data = null;

	
	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	
	public function check_request() {
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['reservation_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}
		if (Salon_Page::serverCheck(array(),$msg) == false) {
			throw new Exception($msg );
		}
	}

	public function show_page() {
		$res = array();
		
		$res['record'] = unserialize($this->table_data['record']);

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}