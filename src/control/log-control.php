<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/log-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/log-component.php');

class Log_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Log_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Log_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Log_Component($this->datas);
		$this->permits = array('Log_Page','Log_Init');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);


		if ($this->action_class == 'Log_Page' ) {

			$this->pages->set_config_datas($this->datas->getConfigData());

		}
		elseif ($this->action_class == 'Log_Init' ) {
			$get_cnt = $this->pages->get_cnt();
			$this->pages->set_init_datas($this->datas->getInitDatas($get_cnt));
		}

		$this->pages->show_page();
		if ($this->action_class != 'Log_Page' ) wp_die();

	}
}		//class

