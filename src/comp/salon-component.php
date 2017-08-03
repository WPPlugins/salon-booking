<?php
class Response_Type {
	const JASON = 1;
	const HTML =2;
	const XML = 3;
	const JASON_406_RETURN = 4;
}

class Salon_Status {
	const OPEN = 0;
	const CLOSE = 1;
}

class Salon_YesNo {
	const Yes = 1;
	const No = 2;
}

class Salon_Reservation_Full_Empty {
	const LOW = 1;
	const MIDDLE = 2;
	const HIGH =  3;
}


class Salon_Reservation_Status {
	const COMPLETE = 1;
	const TEMPORARY = 2;
	const DELETED =  3;
	const INIT =  0;
	const DUMMY_RESERVED = 4;	//実績登録の場合のみ
	const SALES_REGISTERD =  10;
	const BEFORE_DELETED =  5;  //現状未使用
}

class Salon_Edit {
	const OK = 1;
	const NG = 0;
}

class Salon_Regist_Customer {
	const OK = 1;
	const NG = 0;
}

class Salon_Table_id {
	const RECORD = 1;
	const RESERVATION = 2;
}

class Salon_Category {
	const RADIO = 1;
	const CHECK_BOX = 2;
	const TEXT = 3;
	const SELECT = 4;
}

class Salon_Photo {
	const WIDTH = 450;
	const HEIGHT = 450;
	const RATIO = "80%";
}

class Salon_Config {
	const ONLY_BRANCH = 1;
	const MULTI_BRANCH = 2;
	const USER_LOGIN_OK = 1;
	const USER_LOGIN_NG = 0;
	const SET_STAFF_NORMAL = 1;
	const SET_STAFF_REVERSE = 2;
	const NO_PREFERNCE_OK = 1;
	const NO_PREFERNCE_NG = 0;
	const DEFALUT_BEFORE_DAY = 3;
	const DEFALUT_AFTER_DAY = 30;
	const DEFALUT_TIMELINE_Y_CNT = 5;   //	timelineのY軸に何人入れるか
	const DETAIL_MSG_OK = 1;
	const DETAIL_MSG_NG = 2;
	const NAME_ORDER_JAPAN = 1;
	const NAME_ORDER_OTHER = 2;
	const LOG_NEED =1;
	const LOG_NO_NEED =2;
	const DELETE_RECORD_YES = 1;
	const DELETE_RECORD_NO = 2;
	const DELETE_RECORD_PERIOD = 6;
	const MAINTENANCE_INCLUDE_STAFF = 0;
	const MAINTENANCE_NOT_INCLUDE_STAFF = 1;
	//mobile
	const MOBILE_NO_PHOTO = 1;
	const TAP_INTERVAL = 500;
	const MOBILE_USE_YES = 1;
	const MOBILE_USE_NO = 2;
	const PC_MOBILE_USE = 1;
	const PC_ONLY_USE = 2;
	const MOBILE_ONLY_USE = 3;

	//mobile
	const ALL_ITEMS_YES = 1;
	const ALL_ITEMS_NO = 2;
	const ALL_ITEMS_CHANGE_YES = 1;
	const ALL_ITEMS_CHANGE_NO = 2;
	//load tab
	const LOAD_STAFF = 1;
	const LOAD_MONTH = 2;
	const LOAD_WEEK = 3;
	const LOAD_DAY = 4;
	//
	const DEFALUT_RESERVE_DEADLINE = 30;
	const DEFALUT_RESERVE_DEADLINE_UNIT_DAY = 1;
	const DEFALUT_RESERVE_DEADLINE_UNIT_HOUR = 2;
	const DEFALUT_RESERVE_DEADLINE_UNIT_MIN = 3;
	//
	const NO_REGISTED_CUSTOMER_CD = -1;
	//
	const USE_SESSION = 1;
	const USE_NO_SESSION = 2;

	const SHOW_TAB = 1;
	const SHOW_NO_TAB = 2;

	const SETTING_PATERN_TIME = 1;
	const SETTING_PATERN_ORIGINAL = 2;

	const CONFIRM_NO = 1;
	const CONFIRM_BY_ADMIN = 2;
	const CONFIRM_BY_MAIL = 3;

	const COMMA = 1;
	const TAB = 2;

	const USE_SUBMENU = 1;
	const USE_NO_SUBMENU = 2;
}

class Salon_CRank {
	const STANDARD = 1;
	const SILVER = 2;
	const GOLD = 3;
	const PLATINUM = 4;
	const DIAMOND = 5;
}

class Salon_Coupon {
	const UNLIMITED = 1;
	const TIMES = 2;
	const RANK = 3;
	const FIRST = 4;
}

class Salon_Discount {
	const PERCENTAGE = 1;
	const AMOUNT = 2;
}

class Salon_Working {
	const USUALLY = 1;
	const DAY_OFF = 2;
	const IN = 3;
	const OUT = 4;
	const LATE_IN = 5;
	const EARLY_OUT = 6;
	const HOLIDAY_WORK = 7;
	const ABSENCE = 8;


}

class Salon_Position {
	const MAINTENANCE = 7;
}

class Salon_Color {
	const HOLIDAY = "#FFCCFF";
	const USUALLY = "#6699FF";
	const PC_BACK = "#C2D5FC";
	const PC_BACK_PALLET1 = "#6633FF";
	const PC_BACK_PALLET2 = "#996600";
	const PC_BACK_PALLET3 = "#CCCC99";
	const PC_BACK_PALLET4 = "#F7F3F1";
	const PC_BACK_PALLET5 = "#FFF";
	const PC_EVENT_BORDER = "#8894A3";

	const PC_EVENT = "#6BF2E5";
	const PC_EVENT_LINE = "#8A8A8A";
	const PC_BACK_SELCTED = "#D4FAE8";
	const PC_BACK_UNSELCTED = "#DEDEDE";

//	const PC_HOLIDAY = "#F60151";
	const PC_HOLIDAY = "#FAC4BF";
	const PC_ONBUSINESS = "#696";

	const PC_FOCUS = "#D4FAE8";
}

class Salon_Default {
	const NO_PREFERENCE = -1;
	const BRANCH_CD = 1;
}

class Salon_Week {
	const SUNDAY = 0;
	const MONDAY = 1;

}

class Salon_Component {


	public function __construct() {

	}


	static function editSalesData($item_datas, $staff_datas, &$result,$promotion_datas = null ) {
		//アイテム名の設定　アイテムは正規化せず、コードで吸収する
		$item_table = array();
		//連想配列に書き直す
		foreach ($item_datas as $k1 => $d1) {
			$item_table[$d1['item_cd']]  = array('name'=> $d1['name'],'price'=>$d1['price']);
		}
		//スタッフ名の設定　joinすると遅くなりそうなので、コードで吸収する
		$staff_table = array();
		//連想配列に書き直す
		foreach ($staff_datas as $k1 => $d1) {
			$staff_table[$d1['staff_cd']]  = array('name'=> $d1['name']);
		}
		$staff_table[Salon_Default::NO_PREFERENCE] = array('name' => __('Anyone',SL_DOMAIN));

		$promotion_table = array();
		if ( !is_null($promotion_datas ) ) {
			foreach($promotion_datas as $k1 => $d1 ) {
				$promotion_table[$d1['set_code']] = $d1['description'];
			}
		}
		//個別データを編集する
		foreach ($result as $k1 => $d1 ) {
			$result[$k1]['staff_name_bef'] = @$staff_table[$result[$k1]['staff_cd_bef']]['name'];
	//		//予約実績としては、SALESに登録して完了とする。
	//		//で、実績として予約時の内容を設定しとく
			//[2014/08/31]
			$result[$k1]['coupon_name'] = "";
			if (!empty($result[$k1]['coupon']) && !is_null($promotion_datas ) ){
				$result[$k1]['coupon_name'] = $promotion_table[$result[$k1]['coupon']];
			}

			if ( empty($d1['time_from_aft']) ) {
				$result[$k1]['status'] = Salon_Reservation_Status::COMPLETE;
				$result[$k1]['status_name'] = __('result not registerd',SL_DOMAIN);

				$result[$k1]['time_from_aft'] = $result[$k1]['time_from_bef'];
				$result[$k1]['time_to_aft'] = $result[$k1]['time_to_bef'];
				$result[$k1]['staff_cd_aft'] = $result[$k1]['staff_cd_bef'];
				$result[$k1]['staff_name_aft'] = $result[$k1]['staff_name_bef'];
				$result[$k1]['item_cds_aft'] = $result[$k1]['item_cds_bef'];
				$result[$k1]['remark'] = $result[$k1]['remark_bef'];
				$result[$k1]['coupon_aft'] = $result[$k1]['coupon'];
			}
			else {
				$result[$k1]['staff_name_aft'] = "";
				if (!empty($result[$k1]['staff_cd_aft'])&&!empty($result[$k1]['staff_cd_aft']['name']))
					$result[$k1]['staff_name_aft'] = $staff_table[$result[$k1]['staff_cd_aft']]['name'];
				$result[$k1]['status'] = Salon_Reservation_Status::SALES_REGISTERD;
				$result[$k1]['status_name'] = __('result registerd',SL_DOMAIN);
			}
			//[20140518]
			if($result[$k1]['rstatus_cd'] == Salon_Reservation_Status::TEMPORARY) {
				$result[$k1]['rstatus'] = __('tentative',SL_DOMAIN);
			}
			else {
				if($result[$k1]['rstatus_cd'] == Salon_Reservation_Status::DELETED) {
					$result[$k1]['rstatus'] = __('canceled',SL_DOMAIN);
				}
				else {
					$result[$k1]['rstatus'] = __('completed',SL_DOMAIN);
				}
			}

			$items = explode( ',',$d1['item_cds_bef']);
			$res = array();
			$result[$k1]['item_cd_array_bef'] = array();
			foreach ($items as $k2 => $d2 ) {
				if (!empty($item_table[$d2])) {
					$res[] = $item_table[$d2]['name'];
					$result[$k1]['item_cd_array_bef'][$d2] = @$item_table[$d2]['name'];
				}
			}
			$result[$k1]['item_name_bef'] = implode(',',$res);
			$items = explode( ',',$result[$k1]['item_cds_aft']);
			$price = 0;
			foreach ($items as $k2 => $d2 ) {
				if (!empty($item_table[$d2])) {
					$result[$k1]['item_cd_array_aft'][$d2] = @$item_table[$d2]['name'];
					$price += @$item_table[$d2]['price'];
				}
			}
			$result[$k1]['price'] = $price;
		}
		return true;


	}

	static function serverReservationCheck($set_data ,&$datas,$isFullCheck = true) {

		global $wpdb;
		$reservation_data = '';
		if ( $_POST['type'] == 'inserted'    ) {
			if ( ! empty($set_data['reservation_cd']) )
				throw new Exception(self::getMsg('E901',basename(__FILE__).':'.__LINE__),1);
		}
		else {
			$reservation_data = $datas->getTargetSalesData($set_data['reservation_cd']);
			if ( count($reservation_data) == 0 ) {
				throw new Exception(self::getMsg('E912', basename(__FILE__).':'.__LINE__).':['.$set_data['reservation_cd'].']',2);
			}
			if ($_POST['p2'] != $reservation_data[0]['non_regist_activate_key'] ) {
				throw new Exception(self::getMsg('E909', basename(__FILE__).':'.__LINE__),1);
			}
			//顧客は自分のしか更新できない
			if (!$datas->isSalonAdmin("") ) {
				if ($_POST['user_login'] != $reservation_data[0]['user_login'] ) {
					throw new Exception(self::getMsg('E908', basename(__FILE__).':'.__LINE__),1);

				}
			}
		}

		$reservation_cd = '';
		if ( $_POST['type'] == 'updated'    ) $reservation_cd = $set_data['reservation_cd'];
		if ( ($_POST['type'] != 'deleted')&&($_POST['type'] != 'cancel')&&($_POST['type'] != 'confirm') ) {
			//[2014/07/23]同一時間帯に同じユーザはだめ。ログインしている場合のみのチェック
			//ログインしていない場合は電話・メール等のチェックも可能だが今後？
			if (!empty($set_data['user_login']) ) {
				//[2017/03/19 名前の必須入力をやめてダミーにしている場合
				//dummyって名前はあるかもしれないがよしとする
				if (strpos($set_data['user_login'],__("dummy",SL_DOMAIN)) === false ) {
					$sql =	' SELECT count(*) as cnt  '.
							' FROM '.$wpdb->prefix.'salon_reservation '.
							'   WHERE %s > time_from '.
							'      AND time_to > %s   '.
							'      AND user_login = %s '.
							'      AND delete_flg <> %d '.
							'      AND status <> %d ';
					$sql  = $wpdb->prepare($sql,$set_data['time_to'],$set_data['time_from'],$set_data['user_login'],Salon_Reservation_Status::DELETED,Salon_Reservation_Status::DELETED);
					if ( $_POST['type'] == 'updated'    ) $sql .= ' AND reservation_cd <> '. $set_data['reservation_cd'];
					if ($wpdb->query($sql) === false ) {
						$datas->_dbAccessAbnormalEnd();
					}
					else {
						$result = $wpdb->get_results($sql,ARRAY_A);
					}
					if ($result[0]['cnt'] > 0 ) {
						throw new Exception(self::getMsg('E212'),1);
					}
				}

			}
			$result_branch = $wpdb->get_results(
						$wpdb->prepare(
							' SELECT  '.
							' duplicate_cnt,closed,sp_dates,memo,open_time,close_time '.
							' FROM '.$wpdb->prefix.'salon_branch '.
							'   WHERE branch_cd = %d  ',
							$set_data['branch_cd']
						),ARRAY_A
					);
			if ($result_branch === false ) {
				$datas->_dbAccessAbnormalEnd();
			}
			//[2014/08/06]
			//[2017/05/05]チェック位置を店情報の後に変更する
			if (!$datas->isSalonAdmin("")){
				//fromは指定分以降より後
				$from = strtotime($set_data['time_from']);
				$limit_time = new DateTime(date_i18n('Y-m-d H:i'));
//				$limit_time->add(new DateInterval("PT".$datas->getConfigData('SALON_CONFIG_RESERVE_DEADLINE')."M"));
				$deadline = $datas->getConfigData('SALON_CONFIG_RESERVE_DEADLINE');
				if ( (60 * 24 ) <= $deadline ) {
					$setDates = round($deadline / (60 * 24));
					$limit_time->setTime(+substr($result_branch['open_time'],0,2)
									,+substr($result_branch['open_time'],2,2));
					$limit_time->modify("+".$setDates." day");
				}
				else {
					$limit_time->modify("+".$deadline." min");
				}

//				if ($limit_time->getTimestamp() > $from) {
				//Uはマイクロ秒
				if (+$limit_time->format('U') > $from) {
					throw new Exception(self::getMsg('E213'),1);
				}
			}
			//休業日のチェックと特別な営業日のチェック
			//
			$sp_dates = unserialize($result_branch[0]['sp_dates']);
			$year = substr($set_data['time_from'],0,4);
			//yyyy-mm-dd
			$ymd = str_replace('-','',substr($set_data['time_from'],0,10));


			$in_time = str_replace(':','',substr($set_data['time_from'],-5));

			//２４時超えで翌日だったら日付をひとつ前にする
			if (+substr($result_branch[0]['close_time'],0,2) > 23) {
				if ($in_time < $result_branch[0]['open_time'] ) {
					$ymd_24 = new DateTime(substr($set_data['time_from'],0,10));
					$ymd_24->modify('-1 day');
					$ymd = $ymd_24->format('Ymd');
				}
			}


			$in_time_sp = str_replace(':','',substr($set_data['time_from'],-5));
			$out_time_sp = str_replace(':','',substr($set_data['time_to'],-5));
			if (23 < +substr($result_branch[0]['close_time'],0,2)  ) {
				if ($in_time_sp < $result_branch[0]['open_time']) $in_time_sp = Salon_Component::editOver24Calc($in_time_sp);
				if ($out_time_sp < $result_branch[0]['open_time']) $out_time_sp = Salon_Component::editOver24Calc($out_time_sp);
			}

			if(isset($sp_dates[$year][$ymd]) && $sp_dates[$year][$ymd]['status'] == Salon_Status::OPEN ) {
				//未設定の場合＝データが古いままの場合は全日扱い
				//バグ以外ではないはず
				if (isset($sp_dates[$year][$ymd]['fromHHMM'])) {
					if (($sp_dates[$year][$ymd]['fromHHMM'] <= $in_time_sp )
					&& ($out_time_sp <= $sp_dates[$year][$ymd]['toHHMM']  )) {
					}
					else {
						throw new Exception(self::getMsg('E213'),__LINE__);
					}
				}
				else {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}

			}
			elseif(isset($sp_dates[$year][$ymd]) && $sp_dates[$year][$ymd]['status'] == Salon_Status::CLOSE ) {
				//未設定の場合＝データが古いままの場合は全日扱い
				//バグ以外ではないはず
				if (isset($sp_dates[$year][$ymd]['fromHHMM'])) {
					if (($out_time_sp <= $sp_dates[$year][$ymd]['fromHHMM'])
					|| ($sp_dates[$year][$ymd]['toHHMM'] <= $in_time_sp)) {
					}
					else {
						throw new Exception(self::getMsg('E213'),__LINE__);
					}
				}
				else {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}

			}
			else {
				$holidays = explode(',',$result_branch[0]['closed']);
				$holidays_detail = explode(';',$result_branch[0]['memo']);

				$set_holiday = salon_component::getDayOfWeek($set_data['time_from']);


				//２４時超えで翌日だったら曜日をひとつ前にする
				if (+substr($result_branch[0]['close_time'],0,2) > 23) {
					if ($in_time < $result_branch[0]['open_time'] ) {
						$set_holiday--;
						if ($set_holiday == -1 ) $set_holiday = 6;
					}
				}
				if (in_array($set_holiday,$holidays)  ) {
					$idx = array_search($set_holiday,$holidays);
					if ($idx === false) {
						throw new Exception(self::getMsg('E901',basename(__FILE__).':'.__LINE__));
					}
					$holiday_time = explode(",",$holidays_detail[$idx]);
					$holiday_in_time = str_replace(':','',$holiday_time[0]);
					$holiday_out_time = str_replace(':','',$holiday_time[1]);

					$out_time = str_replace(':','',substr($set_data['time_to'],-5));

					if (+substr($result_branch[0]['close_time'],0,2) > 23) {
						//24時超えの場合は24加算する
						if ($in_time < $result_branch[0]['open_time'] ) {
							$in_time = sprintf("%02d",(+substr($in_time,0,2)+24)).substr($in_time,2,2);
						}
						if ($out_time < $result_branch[0]['open_time'] ) {
							$out_time = sprintf("%02d",(+substr($out_time,0,2)+24)).substr($out_time,2,2);
						}
					}
					//休みの時間の中にはいっていてはいけない
					if ($out_time <= $holiday_in_time || $holiday_out_time <= $in_time ) {
					}
					else {
						throw new Exception(self::getMsg('E213'),__LINE__);
					}
				}
			}
			//予約が営業時間内に収まっているか？
			$in_time = str_replace(':','',substr($set_data['time_from'],-5));
			$out_time = str_replace(':','',substr($set_data['time_to'],-5));
			//24時間超え
			if (+substr($result_branch[0]['close_time'],0,2) > 23 ) {
				if ($in_time < $result_branch[0]['open_time']) $in_time = Salon_Component::editOver24Calc($in_time);
				if ($out_time < $result_branch[0]['open_time']) $out_time = Salon_Component::editOver24Calc($out_time);
				//この段階で営業時間内におさまっていない場合、postする値を変更した？
				if ( $result_branch[0]['open_time'] <= $in_time
					&& $in_time <= $result_branch[0]['close_time']) {
				}
				else {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}
				if ( $result_branch[0]['open_time'] <= $out_time
					&& $out_time <= $result_branch[0]['close_time']) {
				}
				else {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}
				if ($result_branch[0]['open_time'] > $in_time  ||  $out_time > $result_branch[0]['close_time'] ) {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}
			}
			else {
				if ($result_branch[0]['open_time'] > $in_time  ||  $out_time > $result_branch[0]['close_time'] ) {
					throw new Exception(self::getMsg('E213'),__LINE__);
				}
			}

			if  ($set_data['staff_cd'] !=  Salon_Default::NO_PREFERENCE ) {
				if ($datas->getConfigData('SALON_CONFIG_STAFF_HOLIDAY_SET') == Salon_Config::SET_STAFF_NORMAL ) {
					//スタッフの休みのチェック その日のスタッフの全データを取得
//					$sql =	' SELECT working_cds, '.
//							' DATE_FORMAT(in_time,"%%H%%i") as in_time,'.
//							' DATE_FORMAT(out_time,"%%H%%i") as out_time '.
//							' FROM '.$wpdb->prefix.'salon_working wk '.
//							'   WHERE %s <= in_time   AND  out_time <= %s '.
//							'     AND staff_cd = %d ';
					$sql =	' SELECT working_cds, '.
							'  in_time,'.
							'  out_time '.
							' FROM '.$wpdb->prefix.'salon_working wk '.
							'   WHERE %s <= in_time   AND  out_time <= %s '.
							'     AND staff_cd = %d ';
					//24時超え
					if (+substr($result_branch[0]['close_time'],0,2) > 23 ) {
						$in_time = str_replace(':','',substr($set_data['time_from'],-5));
						//２４時超えで翌日だったら曜日をひとつ前にする
						if ($in_time < $result_branch[0]['open_time'] ) {
							$lastday = self::computeDate(-1,substr($set_data['time_from'],0,4),substr($set_data['time_from'],5,2),substr($set_data['time_from'],8,2));
							$lastday = substr($lastday,0,10);
						}
						else {
							$lastday = substr($set_data['time_from'],0,10);
						}
						$lastday .= " ".substr($result_branch[0]['open_time'],0,2).":".substr($result_branch[0]['open_time'],2,2);

						$nextdaytmp = new DateTime(substr($lastday,0,10));
						$nextdaytmp->modify('+1 day');
						$nexthh =  sprintf("%02d",+substr($result_branch[0]['close_time'],0,2)-24 ).":".substr($result_branch[0]['close_time'],2,2);
						$nextday = $nextdaytmp->format('Y-m-d')." ".$nexthh;

						$sql  = $wpdb->prepare($sql,$lastday,$nextday,$set_data['staff_cd']);
					}
					else {
						$sql  = $wpdb->prepare($sql,substr($set_data['time_from'],0,10),substr($set_data['time_to'],0,10)." 24:00",$set_data['staff_cd']);
					}
					if ($wpdb->query($sql) === false ) {
						$datas->_dbAccessAbnormalEnd();
					}
					else {
						$result = $wpdb->get_results($sql,ARRAY_A);
					}
					if (count($result) > 0 ) {
						$in_time = strtotime($set_data['time_from']);
						$out_time = strtotime($set_data['time_to']);
						//複数回の出退勤を考慮
						$plu_check_ok_flg = true;
						foreach($result as $k1 => $d1 ) {
							$working_cds = explode( ',',$d1['working_cds']);
							//休みの場合は、指定時間が休みの時間に入っていてはいけない
							$chk_intime = strtotime($d1['in_time'] );
							$chk_outtime = strtotime($d1['out_time']);

							if (in_array(Salon_Working::DAY_OFF,$working_cds) ) {
								if ($out_time > $chk_intime && $chk_outtime >  $in_time  ){
									throw new Exception(__('this staff can not be reserved in this time range',SL_DOMAIN),__LINE__);
								}
							}
							//逆に勤務状態だったら時間帯に入っていること。早退・遅刻でも通常かHOLIDAYはworking登録時に設定
							elseif ( in_array(Salon_Working::USUALLY,$working_cds) ||
									in_array(Salon_Working::HOLIDAY_WORK,$working_cds) 	) {
								if ($chk_intime > $in_time || $out_time > $chk_outtime ) {
									$plu_check_ok_flg = false;
								}
								else {
									$plu_check_ok_flg = true;
									break;
								}
							}
						}
						if (!$plu_check_ok_flg) {
								throw new Exception(__('this staff can not be reserved in this time range',SL_DOMAIN),__LINE__);
						}
					}
				}
				else {
					//スタッフの休みのチェック 該当時間のスタッフの状態を取得
					$sql =	' SELECT working_cds, '.
							' DATE_FORMAT(in_time,"%%H%%i") as in_time,'.
							' DATE_FORMAT(out_time,"%%H%%i") as out_time '.
							' FROM '.$wpdb->prefix.'salon_working wk '.
							'   WHERE in_time <= %s AND %s <= out_time  '.
							'     AND staff_cd = %d ';
					$sql  = $wpdb->prepare($sql,$set_data['time_from'],$set_data['time_to'],$set_data['staff_cd']);
					if ($wpdb->query($sql) === false ) {
						$datas->_dbAccessAbnormalEnd();
					}
					else {
						$result = $wpdb->get_results($sql,ARRAY_A);
					}
					if (count($result) > 0 ) {
						$working_cds = explode( ',',$result[0]['working_cds']);
						//出勤時間ならＯＫ
						if ( ! in_array(Salon_Working::USUALLY,$working_cds) &&
							! in_array(Salon_Working::HOLIDAY_WORK,$working_cds)){
							throw new Exception(__('this staff can not be reserved in this time range',SL_DOMAIN),__LINE__);
						}
					}
					else {
						throw new Exception(__('this staff can not be reserved in this time range',SL_DOMAIN),__LINE__);
					}
				}

				$sql = 	$wpdb->prepare(
								' SELECT  '.
								' duplicate_cnt,in_items '.
								' FROM '.$wpdb->prefix.'salon_staff '.
								'   WHERE staff_cd = %d  ',
								$set_data['staff_cd']
							);

				if ($wpdb->query($sql) === false ) {
					$datas->_dbAccessAbnormalEnd();
				}
				else {
					$result = $wpdb->get_results($sql,ARRAY_A);
				}
				//スタッフの重複可能数のチェック
				$cnt = $datas->countReservationAndDuplicate($set_data['staff_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
				if ($cnt > $result[0]['duplicate_cnt'] ) {
					throw new Exception(self::getMsg('W002',array(__('staff',SL_DOMAIN), $result[0]['duplicate_cnt']+1)),1);
				}
//[2014/07/15]Ver 1.4.3　スタッフとメニューの相関チェック
				if ($isFullCheck) {
					$treated_item_array = explode(',',$result[0]['in_items']);
					$item_array = explode(',',$set_data['item_cds']);
					foreach($item_array as $k1 => $d1 ) {
						if(! in_array($d1,$treated_item_array) )
							throw new Exception(self::getMsg('E901',basename(__FILE__).':'.__LINE__));
					}
				}
//[2014/07/15]Ver 1.4.3


			}	//指名なし以外のスタッフの場合の終わり
//			$result_branch = $wpdb->get_results(
//						$wpdb->prepare(
//							' SELECT  '.
//							' duplicate_cnt '.
//							' FROM '.$wpdb->prefix.'salon_branch '.
//							'   WHERE branch_cd = %d  ',
//							$set_data['branch_cd']
//						),ARRAY_A
//					);
//			if ($result_branch === false ) {
//				$datas->_dbAccessAbnormalEnd();
//			}
			//支店単位の重複数
			$possible_cnt = $result_branch[0]['duplicate_cnt'];
			$edit_sql = $wpdb->prepare(
							' SELECT  '.
							' count(*) as staff_cnt, '.
							' sum(duplicate_cnt) as duplicate_cnt  '.
							' FROM '.$wpdb->prefix.'salon_staff '.
							'   WHERE branch_cd = %d  '.
							'   AND   delete_flg <> %d ',
							$set_data['branch_cd'],Salon_Reservation_Status::DELETED
						);
			if ($wpdb->query($edit_sql) === false ) {
				$datas->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($edit_sql,ARRAY_A);
			}
			//スタッフ単位の重複数＋スタッフ数を加算することにより
			//店全体の重複数
			$possible_cnt += $result[0]['staff_cnt'] + $result[0]['duplicate_cnt'];

			$cnt = $datas->countReservationAtBranch($set_data['branch_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
			if ($cnt > $possible_cnt ) {
				throw new Exception(self::getMsg('W002',array(__('branch',SL_DOMAIN), $possible_cnt)),1);
			}
			//[2017/06/14]指名なしの場合に選択されたメニューを開いているスタッフが扱えるか
			if  ($set_data['staff_cd'] ==  Salon_Default::NO_PREFERENCE ) {
				if ($datas->countReservationAnyone($set_data['branch_cd']
						,$set_data['time_from'],$set_data['time_to']
						,$reservation_cd,$set_data['item_cds']) === false ) {
							throw new Exception(self::getMsg('W005'),1);
						}
			}

			//[2014/08/25]Ver 1.4.8　クーポンのチェック
			if ($datas->isPromotion() ) {
				//クーポンを扱えるスタッフは期限切れ回数等は無視して何を設定してもよい。
			}
			else {
				if (isset($set_data['coupon']) && !empty($set_data['coupon']) ) {
					$result_promotion = $datas->getPromotionData($set_data['branch_cd'],null,$set_data['coupon']);

					if (count($result_promotion) == 0 ) {
						throw new Exception(self::getMsg('E301'),1);
					}
					$add_char = "";

					if (!$datas->checkCustomerPromotion($set_data,$result_promotion[0],$add_char,$reservation_cd  ) ) {
						throw new Exception(self::getMsg('E302',$add_char),1);
					}
				}
			}
			//[2014/08/25]Ver 1.4.8
		}


		return true;

	}


	//[2014/11/01]Ver1.5.1
	static function dummy () {
		$dummy = __('inserted',SL_DOMAIN);
		$dummy = __('updated',SL_DOMAIN);
		$dummy = __('deleted',SL_DOMAIN);
		$dummy = __('cancel',SL_DOMAIN);
		$dummy = __('exec',SL_DOMAIN);
		$dummy = __("Reservation on website",SL_DOMAIN);
		$dummy = __("Access",SL_DOMAIN);
		$dummy = __("Salon Booking enables the reservation to one-on-one business between a client and a staff member.",SL_DOMAIN);

	}

	static function writeMailHeader() {
		return "";
//		$charset = '';
//		if (function_exists( 'mb_internal_encoding' )) {
//			$charset = 'charset="'.mb_internal_encoding().'"';
//		}
//		return '<!DOCTYPE HTML PUBLIC
//			 "-//W3C//DTD HTML 4.01 Transitional//EN">
//			<html lang="ja">
//			<head>
//			  <meta http-equiv="Content-Language"
//				content="ja">
//			  <meta http-equiv="Content-Type"
//				content="text/html; '.$charset.'>
//			  <title></title>
//			  <meta http-equiv="Content-Style-Type"
//				content="text/css">
//			  <style type="text/css"><!--
//				body{margin:0;padding:0;}
//			  --></style>
//			</head>	';
	}

	static function getMsg($err_cd, $add_char = '') {
		$err_msg = '';
		switch ($err_cd) {
			case 'N001':
				$err_msg = sprintf(__("%s normal end",SL_DOMAIN),$add_char);
				break;
			case 'E001':
				$err_msg = sprintf(__("%s error !!",SL_DOMAIN),$add_char);
				break;
			case 'E002':
				$err_msg = sprintf(__("this user not registerd",SL_DOMAIN));
				break;
			case 'E003':
				$err_msg = sprintf(__("this staff not registerd[%s] ",SL_DOMAIN),$add_char);
				break;
			case 'E004':
				$err_msg = sprintf(__("sorry! under maintenance[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E005':
				$err_msg = sprintf(__("sorry! this page not displayed %s",SL_DOMAIN),$add_char);
				break;
			case 'E006':
				$err_msg = sprintf(__("user data is differented %s",SL_DOMAIN),$add_char);
				break;
			case 'E007':
				$err_msg = sprintf(__("an unexpected error has occurred %s",SL_DOMAIN),$add_char);
				break;
			case 'E008':
				$err_msg = sprintf(__("sorry! this page not displayed. checks cookies on ? %s ",SL_DOMAIN),$add_char);
				break;
			case 'E009':
				$err_msg = sprintf(__("this branch_cd[%d] can't find.Please check set short code format. [salon-booking] or if multi shop [salon-booking branch_cd=XX]. ",SL_DOMAIN),$add_char);
				break;
			case 'E010':
				$err_msg = sprintf(__("this branch has no staff ",SL_DOMAIN),$add_char);
				break;
			case 'E011':
				$err_msg = sprintf(__("This reservation has expired. [%s]",SL_DOMAIN),$add_char);
				break;
			case 'E012':
				$err_msg = sprintf(__("This reservation updated. [%s]",SL_DOMAIN),$add_char);
				break;
			case 'E013':
				$err_msg = sprintf(__("This request is invalid nonce. [%s]",SL_DOMAIN),$add_char);
				break;
			case 'E201':
				$err_msg = sprintf(__("required[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E202':
				$err_msg = sprintf(__("this is not time data[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E203':
				$err_msg = sprintf(__("numeric input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E205':
				$err_msg = sprintf(__("zip code XXXXX-XXXX input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E206':
				$err_msg = sprintf(__("Telephone XXXX-XXX-XXXX input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E207':
				$err_msg = sprintf(__("XXX@XXX.XXX input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E208':
				$err_msg = sprintf(__("MM/DD/YYYY or MMDDYYYY input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E209':
				$err_msg = sprintf(__("this day not exist?[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E210':
				$err_msg = sprintf(__("space input between first-name and last-name[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E211':
				$err_msg = sprintf(__("within %d characters[%s]",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;
			case 'E212':
				$err_msg = __("This reservation is duplicated",SL_DOMAIN);
				break;
			case 'E213':
				$err_msg = __("This time zones can not be reserved",SL_DOMAIN);
				break;
			case "E214":
				$err_msg = __("This menu checked as the following staff member.\nplease update the menu of the following staff member.\n",SL_DOMAIN).$add_char;
				break;
			case "E215":
				$err_msg = __("At least one data is needed",SL_DOMAIN);
				break;
			case 'E216':
				$err_msg = sprintf(__("Name <XXX@XXX.XXX> input please[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E301':
				$err_msg = __("This coupon is invalid now.",SL_DOMAIN);
				break;
			case 'E302':
				$err_msg = sprintf(__("This coupon can not be used[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E303':
				$err_msg = sprintf(__("This coupon is used now[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E304':
				$err_msg = sprintf(__("This Code is aleready used[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E305':
				$err_msg = __("\"Valid to\" need after today",SL_DOMAIN);
				break;
			case 'E401':
				$err_msg = __('an unexpected error has occurred',SL_DOMAIN);
				break;
			case 'E901':
				$err_msg = sprintf(__("this data is unacceptble.Bug?[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E902':
				$err_msg = sprintf(__("database error [%s][%s]",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;
			case 'E903':
				$err_msg = sprintf(__("create userid error [%s]",SL_DOMAIN),$add_char);
				break;
			case 'E904':
				$err_msg = sprintf(__("file open error[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E905':
				$err_msg = sprintf(__("file write error",SL_DOMAIN));
				break;
			case 'E906':
				$err_msg = sprintf(__("target data not found",SL_DOMAIN));
				break;
			case 'E907':
				$err_msg = sprintf(__("e-mail could not be sent %s",SL_DOMAIN),$add_char);
				break;
			case 'E908':
				//ここは英字のみ→やめ
				$err_msg = sprintf(__("This access is out of the authority[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E909':
				$err_msg = sprintf(__("This reservation already updated.",SL_DOMAIN),$add_char);
				break;
			case 'E910':
				$err_msg = sprintf(__("This Data is in use.[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E911':
				$err_msg = sprintf(__("file error[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E912':
				$err_msg = sprintf(__("This reservation does not exist.[%s]",SL_DOMAIN),$add_char);
				break;
			case 'E921':
				$err_msg = sprintf(__("e-mail could not be sent.But this reservation is comfirmed. Please update this screen. %s",SL_DOMAIN),$add_char);
				break;
			case 'W001':
				$err_msg = sprintf(__("already reservation existed, so you can't day off",SL_DOMAIN),$add_char);
				break;
			case 'W002':
				$err_msg = sprintf(__("already reservation existed .this %s can reserve %s reservations at same time range,please update datas ",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;
			case 'W003':
				$err_msg = sprintf(__("already [User Login] existed.",SL_DOMAIN),$add_char);
				break;
			case 'W004':
				$err_msg = sprintf(__("already user data existed.Please change the value of [mail] or [tel] or [mobile] ",SL_DOMAIN),$add_char);
				break;
			case 'W005':
				$err_msg = sprintf(__("Anyone staff can not treat these menus. ",SL_DOMAIN),$add_char);
				break;
			case 'I001':
				$err_msg = sprintf(__("when demo site ,can't insert,update and delete.",SL_DOMAIN),$add_char);
				break;
			case 'I002':
				$err_msg = sprintf(__("Customer is registerd.\nUser Login : %s\nPassword : %s",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;
			case 'I003':
				$err_msg = sprintf(__("when demo site ,can't delete.",SL_DOMAIN),$add_char);
				break;
			default:
				$err_msg = __("message not found",SL_DOMAIN).$add_char;

		}
		return $err_cd." ".$err_msg;
	}

	static function computeDate($addDays = 1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$baseSec = mktime(0, 0, 0, $month, $day, $year);
		$addSec = $addDays * 86400;
		$targetSec = $baseSec + $addSec;
		return date("Y-m-d H:i:s", $targetSec);
// 		$base = new Datetime("$year-$month-$day 00:00:00");
// 		if (0 < +$addDays ) {
// 			$base->add(new DateInterval("P".$addDays."D"));
// 		}
// 		else {
// 			$base->sub(new DateInterval("P".-1*$addDays."D"));
// 		}
// 		return $base->format("Y-m-d H:i:s");
	}

	static function getMonthEndDay($year, $month) {
		$dt = mktime(0, 0, 0, $month + 1, 0, $year);
		return date("d", $dt);
	}

	static function computeMonth($addMonths=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$month += $addMonths;
		$endDay = self::getMonthEndDay($year, $month);
		if($day > $endDay) $day = $endDay;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}

	static function computeYear($addYears=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$year += $addYears;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}

	static function zenSp2han($in) {
		if (function_exists( 'mb_convert_kana' )) {
			return  mb_convert_kana($in,"s");
		}
		else {
			return $in;
		}
	}

	static function formatTime($time_data) {
		return sprintf("%02s:%02s",+substr($time_data,0,2),substr($time_data,2,2));
	}

	static function replaceTimeToDb($time_data) {
		if (preg_match('/(?<hour>\d+):(?<minute>\d+)/', $time_data, $matches) == 0 ) {
			$matches['hour'] = substr($time_data,0,2);
			$matches['minute'] = substr($time_data,2,2);
		}
		return sprintf("%02d%02d",+$matches['hour'],+$matches['minute']);
	}


	static function editRequestYmdForDb($in) {
		if (empty($in) ) return;
		if (preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',SL_DOMAIN).'$/',$in,$matches) == 0 )
		   preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',SL_DOMAIN).'$/',$in,$matches);
		return sprintf("%4d-%02d-%02d",+$matches['year'],+$matches['month'],+$matches['day']);
	}

	static function getDayOfWeek($in) {
		return date("w", strtotime($in));
	}

	static function isMobile($checkRequest = true){
		$isMobile = false;
		$result =  unserialize(get_option( 'SALON_CONFIG'));
//$result['SALON_CONFIG_USE_PC_MOBILE'] = Salon_Config::MOBILE_ONLY_USE;
		if (!empty($result['SALON_CONFIG_USE_PC_MOBILE'] )) {
			if ($result['SALON_CONFIG_USE_PC_MOBILE'] == salon_config::MOBILE_ONLY_USE ){
				return true;
			}
		}
		if (!empty($result['SALON_CONFIG_MOBILE_USE']) && ($result['SALON_CONFIG_MOBILE_USE'] == Salon_Config::MOBILE_USE_NO )) {
			return false;
		}
		if ( $checkRequest && isset($_REQUEST['sl_desktop']) && $_REQUEST['sl_desktop'] == 'true'  ) {
			return false;
		}

		$useragents = array(
			'iPhone', // iPhone
			'iPod', // iPod touch
			'Android.*Mobile', // 1.5+ Android *** Only mobile
			'Windows.*Phone', // *** Windows Phone
			'dream', // Pre 1.5 Android
			'CUPCAKE', // 1.5+ Android
			'blackberry9500', // Storm
			'blackberry9530', // Storm
			'blackberry9520', // Storm v2
			'blackberry9550', // Storm v2
			'blackberry9800', // Torch
			'webOS', // Palm Pre Experimental
			'incognito', // Other iPhone browser
			'webmate' // Other iPhone browser
		);
		$pattern = '/'.implode('|', $useragents).'/i';
		if (preg_match($pattern, $_SERVER['HTTP_USER_AGENT']) == 1) {
			$isMobile = true;
		}
		$isMobile = apply_filters('salon_booking_set_isMobile',$isMobile,$useragents);

		return $isMobile;
	}

	static function calcMinute($from,$to) {
		//$from toはHHMM
		if (strlen($from) == 3 ) $from = '0'.$from;
		if (strlen($to) == 3 ) $to = '0'.$to;
		$fromhh = +substr($from,0,2);
		$frommm = +substr($from,2,2);
		$tohh = +substr($to,0,2);
		$tomm = +substr($to,2,2);
		//fromが２４を超えているならｔｏも超えている
		if ($fromhh > 23 ) {
			$fromhh = $fromhh - 24;
			$tohh = $tohh - 24;
		}
		else {
			if ($tohh > 23 ) {
				$tohh = $tohh - 24;
			}
		}
		//24時超え
		$yyyymmdd = '2000/01/01 ';

		if ( ($fromhh*100+$frommm) > ($tohh*100+$tomm) ) {
			$yyyymmdd = '2000/01/02 ';
		}
		$pasttime=strtotime('2000/01/01 '.sprintf("%s:%s:00",$fromhh,$frommm));
		$thistime=strtotime($yyyymmdd.sprintf("%s:%s:00",$tohh,$tomm));
		$diff=$thistime-$pasttime;
		return floor($diff/60);
	}

	static function editOver24Calc($hhmm) {
		//hhとmmを分けて計算する。そうしないと0:30が30:00の扱いになる

		$aft_hhmm = str_replace(':','',$hhmm);
		$edit = sprintf("%02d%02d",+substr($aft_hhmm,0,2)+24,+substr($aft_hhmm,2,2));
		return  $edit;
	}

	static function checkRole($class_name) {
		$class_name_array = explode('_',$class_name);
		if (empty($class_name_array[0]) ) {
				throw new Exception(self::getMsg('E908',basename(__FILE__).':'.__LINE__),1);
		}
		$target_name = strtolower ($class_name_array[0]);
		if ( $target_name == 'booking'
			|| $target_name == 'confirm'
			|| $target_name == 'menulist'
			|| $target_name == 'stafflist' ) return;
		//マルチサイトでネットワークユーザならOK
		if (is_multisite() && is_super_admin() ) return;
		//global $current_user;
		//get_currentuserinfo();
		$current_user = wp_get_current_user();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		//このプラグインでは寄稿者は管理させない
		if (empty($user_role) || $user_role == 'subscriber' ) {
				throw new Exception(self::getMsg('E908',basename(__FILE__).':'.__LINE__),2);
		}
		global $wpdb;
		$sql =  $wpdb->prepare('SELECT role FROM '.$wpdb->prefix.'salon_position po ,'.
								$wpdb->prefix.'salon_staff st '.
						' WHERE st.user_login = %s '.
						'   AND st.position_cd = po.position_cd '.
						'   AND st.delete_flg <> '.Salon_Reservation_Status::DELETED,$current_user->user_login);

		if ($wpdb->query($sql) === false ) {
				throw new Exception(self::getMsg('E908',basename(__FILE__).':'.__LINE__),3);
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result) == 0 ) {
				throw new Exception(self::getMsg('E908',basename(__FILE__).':'.__LINE__),4);
		}
		$show_menu =  explode(",",$result[0]['role']);
		if ($target_name == 'basic') $target_name = 'base';
		if ($target_name == 'search') $target_name = 'booking';
		if ($target_name == 'photo') $target_name = 'staff';
		if ($target_name == 'mail') $target_name = 'config';
		if ($target_name == 'configbooking') $target_name = 'config';
		if ($target_name == 'checkconfig') $target_name = 'config';
		if ($target_name == 'download') {
			if (!in_array('edit_resevation',$show_menu) && !in_array('edit_sales',$show_menu) ) {
					throw new Exception(self::getMsg('E908',$class_name),5);
			}
		}
		else {
			if (!in_array('edit_'.$target_name,$show_menu) ) {
					throw new Exception(self::getMsg('E908',$class_name),6);
			}
		};
	}

}