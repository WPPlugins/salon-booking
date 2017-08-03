<?php

class Basic_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editTableData () {

		if ( $_POST['type'] == 'updated' ) {
			$set_data['open_time'] = Salon_Component::replaceTimeToDb($_POST['open_time']);
			$set_data['close_time'] = Salon_Component::replaceTimeToDb($_POST['close_time']);
			$set_data['time_step'] = intval($_POST['time_step']);
			$set_data['closed'] = $_POST['salon_closed'];
			$set_data['branch_cd'] = intval($_POST['target_branch_cd']);
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
			//[2014/10/01]半休対応
			$set_data['memo'] = "";
			if (isset($_POST['memo'])&& !empty($_POST['memo']) ) {
				$tmp_array = explode(";",$_POST['memo']);
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
		else {
			$set_data['branch_cd'] = intval($_POST['target_branch_cd']);
			$target_date = str_replace('/','',$_POST['target_date']);
			$datas = $this->datas->getBranchData($set_data['branch_cd'],'sp_dates');
			$sp_dates = unserialize($datas['sp_dates']);
			if ($_POST['type']	== 'inserted' ) {
				$sp_dates[substr($target_date,0,4)][$target_date]['status'] = intval($_POST['status']);
				$sp_dates[substr($target_date,0,4)][$target_date]['fromHHMM'] = Salon_Component::replaceTimeToDb($_POST['fromHHMM']);
				$sp_dates[substr($target_date,0,4)][$target_date]['toHHMM'] = Salon_Component::replaceTimeToDb($_POST['toHHMM']);
			}
			elseif ($_POST['type']	== 'deleted' ) {
				unset($sp_dates[substr($target_date,0,4)][$target_date]);
			}
			$set_data['sp_dates'] = $sp_dates;
		}
		return $set_data;

	}



}