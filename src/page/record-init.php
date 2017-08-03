<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Record_Init extends Salon_Page {

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
		return intval($_POST['target_branch_cd']);
	}

	public function get_from() {
		return $_POST['from'];
	}
	public function get_to() {
		return $_POST['to'];
	}



	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['name'] = htmlspecialchars($d1['name'],ENT_QUOTES);
		}
		$this->echoInitData($this->init_datas);
	}
}