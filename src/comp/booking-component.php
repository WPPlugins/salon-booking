<?php

class Booking_Component  {

	private $version = '1.0';

	private $datas = null;
	private $is_need_sendmail = false;

	private $mailErrorInformation = "";

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function set_sendmail () {
		$this->is_need_sendmail = true;
	}

	public function getTargetStaffData($branch_cd) {
		$result = $this->datas->getTargetStaffData($branch_cd);
		foreach ($result as $k1 => $d1 ) {
			//[PHOTO]
			$photo_result = $this->datas->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				$tmp[] = $photo_result[$i];
			}
			$result[$k1]['photo_result'] = $tmp;
			//[PHOTO]
		}
		return $result;
	}

	public function editPromotionData($branch_cd) {
		$edit_result = array();
		$result = $this->datas->getPromotionData($branch_cd);
		if ($result) {
			if ($this->datas->isPromotion() ) return $result;
			foreach ($result as $k1=>$d1 ) {
				switch ($d1['usable_patern_cd']) {
				case Salon_Coupon::UNLIMITED:
				case Salon_Coupon::FIRST:
					$edit_result[] = $d1;
					break;
				case Salon_Coupon::TIMES:
					if ($this->datas->isCustomer() || $this->datas->isStaff() )	$edit_result[] = $d1;
					break;
				case Salon_Coupon::RANK:
					if ( $this->datas->isStaff() )	$edit_result[] = $d1;
					if ($this->datas->isCustomer() && $this->datas->customerRank() >= $d1['usable_data'] )	$edit_result[] = $d1;
					break;
//				case Salon_Coupon::FIRST:
//					if (!$this->datas->isCustomer() || $this->datas->isStaff() )	$edit_result[] = $d1;
//					break;
				}
			}
		}
		return $edit_result;
	}
	public function editWorkingData($branch_cd, $branch_datas)  {
		$day_from = Salon_Component::computeDate(-1 * $this->datas->getConfigData('SALON_CONFIG_BEFORE_DAY'));

		$day_to = Salon_Component::computeDate( $this->datas->getConfigData('SALON_CONFIG_AFTER_DAY'));
		$over24 = false;
		if (+substr($branch_datas['close_time'],-4,2) > 23) $over24 = true;
		$result = $this->datas->getWorkingDataByBranchCd($branch_cd ,$day_from,$day_to,$over24 );
		$result_after = array();
		$is_normal_patern = true;
		if ($this->datas->getConfigData('SALON_CONFIG_STAFF_HOLIDAY_SET') == Salon_Config::SET_STAFF_REVERSE ) {
			$is_normal_patern = false;
		}
		foreach ($result as $k1 => $d1 ){
			$working_cds = explode( ',',$d1['working_cds']);
			if ($is_normal_patern ) {

				if (in_array(Salon_Working::DAY_OFF,$working_cds) ){
//					$result_after[$d1['day']][$d1['staff_cd']] = $d1;
					$result_after[$d1['day']][] = $d1;
				}
				elseif ( in_array(Salon_Working::USUALLY,$working_cds) ||
						in_array(Salon_Working::HOLIDAY_WORK,$working_cds)  ||
						in_array(Salon_Working::LATE_IN,$working_cds)  ||
						in_array(Salon_Working::EARLY_OUT,$working_cds)	) {
					//開店時間より前に出勤するや閉店時間より後に退勤する場合
					$op = substr($branch_datas['open_time'],-4);
					$cl = substr($branch_datas['close_time'],-4);
					$fr = substr($d1['in_time'],-4);
					$to = substr($d1['out_time'],-4);
					$set_day_close = $d1['day'];
					$set_in_time = +$d1['in_time'];
					$set_out_time = +$d1['out_time'];
					//24時間対応
					if ($over24) {
						//
						$yyyy_mm_dd_hh_mm_dd_ss = Salon_Component::computeDate(1,substr($set_day_close,0,4),substr($set_day_close,4,2),substr($set_day_close,6,2));
						$set_day_close = substr(str_replace("-","",$yyyy_mm_dd_hh_mm_dd_ss),0,8);
						//出勤時間が開店時間より前→24時を超えた出勤　3:00～6:00のように
						if ($fr < $op) {
							$d1['day']=$d1['before_day'];
							$fr = Salon_Component::editOver24Calc($fr);
							$set_in_time += 2400;
						}
						//退勤時間が開店時間より前→24時を超えた出勤　3:00～6:00のように
						if ($to < $op) {
							$to = Salon_Component::editOver24Calc($to);
							$set_out_time += 2400;
						}
					}
					//出勤時間が開店時間より後ｰ>遅刻　出勤時間までは休み
					if ($fr > $op ) {
						$tmp_1 = $d1;
						//ひとつ前にデータがある？
						$set_index = 0;
						if (isset($result_after[$d1['day']]) )
							$set_index = count($result_after[$d1['day']]);
						if($set_index>0){
							$result_after[$d1['day']][$set_index-1]['out_time'] =  $set_in_time;

						}
						else {
							$tmp_1['in_time'] = $tmp_1['day'].$branch_datas['open_time'];
							$tmp_1['out_time'] = $set_in_time;
							$result_after[$d1['day']][] = $tmp_1;
						}
					}
					//退勤時間が閉店時間より前ｰ>早退　退勤時間以降は休み
					if ($to <  $cl ) {
						$tmp_2 = $d1;
						$tmp_2['in_time'] = $set_out_time;
						$tmp_2['out_time'] = $set_day_close.$branch_datas['close_time'];
						$result_after[$d1['day']][] = $tmp_2;
					}

				}
			}
			else {
				if ( in_array(Salon_Working::USUALLY,$working_cds) ||
					in_array(Salon_Working::HOLIDAY_WORK,$working_cds)){
					//24時間対応
					if ($over24) {
						$op = substr($branch_datas['open_time'],-4);
						$fr = substr($d1['in_time'],-4);
// 						if ($fr < $op && Salon_Component::isMobile()) $d1['day']=$d1['before_day'];
						if ($fr < $op ) $d1['day']=$d1['before_day'];
					}
					$result_after[$d1['day']][] = $d1;
				}
			}
		}

		return $result_after;
	}
	public function editTableData ($branch_datas,$is_edit = false) {
		if  ($_POST['type'] == 'deleted' ) {
			$set_data['reservation_cd'] = intval($_POST['id']);
			$set_data['status'] = Salon_Reservation_Status::DELETED;
		}
		else {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$set_data['non_regist_name'] = stripslashes($_POST['name']);
			$set_data['non_regist_email'] = $_POST['mail'];
			$set_data['time_from'] = $_POST['start_date'];
			$set_data['status'] = $_POST['status'];
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['user_login'] = '';
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['non_regist_activate_key'] = substr(md5(uniqid(mt_rand(),1)),0,8);
			$set_data['item_cds'] = $_POST['item_cds'];
			$set_data['non_regist_tel'] = $_POST['tel'];
			if (($is_edit)&&(!empty($branch_datas['notes'])))
				$set_data['time_to'] = $_POST['end_date'];
			else
				$set_data['time_to'] = $this->datas->getMenuItemCalcEndTime($set_data['time_from'],$set_data['item_cds']);

			$set_data['coupon'] = "";
			if (isset($_POST['coupon']) && !empty($_POST['coupon'])) {
				$set_data['coupon'] = stripslashes($_POST['coupon']);
			}

			$user_login = $this->datas->getUserLogin();
			if (  empty($user_login) ) {
				if ($this->datas->getConfigData('SALON_CONFIG_CONFIRM_STYLE') == Salon_Config::CONFIRM_NO) {
					$set_data['status'] = Salon_Reservation_Status::COMPLETE;
				}
				else {
					$set_data['status'] = Salon_Reservation_Status::TEMPORARY;
					if ($this->datas->getConfigData('SALON_CONFIG_CONFIRM_STYLE') == Salon_Config::CONFIRM_BY_MAIL) {
						$this->is_need_sendmail = true;
					}
				}
			}
			else {
				$set_data['status'] = Salon_Reservation_Status::COMPLETE;
				$role = array();
				$isAdmin = $this->datas->isSalonAdmin($user_login,$role);
				if ($this->datas->isStaff()) {
					if ( $isAdmin || in_array('edit_booking',$role) ) {
						if (empty($_POST['user_login']) ) {
							if (empty($_POST['regist_customer'] ) ) $regist_customer = false;
							else $regist_customer = true;
							$set_data['user_login'] = $this->datas->registCustomer($set_data['branch_cd'],$set_data['non_regist_email'], $set_data['non_regist_tel'] ,$set_data['non_regist_name'],__('registerd by reservation process(booking)',SL_DOMAIN),'','','',$regist_customer,false);
						}
						else {
							$set_data['user_login'] = $_POST['user_login'];
						}
					}
				}
				else {
					$set_data['user_login'] = $user_login;
				}
			}

			if ($_POST['type'] == 'updated' ) {
				$set_data['reservation_cd'] = intval($_POST['id']);
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

	public function sendMailForConfirm($set_data) {
		if 	($this->is_need_sendmail ) {
			$branch_datas = $this->datas->getBranchData($set_data['branch_cd']);
			$to = $set_data['non_regist_email'];

			$subject = sprintf($this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_SUBJECT').'[%d]',$set_data['reservation_cd']);
			$message = $this->_create_body($set_data['reservation_cd'],$set_data['non_regist_name'],$set_data['non_regist_activate_key'],$branch_datas);

			$message = apply_filters('salon_replace_mail_body_confirm',$message);

			$header = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_FROM');
			if (!empty($header))	{
				$header = "from:".$header."\n";
			}

			add_action( 'phpmailer_init', array( &$this,'setReturnPath') );
			add_action('wp_mail_failed', array( &$this,'getMailErrorInformation'));

			if (wp_mail( $to,$subject, $message,$header ) === false ) {
				//phpmailerのsendで直接falseを返す場合あり？
				if ($this->mailErrorInformation == ":" || $this->mailErrorInformation == "") {
					global $phpmailer;
					$this->mailErrorInformation = "PHP ErrorInformation:".$phpmailer->ErrorInfo;
				}
				throw new Exception(Salon_Component::getMsg('E921',$this->mailErrorInformation),1);
			}

		}

	}


	public function getMailErrorInformation($wpError) {
		if (is_wp_error($wpError) ) {
			$this->mailErrorInformation  = $wpError->get_error_code();
			$this->mailErrorInformation  .= ":".$wpError->get_error_message();
		}
	}

	public function setReturnPath( $phpmailer ) {
		$path = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_RETURN_PATH');
		if (empty($path)) return;
		$phpmailer->Sender = $path;
	}

	private function _create_body($reservation_cd,$name ,$activate_key,$branch_datas) {
		$url = get_bloginfo( 'url' );
		$page = get_option('salon_confirm_page_id');
		$send_mail_text = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_TEXT');

//		$body = '<body>'.$send_mail_text.'</body>';
		$body = $send_mail_text;

//		$url = sprintf('<a href="%s/?page_id=%d&P1=%d&P2=%s" >'.__('to confirmed reservation form',SL_DOMAIN).'</a>',$url,intval($page),intval($reservation_cd),$activate_key);
		$url = sprintf('%s/?page_id=%d&P1=%d&P2=%s',$url,intval($page),intval($reservation_cd),$activate_key);


		$body = str_replace('{X-TO_NAME}',htmlspecialchars($name,ENT_QUOTES),$body);
		$body = str_replace('{X-URL}',$url,$body);


		$body = str_replace('{X-SHOP_NAME}',htmlspecialchars($branch_datas['name'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_ADDRESS}',htmlspecialchars($branch_datas['address'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_TEL}',htmlspecialchars($branch_datas['tel'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_MAIL}',htmlspecialchars($branch_datas['mail'],ENT_QUOTES),$body);

//		$body = Salon_Component::writeMailHeader().nl2br($body);
//		$body = nl2br($body);

		return $body;

	}


	public function serverCheck($set_data) {
		Salon_Component::serverReservationCheck($set_data,$this->datas);

	}


}
