<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Customer_Init extends Salon_Page {
	
	private $init_datas =  null;
	
	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function get_init_datas() {
		return $this->init_datas;
		
	}
	public function set_init_datas($init_datas) {
		$this->init_datas = $init_datas;
		
	}


	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			$this->init_datas[$k1]['first_name'] = htmlspecialchars($d1['first_name'],ENT_QUOTES);
			$this->init_datas[$k1]['last_name'] = htmlspecialchars($d1['last_name'],ENT_QUOTES);
			$this->init_datas[$k1]['address'] = htmlspecialchars($d1['address'],ENT_QUOTES);
			unset($this->init_datas[$k1]['memo']);
			unset($this->init_datas[$k1]['notes']);
		}
		parent::echoInitData($this->init_datas);
	}
}