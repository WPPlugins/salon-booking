<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Confirm_Page extends Salon_Page {

	private $reservation_cd = '';
	private $activation_key = '';

	private $datas = null;

	private $error_msg = '';



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		if (!empty($_GET['P1'])) $this->reservation_cd = intval($_GET['P1']);
		if (!empty($_GET['P2'])) $this->activation_key = $_GET['P2'];
	}


	public function set_reservation_datas ( $datas ) {
		$this->datas = $datas;
	}
	public function get_reservation_cd (  ) {
		return $this->reservation_cd;
	}

	public function check_request() {
		if (empty($this->reservation_cd) || empty($this->activation_key)  ) {
			$this->error_msg = Salon_Component::getMsg('E005',__LINE__);
			return;
		}
		if ( count($this->datas) == 0 )  {
			$this->error_msg = Salon_Component::getMsg('E005',__LINE__);
			return;
		}

		if ($this->datas['non_regist_activate_key'] !== $this->activation_key) {
			$this->error_msg = Salon_Component::getMsg('E012',__LINE__);
		}

		$now =  date_i18n("YmdHi");
		if ($this->datas['check_day'] < $now )  {
			$this->error_msg = Salon_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']);
		}

	}


	public function show_page() {
		if (!empty($this->error_msg) ) {
			echo '<h1>'.$this->error_msg.'</h1>';
			return;
		}

?>
		<div id="salon_confirm_detail">
				<table>
				<tbody>
					<tr><th><?php _e('name',SL_DOMAIN); ?></th><td><?php echo $this->datas['name']; ?></td></tr>
					<tr><th><?php _e('reserved day',SL_DOMAIN); ?></th><td><?php echo $this->datas['target_day']; ?>&nbsp;<?php echo $this->datas['time_from']; ?>-<?php echo $this->datas['time_to']; ?></td></tr>
					<tr><th><?php _e('reserved staff',SL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['staff_name'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('reserved menu',SL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['item_name'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('use coupon',SL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['coupon_name'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('Remark',SL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['remark'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('status',SL_DOMAIN); ?></th><td id="status_name"><?php echo $this->datas['status_name']; ?></td></tr>
					<?php if (($this->datas['status'] == Salon_Reservation_Status::TEMPORARY) && ($this->config_datas['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK)) : ?>
						<tr><th><?php _e('Register as a Member',SL_DOMAIN); ?></th><td ><input id="user_login_regist" type="checkbox"  /></td></tr>
					<?php endif; ?>
				</tbody>
				</table>
		</div>
<?php
		if ($this->datas['status'] == Salon_Reservation_Status::TEMPORARY) {
			$echo_data_exec = '<input id="button_exec" type="button" value="'.__('Create Reservation',SL_DOMAIN).'" class="sl_button"/>';
			$echo_data_cancel = '<input id="button_cancel" type="button" value="'.__('Cancel Reservation',SL_DOMAIN).'" class="sl_button"/>';
			$echo_data_exec_event = '$j("#button_exec").click(function(){if (!_checkNow() ) return;if (confirm("'.__('Reservation Completed ?',SL_DOMAIN).'") == false) return;fnFixReservation("exec");});';
		}
		elseif ($this->datas['status'] == Salon_Reservation_Status::COMPLETE) {
			$echo_data_exec = '';
			$echo_data_cancel = '<input id="button_cancel" type="button" value="'.__('Cancel Reservation',SL_DOMAIN).'" class="sl_button"/>';
			$echo_data_exec_event = '';
		}
?>
		<div id="salon_button_div" >
			<?php echo $echo_data_exec; ?>
			<?php echo $echo_data_cancel; ?>
		</div>
		<script type="text/javascript">
			var $j = jQuery
			$j("#button_cancel").click(function(){

				if (!_checkNow() ) return;
				if (confirm("<?php _e('Cancel a reservation ?',SL_DOMAIN); ?>") == false) return;
				fnFixReservation("cancel");
			});
			<?php echo $echo_data_exec_event; ?>
			function fnFixReservation(action) {
				var isRegist = null;
				if ($j("#user_login_regist").prop("checked")) isRegist="checked";
				$j.ajax({
						type: "post"
						,url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slconfirm"
						,dataType : "json"
						,data: {
							"type":action
							,"target":"<?php echo $this->reservation_cd; ?>"
							,"P2":"<?php echo $this->activation_key; ?>"
							,"nonce":"<?php echo $this->nonce; ?>"
							<?php if (($this->datas['status'] == Salon_Reservation_Status::TEMPORARY) && ($this->config_datas['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK)) : ?>
							,"is_regist": isRegist
							<?php endif; ?>
							,"menu_func":"Confirm_Edit"
						}

						,success: function(data) {
							if (data.status == "Error" ) {
								alert(data.message);
								return false;
							}
							$j("#status_name").text(data.set_data["status_name"]);
							$j("#button_cancel").val("<?php _e('Cancel Reservation',SL_DOMAIN); ?>");
							$j("#button_exec").remove();
							if (action == "cancel" ) {
								$j("#button_cancel").remove();
							}
						}
						,error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
							return false;
						}
				});
			}
			function _checkNow() {
				var target = new Date(<?php echo sprintf("%d,%d,%d,%d,%d,%d",substr($this->datas['check_day'],0,4),substr($this->datas['check_day'],4,2)-1,substr($this->datas['check_day'],6,2), substr($this->datas['check_day'],8,2),substr($this->datas['check_day'],10,2), 59); ?>);
				var now = new Date();
				if(target.getTime() < now.getTime()) {
					alert("<?php echo Salon_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']); ?>");
					return false;
				}
				return true;
			}
		</script>
<?php
	}	//show_page
}		//class

