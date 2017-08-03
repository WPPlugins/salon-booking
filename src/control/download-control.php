<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/download-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/download-component.php');

class Download_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = '';


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Download_Page';
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Download_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Download_Component($this->datas);
		$this->permits = array('Download_Page','Download_Exec');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());
		if ($this->action_class == 'Download_Exec' ) {
			$this->pages->check_request();
			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			if ($this->pages->isSalonAdmin() ) $branch_cd = 'ALL';
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
			$this->pages->set_file_name($this->comp->getDownloadFileName());
			$cnt = $this->comp->writeFile($this->pages->get_target(),$branch_cd,$this->pages->get_download_items() ,$this->pages->get_cols());
			$this->pages->set_csv_data_cnt($cnt);
		}

		$this->pages->show_page();
		wp_die();
	}
}		//class


