<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Working_Get_Data extends Salon_Page {
	
	private $target_day = '';
	private $working_datas = null;
	private $branch_cd = '';
	private $staff_cd = '';
	
	private $user_login = '';
	
	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->target_day = Salon_Component::computeDate(-3650);	//[debug]
		$this->branch_cd = $_GET['branch_cd'];
		$this->staff_cd =  $_GET['staff_cd'];
	}
	
	public function get_target_day() {
		return $this->target_day;
	}
	
	public function get_branch_cd() {
		return $this->branch_cd;
	}
	
	public function get_staff_cd() {
		return $this->staff_cd;
	}
	
	public function set_working_datas($working_datas) {
		$this->working_datas = $working_datas;
	}

	
	public function set_user_login($user_login) {
		$this->user_login = $user_login;
	}


	public function show_page() {
		$edit_flg = Salon_Edit::OK;
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo '<data>';
		foreach ($this->working_datas as $k1 => $d1 ) {
			$status = '';
			$color = '';
			$tmp = explode(',',$d1['working_cds']);
			if ($tmp ) {
				foreach ($tmp as $k2 => $d2 ) {
					if ($d2 == Salon_Working::DAY_OFF ) {
							$status =  __('DAY_OFF',SL_DOMAIN);
							$color = ' color = "'.Salon_Color::HOLIDAY.'" ';
					}
				}
			}
			$edit_remark = htmlspecialchars($d1['remark'],ENT_QUOTES);
			echo <<<EOT
				<event staff_cd =  "{$d1['staff_cd']}"
				 start_date= "{$d1['in_time']}"
				 end_date  = "{$d1['out_time']}"
				 text = "{$status}"
				 remark = "{$edit_remark}"
				 working_cds = "{$d1['working_cds']}"
				 edit_flg="{$edit_flg}" 
				 {$color}
				 />
EOT;
		}
		echo '<userdata name="status">Ok</userdata><userdata name="message">'.Salon_Component::getMsg('N001').'</userdata>';
		echo '</data>';
	}
}