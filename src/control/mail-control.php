<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/mail-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/mail-component.php');

class Mail_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Mail_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Mail_Data();
		$this->comp = new Mail_Component($this->datas);
		$this->set_config($this->datas->getConfigData());
		$this->permits = array('Mail_Page','Mail_Edit');

	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Mail_Page' ) {

		}
		elseif ($this->action_class == 'Mail_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->datas->update( $res);
		}

		$this->pages->show_page();
		if ($this->action_class != 'Mail_Page') wp_die();
	}
}		//class


