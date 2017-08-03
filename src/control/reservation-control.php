<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/reservation-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/reservation-component.php');

class Reservation_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Reservation_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Reservation_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Reservation_Component($this->datas);
		$this->permits = array('Reservation_Page','Reservation_Init','Reservation_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Reservation_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) === false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);

			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());

			$this->pages->set_promotion_datas($this->datas->getPromotionData($branch_cd));

			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));
			$this->pages->set_item_datas($this->datas->getTargetItemData($branch_cd,true,true));
			$this->pages->set_staff_datas($this->datas->getTargetStaffData($branch_cd));
			$this->pages->set_category_datas($this->datas->getCategoryDatas(Salon_Table_id::RESERVATION));

		}
		elseif ($this->action_class == 'Reservation_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->comp->serverCheck($res);

			if ($_POST['type'] == 'inserted' ) {
				$reservation_cd = $this->datas->insertTable( $res);
				if (!empty($_POST['regist_customer'] ) )
					$this->pages->set_user_pass($this->datas->getUserPass($res['user_login']));
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res);
				$reservation_cd = $res['reservation_cd'];
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
				$reservation_cd = $res['reservation_cd'];
			}
			elseif ($_POST['type'] == 'cancel' ) {
				$this->datas->cancelTable( $res);
				$reservation_cd = $res['reservation_cd'];
			}
			elseif ($_POST['type'] == 'confirm' ) {
				$this->datas->confirmTable( $res);
				$reservation_cd = $res['reservation_cd'];
			}
			if ($_POST['type'] != 'deleted' ) {
				$table_data = $this->datas->getTargetSalesData($reservation_cd);
				Salon_Component::editSalesData($this->datas->getTargetItemData($res['branch_cd']),$this->datas->getTargetStaffData($res['branch_cd']),$table_data);
				$this->pages->set_table_data($table_data[0]);
			}
			$this->datas->sendInformationMail($reservation_cd);

		}

		$this->pages->show_page();
		if ($this->action_class != 'Reservation_Page' ) wp_die();

	}
}		//class
