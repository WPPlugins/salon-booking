<?php

class Branch_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function serverCheck($set_data) {
		global $wpdb;
		//連続して削除されると、どっかのスタッフが使用しているメニューでも削除できるのでここでチェックする。
		if ( $_POST['type'] == 'deleted' )  {

			$sql =	$wpdb->prepare(
						' SELECT  count(*) as cnt '.
						' FROM '.$wpdb->prefix.'salon_branch '.
						'   WHERE delete_flg <> %d ',
						Salon_Reservation_Status::DELETED);

			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($sql,ARRAY_A);
			}

			if ($result[0]['cnt'] == 1 ) {
				throw new Exception(Salon_Component::getMsg('E215'),1);
			}
			//スタッフの情報で使用されていないこと
			$sql =	$wpdb->prepare(
					' SELECT  count(*) as cnt '.
					' FROM '.$wpdb->prefix.'salon_staff '.
					'   WHERE delete_flg <> %d '.
					'     AND branch_cd = %d ',
					Salon_Reservation_Status::DELETED
					,$set_data['branch_cd']);

			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($sql,ARRAY_A);
			}

			if ( 0 < $result[0]['cnt'] ) {
				throw new Exception(Salon_Component::getMsg('E910',__('STAFF INFORMATION',SL_DOMAIN)),1);
			}

			//メニューの情報で使用されていないこと
			$sql =	$wpdb->prepare(
					' SELECT  count(*) as cnt '.
					' FROM '.$wpdb->prefix.'salon_item '.
					'   WHERE delete_flg <> %d '.
					'     AND branch_cd = %d ',
					Salon_Reservation_Status::DELETED
					,$set_data['branch_cd']);

			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($sql,ARRAY_A);
			}

			if ( 0 < $result[0]['cnt'] ) {
				throw new Exception(Salon_Component::getMsg('E910',__('MENU INFORMATION',SL_DOMAIN)),1);
			}


		}
	}

	public function editTableData () {

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['branch_cd'] = intval($_POST['branch_cd']);

			$set_data['name'] = stripslashes($_POST['name']);
//			$set_data['zip'] = str_replace('-','',$_POST['zip']);
//			$set_data['zip'] = substr($set_data['zip'],0,3).'-'.substr($set_data['zip'],3);
			$set_data['zip'] = $_POST['zip'];
			$set_data['address'] = stripslashes($_POST['address']);
			$set_data['tel'] = $_POST['tel'];
			$set_data['mail'] = $_POST['mail'];
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
			$set_data['notes'] = stripslashes($_POST['notes']);
			$set_data['open_time'] = Salon_Component::replaceTimeToDb($_POST['open_time']);
			$set_data['close_time'] = Salon_Component::replaceTimeToDb($_POST['close_time']);
			$set_data['time_step'] = $_POST['time_step'];
			$set_data['closed'] = $_POST['closed'];
			//[2014/10/01]半休対応
			$set_data['memo'] = "";
			if (isset($_POST['memo'])&& !empty($_POST['memo']) ) {
				$tmp_array = explode(";",$_POST['memo']);
				if (count($tmp_array) > 0 ) {
					$tmp_result = array();
					foreach ($tmp_array as  $d1 ) {
						$frto = explode(",",$d1);
						$tmp_frto = array();
						$from = Salon_Component::replaceTimeToDb($frto[0]);
						if (+$from <  +$set_data['open_time']) $from = $set_data['open_time'];
						$tmp_frto[] = $from;
						$to = Salon_Component::replaceTimeToDb($frto[1]);
						if (+$to >  +$set_data['close_time']) $to = $set_data['close_time'];
						$tmp_frto[] = $to;
						$tmp_result[] = implode(",",$tmp_frto);
					}
					$set_data['memo'] = implode(";",$tmp_result);
				}
			}
		}
		return $set_data;

	}


	public function editColumnData() {
		$column = array();
		$column[2]="name = %s ";
		$column[3]="remark = %s ";


		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['branch_cd'] = intval($_POST['branch_cd']);
		return $set_data;
	}

	public function editInitDatas() {
		$result = $this->datas->getInitdatas();
		foreach ($result as $k1 => $d1 ) {
			$result[$k1]['shortcode'] = '[salon-booking branch_cd='.$d1['branch_cd'].']';
			$result[$k1]['open_time'] = Salon_Component::formatTime($d1['open_time']);
			$result[$k1]['close_time'] = Salon_Component::formatTime($d1['close_time']);
		}
		return $result;
	}


}