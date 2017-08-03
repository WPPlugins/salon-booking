<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Download_Exec extends Salon_Page {

	private $download_items = null;
	private $file_name = '';
	private $redirect_url = '';
	private $csv_data_cnt = 0;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->download_items = parent::set_download_item();

	}

	public function check_request() {
		$msg = null;
		Salon_Page::serverCheck(array(),$msg);

	}

	public function set_file_name ($file_name){
		$this->file_name = $file_name;
		$this->redirect_url = SL_PLUGIN_URL.'/csv/'.$file_name;
	}

	public function set_csv_data_cnt($cnt) {
		$this->csv_data_cnt = $cnt;
	}

	public function get_download_items() {
		return $this->download_items;
	}


	public function get_target() {
		return $_POST['target'];
	}

	public function get_cols() {
		return $_POST['cols'];
	}


	public function show_page() {
		if ($this->csv_data_cnt > 0 ) {
			echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
					"redirect_url":'.json_encode($this->redirect_url).' }';
		}
		else {
			echo '{	"status":"Error","message":"'.Salon_Component::getMsg('E906').'"}';
		}
	}	//show_page
}		//class

