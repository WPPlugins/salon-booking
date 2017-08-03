<?php

class Customer_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editInitData($result,$branch_cd) {

		$edit = array();
		$index = 0;
		$save_key = $result[0]['ID'];
		//ログインID単位の配列に変換する。
		for ($i =0; $i < count($result) ; $i++ ) {
			if ($save_key <> $result[$i]['ID'] ) {
				$index++;
			}
			$edit[$index][$result[$i]['meta_key']] = $result[$i]['meta_value'];
			//[TODO]ちと冗長？
			$edit[$index]['ID'] = $result[$i]['ID'];
			$edit[$index]['customer_cd'] = $result[$i]['customer_cd'];
			$edit[$index]['user_login'] = $result[$i]['user_login'];
			$edit[$index]['mail'] = $result[$i]['user_email'];
			$edit[$index]['branch_cd'] = $result[$i]['branch_cd'];
			$edit[$index]['remark'] = $result[$i]['remark'];
			$edit[$index]['memo'] = $result[$i]['memo'];
			$edit[$index]['notes'] = $result[$i]['notes'];
			$edit[$index]['rank_patern_cd'] = $result[$i]['rank_patern_cd'];

			$save_key = $result[$i]['ID'];
		}
		//不要な項目が多いので編集する
		$result_after = array();
		$index = 0;
		global $wpdb;

		foreach ( $edit as $k1 => $d1 ) {
			if (is_multisite() ) {
				if (!isset($d1[$wpdb->prefix.'capabilities']) ) {
					continue;
				}
			}
			$role = unserialize($d1[$wpdb->prefix.'capabilities']) ;
			//顧客は「購読者」のみ
			if ( array_key_exists('subscriber',$role) ){
				//管理者以外は他店舗をみられない
				if ($this->datas->isSalonAdmin()
				||	$branch_cd == $d1['branch_cd']) {
					$result_after[$index]['ID'] = $d1['ID'];
					$result_after[$index]['customer_cd'] = $d1['customer_cd'];
					$result_after[$index]['user_login'] = $d1['user_login'];
					$result_after[$index]['mail'] = $d1['mail'];
					$result_after[$index]['branch_cd'] = $d1['branch_cd'];
					$result_after[$index]['remark'] = $d1['remark'];
					$result_after[$index]['memo'] = $d1['memo'];
					$result_after[$index]['notes'] = $d1['notes'];
					$result_after[$index]['rank_patern_cd'] = $d1['rank_patern_cd'];
					//		$result_after[$index]['name'] = trim($d1['first_name'].' '.$d1['last_name']);
					$result_after[$index]['first_name'] = $d1['first_name'];
					$result_after[$index]['last_name'] = $d1['last_name'];
					$result_after[$index]['zip'] = @$d1['zip'];
					$result_after[$index]['address'] = @$d1['address'];
					$result_after[$index]['tel'] = @$d1['tel'];
					$result_after[$index]['mobile'] = @$d1['mobile'];
					$index++;
				}
			}
		}
		//氏名を編集したり、もろもろ
		foreach ( $result_after as $k1 => $d1) {
			if (empty($d1['first_name'] ) ) $result_after[$k1]['first_name'] = __('last name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['last_name'] ) ) $result_after[$k1]['last_name'] = __('first name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['branch_cd'] ) ) {
				$result_after[$k1]['branch_name'] = __('registerd salon ',SL_DOMAIN).__('not registered',SL_DOMAIN);
			}
			else {
				$datas = $this->datas->getBranchData($d1['branch_cd']);
				$result_after[$k1]['branch_name'] = $datas['name'];
			}
		}
		return $result_after;

	}


	public function editUserData() {
		$set_data['ID'] = $_POST['ID'];
		$set_data['user_login'] = $_POST['user_login'];
		$set_data['mail'] = $_POST['mail'];
		$set_data['zip'] = $_POST['zip'];
		$set_data['address'] = stripslashes($_POST['address']);
		$set_data['tel'] = $_POST['tel'];
		$set_data['mobile'] = $_POST['mobile'];
		$set_data['first_name'] = stripslashes($_POST['first_name']);
		$set_data['last_name'] = stripslashes($_POST['last_name']);
		$set_data['position_cd'] = '';
		return $set_data;
	}

	public function editTableData () {

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['customer_cd'] = intval($_POST['customer_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['customer_cd'] = intval($_POST['customer_cd']);

			$set_data['ID'] = intval($_POST['ID']);
			$set_data['user_login'] =  stripslashes($_POST['user_login']);
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['rank_patern_cd'] = intval($_POST['rank_patern_cd']);
			$set_data['remark'] =  stripslashes($_POST['remark']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['photo'] = '';

		}
		return $set_data;

	}

	public function editColumnDataForWpUser() {
		switch (intval($_POST['column'])) {
			case 2:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'last_name';
				else $meta = 'first_name';
				break;
			case 3:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'first_name';
				else $meta = 'last_name';
				break;
		}

		return array('ID'=>intval($_POST['ID']),'meta'=>$meta,'value'=>stripslashes($_POST['value']));

	}

	public function editColumnData() {
		$column = array();
		$column[4]="branch_cd = %d ";
		$column[5]="remark = %s ";


		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] =  stripslashes($_POST['value']);
		$set_data['customer_cd'] = intval($_POST['customer_cd']);
		$set_data['ID'] = intval($_POST['ID']);
		return $set_data;
	}


}