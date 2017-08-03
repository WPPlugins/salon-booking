<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Confirm_Edit extends Salon_Page {
	
	private $table_data = null;
	
	private $reservation_cd = '';
	private $activation_key = '';

	private $datas = null;
	
	private $error_msg = '';
	



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->reservation_cd = intval($_POST['target']);
		$this->activation_key = $_POST['P2'];
	}

	
	public function get_reservation_cd () {
		return $this->reservation_cd;
	}
	public function set_reservation_datas ( $datas ) {
		$this->datas = $datas;
	}



	
	public function check_request() {
		$nonce = SL_PLUGIN_DIR;
		if ($this->config_datas['SALON_CONFIG_USE_SESSION_ID'] == Salon_Config::USE_SESSION) $nonce = session_id();
		if (wp_verify_nonce($_REQUEST['nonce'],$nonce) === false) {
			throw new Exception(Salon_Component::getMsg('E013',__function__.':'.__LINE__ ) ,1);
		}
		if ( empty($_POST['target']) || ( $_POST['type'] !== 'exec' && $_POST['type'] !== 'cancel' ) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( count($this->datas) == 0   ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( $this->datas['non_regist_activate_key'] !== $this->activation_key ) {
			throw new Exception(Salon_Component::getMsg('E012',basename(__FILE__).':'.__LINE__),1 );
		}
		$now =  date_i18n("YmdHi");
		if ($this->datas['check_day'] < $now )  {
			throw new Exception(Salon_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']));
		}
		
	}



	public function show_page() {
		$status = array();
		if ($_POST['type'] == 'exec' ) $result = array('status_name'=>__('reservation completed',SL_DOMAIN));
		else $result = array('status_name'=>__('reservation deleted',SL_DOMAIN));
		
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($result).' }';
	}


}