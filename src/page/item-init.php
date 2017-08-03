<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Item_Init extends Salon_Page {
	
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
		$res = array();
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['name'] = htmlspecialchars($d1['name'],ENT_QUOTES);
			$this->init_datas[$k1]['short_name'] = htmlspecialchars($d1['short_name'],ENT_QUOTES);
			$this->init_datas[$k1]['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			$this->init_datas[$k1]['branch_name'] = htmlspecialchars($d1['branch_name'],ENT_QUOTES);
			unset($this->init_datas[$k1]['memo']);
			unset($this->init_datas[$k1]['notes']);
			unset($this->init_datas[$k1]['delete_flg']);
			unset($this->init_datas[$k1]['insert_time']);
			unset($this->init_datas[$k1]['update_time']);
		}
		$this->echoInitData($this->init_datas);
	}
}