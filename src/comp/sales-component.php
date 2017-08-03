<?php

class Sales_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function editShowData($branch_cd,$result) {

		Salon_Component::editSalesData($this->datas->getTargetItemData($branch_cd,false),$this->datas->getTargetStaffData($branch_cd,true),$result,$this->datas->getPromotionData($branch_cd,false,false,true));
		return $result;
	}



	public function editTableData (&$user_login) {

		$set_data['reservation_cd'] = intval($_POST['reservation_cd']);
		if ( $_POST['type'] != 'deleted' ) {
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$day_edit = Salon_Component::editRequestYmdForDb($_POST['target_day']);
			$set_data['time_from'] = $day_edit." ".$_POST['time_from'];
			$set_data['time_to'] = $day_edit." ".$_POST['time_to'];
					//２４時声の確認
			$from = new DateTime($set_data['time_from']);
			$to = new DateTime($set_data['time_to']);
			//fromが２４時前でtoが２４時超えだったら逆転してるはず
			if ($from > $to ) {
// 				$to->add(new DateInterval('P1D'));
				$to->modify("+1 day");
				$set_data['time_to'] = $to->format('Y-m-d H:i');
			}
			$set_data['item_cds'] = $_POST['item_cds'];
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['customer_cd'] = 0;
			$set_data['status'] = Salon_Reservation_Status::INIT;
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['price'] = $_POST['price'];

			$set_data['coupon'] ="";
			if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
				$set_data['coupon'] = stripslashes($_POST['coupon']);
			}



			if ($_POST['type'] == 'inserted_reserve' ){
				$set_data_reservation = array();
				$set_data_reservation['branch_cd'] = $set_data['branch_cd'];
				$set_data_reservation['staff_cd'] = $set_data['staff_cd'];
				$set_data_reservation['time_from'] =  $set_data['time_from'];
				$set_data_reservation['time_to'] = $set_data['time_to'];
				$set_data_reservation['status'] = Salon_Reservation_Status::DUMMY_RESERVED;
				$set_data_reservation['remark'] = __('registed result without reservation',SL_DOMAIN);
				$set_data_reservation['item_cds'] = $_POST['item_cds'];

				$set_data_reservation['non_regist_name'] = stripslashes($_POST['name']);
				$set_data_reservation['non_regist_email'] = $_POST['mail'];
				$set_data_reservation['non_regist_tel'] = $_POST['tel'];
				$set_data_reservation['non_regist_activate_key'] = substr(md5(uniqid(mt_rand(),1)),0,8);


				if (empty($_POST['regist_customer'] ) ) $regist_customer = false;
				else $regist_customer = true;

				$user_login = $_POST['user_login'];
				if (empty($user_login ) ){
					$user_login = $this->datas->registCustomer($set_data['branch_cd'],$set_data_reservation['non_regist_email'], $set_data_reservation['non_regist_tel'] ,$set_data_reservation['non_regist_name'],__('registerd by result register process',SL_DOMAIN),'','','',$regist_customer,false);
				}
				$set_data_reservation['user_login'] = $user_login;
				$set_data_reservation['coupon'] = $set_data['coupon'];
				$set_data['reservation_cd'] = $this->datas->insertSql('salon_reservation ',$set_data_reservation,'%d,%d,%s,%s,%d,%s,%s,%s,%s,%s,%s,%s,%s');
			}
		}

		return $set_data;

	}



}