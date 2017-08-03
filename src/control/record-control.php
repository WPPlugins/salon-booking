<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/record-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/record-component.php');

class Record_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Record_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Record_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Record_Component($this->datas);
		$this->permits = array('Record_Page','Record_Init','Record_Edit','Record_Get_Month');

	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Record_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) === false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());
			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));

			$this->pages->set_category_datas($this->datas->getCategoryDatas(Salon_Table_id::RECORD));

			$this->pages->set_reservation_datas($this->comp->getReservationDataByDate($branch_cd,"","",$this->pages->get_customer_cd()));

		}
		elseif ($this->action_class == 'Record_Get_Month' ) {
			$branch_cd = $this->pages->get_target_branch_cd();
			$this->pages->set_init_datas($this->comp->getReservationDataByDate($branch_cd,$this->pages->get_from(),$this->pages->get_to(),$this->pages->get_customer_cd()));
		}
// 		elseif ($this->action_class == 'Record_Init' ) {
// 			$branch_cd = $this->pages->get_target_branch_cd();
// 			$this->pages->set_init_datas($this->comp->getReservationDataByDate($branch_cd,$this->pages->get_from(),$this->pages->get_to()));
// 		}
		elseif ($this->action_class == 'Record_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->comp->serverCheck($res);
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->datas->insertTable( $res);
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res);
			}
		}
		$this->pages->show_page();
		if ($this->action_class != 'Record_Page' ) wp_die();
	}
}		//class


