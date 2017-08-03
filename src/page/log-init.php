<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Log_Init extends Salon_Page {
	
	private $init_datas =  null;
	private $get_cnt = '';
	
	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);

		$this->get_cnt = intval($_POST['get_cnt']);
	}

	public function set_init_datas($init_datas) {
		$this->init_datas = $init_datas;
		
	}
	
	public function get_cnt () {
		return $this->get_cnt;
	}

	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['logged_day'] = htmlspecialchars($d1['logged_day'],ENT_QUOTES);
			$this->init_datas[$k1]['logged_time'] = htmlspecialchars($d1['logged_time'],ENT_QUOTES);
			$this->init_datas[$k1]['operation'] = htmlspecialchars($d1['operation'],ENT_QUOTES);
			$this->init_datas[$k1]['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
		}
		
		$this->echoInitData($this->init_datas);
	}
}