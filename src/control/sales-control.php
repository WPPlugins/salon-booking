<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/sales-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/sales-component.php');

class Sales_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Sales_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Sales_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Sales_Component($this->datas);
		$this->permits = array('Sales_Page','Sales_Init','Sales_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());


		if ($this->action_class == 'Sales_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) === false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);

			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());

			$this->pages->set_promotion_datas($this->datas->getPromotionData($branch_cd,null,null,true));

			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));
			$this->pages->set_item_datas($this->datas->getTargetItemData($branch_cd,false));
			$this->pages->set_staff_datas($this->datas->getTargetStaffData($branch_cd,false));



		}
		elseif ($this->action_class == 'Sales_Init' ) {
			$branch_cd = $this->pages->get_target_branch_cd();
			$target_day_from = $this->pages->get_target_day_from();
			$target_day_to = $this->pages->get_target_day_to();
			$this->pages->set_init_datas($this->comp->editShowData($branch_cd,$this->datas->getAllSalesData($target_day_from,$target_day_to,$branch_cd,$this->pages->get_sub_menu()),$this->datas->getPromotionData($branch_cd,null,null,true)));
		}
		elseif ($this->action_class == 'Sales_Edit' ) {
			$this->pages->check_request();
			$user_login = '';
			$res = $this->comp->editTableData($user_login);

			if (substr($_POST['type'],0,8) == 'inserted' ) {
				$this->datas->insertTable( $res );
				if ( $_POST['type'] == 'inserted_reserve'  && !empty($_POST['regist_customer'])  )
					$this->pages->set_user_pass($this->datas->getUserPass($user_login));
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res );
			}
			$table_data = $this->datas->getTargetSalesData($res['reservation_cd']);
			$show_data = $this->comp->editShowData($table_data[0]['branch_cd'],$table_data );
			$this->pages->set_table_data( $show_data[0] );
		}

		$this->pages->show_page();
		if ($this->action_class != 'Sales_Page' ) wp_die();
	}
}		//class

