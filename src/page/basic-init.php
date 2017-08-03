<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Basic_Init extends Salon_Page {
	
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
	
	public function get_target_year () {
		return $_POST['target_year'];
	}
	
	public function get_target_branch_cd() {
		return $_POST['target_branch_cd'];
	}

	public function show_page() {
		$this->echoInitData($this->init_datas);
	}
}