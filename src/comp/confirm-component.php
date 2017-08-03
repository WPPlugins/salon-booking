<?php

class Confirm_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	function editTargetReservationData($reservation_cd) {
		$result = $this->datas->getTargetReservationData($reservation_cd);
		if ( count($result) == 0 ) return array();

		$item_datas = $this->datas->getTargetItemData($result[0]['branch_cd']);
		$item_table = array();
		foreach ($item_datas as $k1 => $d1) {
			$item_table[$d1['item_cd']]  = array('name'=> $d1['name'],'price'=>$d1['price']);
		}
		$items = explode( ',',$result[0]['item_cds']);
		$res = array();
		foreach ($items as $k1 => $d1 ) {
			$res[] = $item_table[$d1]['name'];
		}
		$result[0]['item_name'] = implode(',',$res);
		$promotion_datas = $this->datas->getPromotionData($result[0]['branch_cd']);
		$promotion_table = array();
		foreach ($promotion_datas as $k1 => $d1 ) {
			$promotion_table[$d1['set_code']] = $d1['description'];
		}
		$result[0]['coupon_name'] = "";
		if (empty($result[0]['coupon']) ) {
			$result[0]['coupon_name'] = __('No Use',SL_DOMAIN);
		}
		else {
			$result[0]['coupon_name'] = $promotion_table[$result[0]['coupon']];
		}
		if ($result[0]['staff_cd'] == Salon_Default::NO_PREFERENCE) 	$result[0]['staff_name'] = __('Anyone',SL_DOMAIN);
		else $result[0]['staff_name'] = $this->datas->getUserName($result[0]['user_login']);
		if ($result[0]['status'] == Salon_Reservation_Status::COMPLETE) $result[0]['status_name'] = __('reservation completed',SL_DOMAIN);
		elseif ($result[0]['status'] == Salon_Reservation_Status::TEMPORARY) $result[0]['status_name'] = __('reservation temporary',SL_DOMAIN);
		elseif ($result[0]['status'] == Salon_Reservation_Status::DELETED) $result[0]['status_name'] = __('reservation canceled',SL_DOMAIN);
		else $result[0]['status_name'] = __('no status',SL_DOMAIN);
		return $result[0];
	}



	public function editTableData ($reservation_data) {

		$set_data['reservation_cd'] = $reservation_data['reservation_cd'];
		if ( $_POST['type'] == 'cancel' ) {
			$set_data['status'] = Salon_Reservation_Status::DELETED;
		}
		elseif  ( $_POST['type'] == 'exec' ){
			$set_data['status'] = Salon_Reservation_Status::COMPLETE;

			$user_login = '';
			$user_result = $this->datas->checkUserlogin($reservation_data['email'], $reservation_data['tel'] ,$reservation_data['name'],$user_login);
			if (count($user_result) > 0 ){
				$set_data['user_login'] = $user_result[0]['user_login'];
			}
			else {
				if (empty($_POST['is_regist']) ){
					$set_data['user_login'] = $reservation_data['branch_cd'].':'.$reservation_data['email'].':'.$reservation_data['tel'];
				}
				else {
					$set_data['user_login'] = $this->datas->registCustomer($reservation_data['branch_cd'],$reservation_data['email'], $reservation_data['tel'] ,$reservation_data['name'],__('registerd by confirm process',SL_DOMAIN),'','','',true);
				}
			}
		}
		return $set_data;

	}


}