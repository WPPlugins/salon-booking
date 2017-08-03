<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Customer_Col_Edit extends Salon_Page {

	private $table_data = null;
	private $is_wpuserdata = false;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}


	public function isWpuserdata () {
		return $this->is_wpuserdata;
	}

	public function check_request() {

		if (!isset($_POST['column'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		if (!isset($_POST['value'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3 );
		}
		$col = intval($_POST['column']);
		if ( ( $col == 2 ) || ($col == 3 ) )  $this->is_wpuserdata = true;
		if ( empty($_POST['customer_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E002',null),1 );
		}
		$meta = '';
		$check_item = '';
		switch (intval($_POST['column'])) {
			case 2:
				if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'last_name';
				else $meta = 'first_name';
				$check_item = $meta;
				break;
			case 3:
				if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'first_name';
				else $meta = 'last_name';
				$check_item = $meta;
				break;
			case 4:
				$check_item = 'branch_cd';
				break;
			case 5:
				$check_item = 'remark';
				break;

		}
		if (empty($check_item)) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}
		if ($meta) {
			if ( stripslashes($_POST['value']) == htmlspecialchars_decode($_POST[$meta],ENT_QUOTES) ) {
				$this->table_data['value'] = stripslashes($_POST['value']);
				return false;
			}
		}
		$msg = '';
		if (Salon_Page::serverCheck(array(),$msg) == false) return false;
		if (Salon_Page::serverColumnCheck($_POST['value'],$check_item,$msg) == false ) {
			throw new Exception($msg,1 );
		}

		return true;
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode(htmlspecialchars($this->table_data['value'],ENT_QUOTES)).' }';
	}


}