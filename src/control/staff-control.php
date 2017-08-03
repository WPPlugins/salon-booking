<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/staff-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/staff-component.php');

class Staff_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Staff_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Staff_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Staff_Component($this->datas);
		$this->permits = array('Staff_Page','Staff_Init','Staff_Edit','Staff_Col_Edit','Staff_Seq_Edit');
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


		if ($this->action_class == 'Staff_Page' ) {
			$this->pages->set_branch_datas($this->datas->getBranchDataIncMenu());
			$this->pages->set_position_datas($this->datas->getAllPositionData(true));
			$this->pages->set_item_datas($this->comp->editStaffData($this->datas->getAllItemDataForSet(),$user_login, $branch_cd));
		}
		elseif ($this->action_class == 'Staff_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitData($this->datas->getInitDatas(), $branch_cd));
		}
		elseif ($this->action_class == 'Staff_Edit' ) {
			$this->pages->check_request();
			if ( $_POST['type'] != 'deleted' ) {
				$user_datas = $this->comp->editUserData();
				$user_datas['ID'] = $this->datas->setUserId($user_datas);
				$this->pages->set_table_data($user_datas);
			}
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);

			if ($_POST['type'] == 'inserted' ) {
				$this->datas->fixedPhoto($_POST['type'],$res['photo']);
				$res['staff_cd'] = $this->datas->insertTable( $res);
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateStaffPhotoData($res['staff_cd'],$res['photo']);
				$this->datas->updateTable( $res);
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteStaffPhotoData($res['staff_cd']);
				$this->datas->deleteTable( $res);
			}
			//完全に削除した場合のみとそれ以外で分岐
			if ( $_POST['type'] == 'deleted' && $this->datas->isCompleteDelete() ) {
				$reRead = $this->comp->editInitData($this->datas->getStaffDataByUser($res['user_login']));
			}
			else {
				$reRead = $this->comp->editInitData($this->datas->getStaffDataByStaffcd($res['staff_cd']));
			}
			$this->pages->set_table_data($reRead[0]);
		}
		elseif ($this->action_class == 'Staff_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			if ($this->pages->check_request() ) {
				if ($this->pages->isWpuserdata() ) {
					$res = $this->comp->editColunDataForWpUser();
					$this->datas->updateWpUser($res);
				}
				else {
					$res = $this->comp->editColumnData();
					$this->datas->updateColumn($res);
				}
				$this->pages->set_table_data($res);
			}
		}
		elseif ($this->action_class == 'Staff_Seq_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editSeqData();
			$this->pages->set_table_data($res);
			$this->datas->updateSeq($res,'staff','staff_cd');
		}

		$this->pages->show_page();
		if ($this->action_class != 'Staff_Page' ) wp_die();
	}
}		//class



