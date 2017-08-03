<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Promotion_Init extends Salon_Page {
	
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
	
	
	public function get_target_branch_cd() {
		return $_POST['target_branch_cd'];
	}

	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			$this->init_datas[$k1]['description'] = htmlspecialchars($d1['description'],ENT_QUOTES);
			$this->init_datas[$k1]['set_code'] = htmlspecialchars($d1['set_code'],ENT_QUOTES);
		}
		
		$this->echoInitData($this->init_datas);
	}
}