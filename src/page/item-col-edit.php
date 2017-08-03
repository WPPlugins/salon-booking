<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Item_Col_Edit extends Salon_Page {

	private $table_data = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function check_request() {

		if ( empty($_POST['item_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}

		if (!isset($_POST['column'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		if (!isset($_POST['value'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3 );
		}
		$check_item = '';
		switch (intval($_POST['column'])) {
			case 2:
				$check_item = 'item_name';
				break;
			case 3:
				$check_item = 'branch_cd';
				break;
			case 5:
				$check_item = 'price';
				break;
			case 6:
				$check_item = 'remark';
				break;

		}
		if (empty($check_item)) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),4 );
		}
		$msg = '';
		if (Salon_Page::serverCheck(array(),$msg) == false) {
			throw new Exception($msg,3 );

		}
		if (Salon_Page::serverColumnCheck($_POST['value'],$check_item,$msg) == false ) {
			throw new Exception($msg,4 );
		}
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode(htmlspecialchars($this->table_data['value'],ENT_QUOTES)).' }';
	}


}