<?php

class Reservation_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function editShowData($branch_cd,$result) {

		Salon_Component::editSalesData($this->datas->getTargetItemData($branch_cd),$this->datas->getTargetStaffData($branch_cd),$result);
		return $result;
	}



	public function editTableData () {

		if ( $_POST['type'] == 'deleted') {
			$set_data['reservation_cd'] = intval($_POST['reservation_cd']);
		}
		elseif ($_POST['type'] == 'cancel' || $_POST['type'] == 'confirm') {
			$set_data['reservation_cd'] = intval($_POST['reservation_cd']);
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['reservation_cd'] = intval($_POST['reservation_cd']);
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$day_edit = Salon_Component::editRequestYmdForDb($_POST['target_day']);
			$set_data['time_from'] = $day_edit." ".$_POST['time_from'];
			$set_data['time_to'] = $day_edit." ".$_POST['time_to'];
			//２４時超え確認
			$from = new DateTime($set_data['time_from']);
			$to = new DateTime($set_data['time_to']);
// 			//fromが２４時前でtoが２４時超えだったら逆転してるはず
			if ($from > $to ) {
// 				$to->add(new DateInterval('P1D'));
				$to->modify("+1 day");
				$set_data['time_to'] = $to->format('Y-m-d H:i');
			}
			$set_data['item_cds'] = $_POST['item_cds'];
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['status'] = Salon_Reservation_Status::COMPLETE;
			$set_data['memo'] = '';
			$set_data['notes'] = sprintf(__("price:%d",SL_DOMAIN),$_POST['price']);
			$set_data['non_regist_name'] = stripslashes($_POST['name']);
			$set_data['non_regist_email'] = $_POST['mail'];
			$set_data['non_regist_tel'] = $_POST['tel'];
			$set_data['non_regist_activate_key'] = substr(md5(uniqid(mt_rand(),1)),0,8);

			if (empty($_POST['regist_customer'] ) ) $regist_customer = false;
			else $regist_customer = true;

			$user_login = $_POST['user_login'];
			if (empty($user_login ) ){
				$user_login = $this->datas->registCustomer($set_data['branch_cd'],$set_data['non_regist_email'], $set_data['non_regist_tel'] ,$set_data['non_regist_name'],__('registerd by reservation process',SL_DOMAIN),'','','',$regist_customer,false);
			}
			$set_data['user_login'] = $user_login;
			$set_data['coupon'] ="";
			if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
				$set_data['coupon'] = stripslashes($_POST['coupon']);
			}
			if ($this->datas->getConfigData('SALON_CONFIG_USE_SUBMENU') == Salon_Config::USE_SUBMENU ) {
				$edit_record = array();
				foreach ($_POST['sl_memo'] as $k1 => $d1 ){
					$edit_record[$k1] = stripslashes($d1);
				}
				$set_data['memo'] = serialize($edit_record);
			}
		}
		return $set_data;

	}

	public function serverCheck($set_data) {
		Salon_Component::serverReservationCheck($set_data,$this->datas,false);
	}


}