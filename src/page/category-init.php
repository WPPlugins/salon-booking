<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Category_Init extends Salon_Page {
	
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
			
//			$res[$k1]['sl_category_cd'] = $d1['category_cd'];
			$this->init_datas[$k1]['sl_category_name'] = htmlspecialchars($d1['category_name']);
			$this->init_datas[$k1]['category_values'] = htmlspecialchars($d1['category_values']);
			$this->init_datas[$k1]['remark'] = '';
//			$res[$k1]['sl_category_patern'] = $d1['category_patern'];
//			$res[$k1]['sl_category_value'] = $d1['category_values'];
//			$res[$k1]['sl_target_table'] = $d1['target_table_id'];
//			$res[$k1]['display_sequence'] = $d1['display_sequence'];
		}
		$this->echoInitData($this->init_datas);
	}
}