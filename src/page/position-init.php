<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Position_Init extends Salon_Page {
	
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
			$this->init_datas[$k1]['name'] = htmlspecialchars($d1['name'],ENT_QUOTES);
			$this->init_datas[$k1]['wp_role'] = htmlspecialchars($d1['wp_role'],ENT_QUOTES);
			unset($this->init_datas[$k1]['delete_flg']);
			unset($this->init_datas[$k1]['insert_time']);
			unset($this->init_datas[$k1]['update_time']);
		}
		$this->echoInitData($this->init_datas);
	}
}