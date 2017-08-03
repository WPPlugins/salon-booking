<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/config-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/config-component.php');

class Config_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Config_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Config_Data();
		$this->comp = new Config_Component($this->datas);
		$this->set_config($this->datas->getConfigData());
		$this->permits = array('Config_Page','Config_Edit');

	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Config_Page' ) {

		}
		elseif ($this->action_class == 'Config_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->datas->update( $res);
		}

		$this->pages->show_page();
		if ($this->action_class != 'Config_Page') wp_die();
	}
}		//class

