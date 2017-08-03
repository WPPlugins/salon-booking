<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Basic_Data extends Salon_Data {

	const TABLE_NAME = 'salon_branch';

	function __construct() {
		parent::__construct();
	}

	public function getAllSpDateData($target_year = null,$target_branch_cd = null){
		$now = new DateTime(date_i18n('Y-m-d'));
		$min = clone $now;
		$max = clone $now;
//		$min->sub(new DateInterval("P".$this->getConfigData('SALON_CONFIG_BEFORE_DAY')."D"));
//		$max->add(new DateInterval("P".$this->getConfigData('SALON_CONFIG_AFTER_DAY')."D"));
		$min->modify("-".$this->getConfigData('SALON_CONFIG_BEFORE_DAY')." day");
		$max->modify("+".$this->getConfigData('SALON_CONFIG_AFTER_DAY')." day");
		$minYmd = $min->format("Ymd");
		$maxYmd = $max->format("Ymd");


		$datas = $this->getBranchData($target_branch_cd,'sp_dates,open_time,close_time');
		$result = array();
		if ($datas) {
			$sp_dates = unserialize($datas['sp_dates']);
			if ($sp_dates && !empty($sp_dates[$target_year])) {
				foreach ($sp_dates[$target_year] as $k1 => $d1) {
					if ($minYmd <= $k1 && $k1 <= $maxYmd) {
						$title = __('special holiday',SL_DOMAIN);
						if ($d1['status'] == Salon_Status::OPEN) $title = __('on business',SL_DOMAIN);
						$target_date = __('%%m/%%d/%%Y',SL_DOMAIN);
						$target_date = str_replace('%%Y',substr($k1,0,4),$target_date);
						$target_date = str_replace('%%m',substr($k1,4,2),$target_date);
						$target_date = str_replace('%%d',substr($k1,6,2),$target_date);
						$show_date = $target_date . " "
								. substr($d1['fromHHMM'],0,2) . ":" . substr($d1['fromHHMM'],2,2)
							." - " . substr($d1['toHHMM'],0,2) . ":" . substr($d1['toHHMM'],2,2);
						$result[$target_date] = array("show_date"=>$show_date,"target_date"=>$target_date,"status_title"=>$title,"status"=>$d1['status'],"fromHHMM"=>$d1['fromHHMM'],"toHHMM"=>$d1['toHHMM']);
					}
				}
			}
		}

		$edit_result = array();
		sort($result);
		foreach ($result as $k1 => $d1) {
			$edit_result[] = $d1;
		}
		return ($edit_result);
	}


	public function updateTable ($table_data){
		$set_string = 	' open_time = %s , '.
						' close_time = %s , '.
						' time_step = %d , '.
						' closed = %s , '.
						' duplicate_cnt = %d , '.
						' memo = %s , '.
						' update_time = %s ';

		$set_data_temp = array(
						$table_data['open_time'],
						$table_data['close_time'],
						$table_data['time_step'],
						$table_data['closed'],
						$table_data['duplicate_cnt'],
						$table_data['memo'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function updateSpDate ($table_data){
		$set_string = 	' sp_dates = %s , '.
						' update_time = %s ';

		$set_data_temp = array(
						serialize($table_data['sp_dates']),
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}






}