<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/confirm-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/confirm-component.php');

class Confirm_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Confirm_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Confirm_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Confirm_Component($this->datas);
		$this->permits = array('Confirm_Page','Confirm_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Confirm_Page' ) {
			$reservation_cd = $this->pages->get_reservation_cd();
			$this->pages->set_reservation_datas($this->comp->editTargetReservationData($reservation_cd));
			$this->pages->check_request();
		}
		elseif ($this->action_class == 'Confirm_Edit' ) {
			$reservation_cd = $this->pages->get_reservation_cd();
			$reservation_data = $this->comp->editTargetReservationData($reservation_cd);
			$this->pages->set_reservation_datas($reservation_data);
			$this->pages->check_request();
			$table_data = $this->comp->editTableData($reservation_data);
			if ( $_POST['type'] == 'exec' ) {
				$this->datas->updateTable($table_data);
			}
			elseif ( $_POST['type'] == 'cancel' ) {
				$this->datas->deleteTable($table_data);
			}
			$this->datas->sendInformationMail($this->pages->get_reservation_cd(),true);


		}

		$this->pages->show_page();
		if ($this->action_class != 'Confirm_Page' ) wp_die();
	}
}		//class


