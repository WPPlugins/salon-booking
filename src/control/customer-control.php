<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/customer-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/customer-component.php');

class Customer_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Customer_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Customer_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Customer_Component($this->datas);
		$this->permits = array('Customer_Page','Customer_Init','Customer_Edit','Customer_Col_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		//現店舗コード
		$user_login = $this->datas->getUserLogin();
		$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
		$branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
		$this->pages->set_user_branch_cd($branch_cd);


		if ($this->action_class == 'Customer_Page' ) {
			$this->pages->set_branch_datas($this->datas->getAllBranchData());
			$this->pages->set_customer_rank_datas($this->datas->getCustomerRank());

		}
		elseif ($this->action_class == 'Customer_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitData($this->datas->getInitDatas(),$branch_cd));
		}
		elseif ($this->action_class == 'Customer_Edit' ) {
			$this->pages->check_request();
			if ( $_POST['type'] != 'deleted' ) {
				$user_datas = $this->comp->editUserData();
				$this->pages->set_table_data($user_datas);
				$this->pages->set_user_id($this->datas->setUserId($user_datas));
			}
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$res['customer_cd'] = $this->datas->insertTable( $res );
//				$this->pages->set_customer_cd($this->datas->insertTable( $res ));
//				$name = $this->datas->getBranchData($this->pages->get_branch_cd(),'name');
//				$this->pages->set_branch_name($name['name']);
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
//				$name = $this->datas->getBranchData($this->pages->get_branch_cd(),'name');
//				$this->pages->set_branch_name($name['name']);
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res );
			}
			$reRead = $this->comp->editInitData($this->datas->getCustomerDataByCustomercd($res['customer_cd']));
			if ($_POST['type'] == 'deleted' ) $reRead[0]['branch_cd'] = '';
			$this->pages->set_table_data($reRead[0]);

		}
		elseif ($this->action_class == 'Customer_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			if ($this->pages->check_request() ) {
				if ($this->pages->isWpuserdata() ) {
					$res = $this->comp->editColumnDataForWpUser();
					$this->datas->updateWpUser($res);
				}
				else {
					$res = $this->comp->editColumnData();
					$this->datas->updateColumn($res);
				}
				$this->pages->set_table_data($res);
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Customer_Page' ) wp_die();
	}
}		//class

