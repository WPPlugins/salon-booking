<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Config_Page extends Salon_Page {

	private $set_items = null;

	private $config = null;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items =
			array('config_user_login','config_staff_holiday_set'
			,'config_no_prefernce','before_day','after_day','timeline_y_cnt'
			,'config_show_detail_msg','config_name_order_set','config_log'
			,'config_delete_record','config_delete_record_period'
			,'maintenance_include_staff','mobile_use','load_tab'
			,'reserve_deadline','show_tab','config_use_session'
			,'confirm_style');
	}


	public function show_page() {
?>

	<script type="text/javascript" charset="utf-8">

		var $j = jQuery;
		<?php parent::echoClientItem($this->set_items); ?>

		$j(document).ready(function() {
			$j("#salon_button_div input[type=button]").addClass("sl_button");
			<?php parent::echoSetItemLabel(false); ?>

			<?php parent::echoConfigSetLabel(parent::INPUT_BOTTOM_MARGIN); ?>

			$j("#button_update").click(function()	{
				fnClickUpdate();
			});

			<?php if ( $this->config_datas['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_user_login").attr("checked",<?php echo $set_boolean; ?>);

			<?php if ( $this->config_datas['SALON_CONFIG_DELETE_RECORD'] == Salon_Config::DELETE_RECORD_YES ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_delete_record").attr("checked",<?php echo $set_boolean; ?>);
			$j("#delete_record_period").val("<?php echo $this->config_datas['SALON_CONFIG_DELETE_RECORD_PERIOD']; ?>");

			<?php if ( $this->config_datas['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_log_need").attr("checked",<?php echo $set_boolean; ?>);
			<?php if ( $this->config_datas['SALON_CONFIG_SHOW_DETAIL_MSG'] == Salon_Config::DETAIL_MSG_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_show_detail_msg").attr("checked",<?php echo $set_boolean; ?>);
			$j("input[name=\"config_staff_holiday_set\"]").val([<?php echo $this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET']; ?>]);
			$j("input[name=\"config_name_order_set\"]").val([<?php echo $this->config_datas['SALON_CONFIG_NAME_ORDER']; ?>]);
			<?php if ( $this->config_datas['SALON_CONFIG_NO_PREFERENCE'] == Salon_Config::NO_PREFERNCE_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_no_preference").attr("checked",<?php echo $set_boolean; ?>);

			$j("#before_day").val("<?php echo $this->config_datas['SALON_CONFIG_BEFORE_DAY']; ?>");
			$j("#after_day").val("<?php echo $this->config_datas['SALON_CONFIG_AFTER_DAY']; ?>");
			$j("#timeline_y_cnt").val("<?php echo $this->config_datas['SALON_CONFIG_TIMELINE_Y_CNT']; ?>");

			<?php if ( $this->config_datas['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] == Salon_Config::MAINTENANCE_INCLUDE_STAFF ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_maintenance_include_staff").attr("checked",<?php echo $set_boolean; ?>);

			<?php if ( $this->config_datas['SALON_CONFIG_MOBILE_USE'] == Salon_Config::MOBILE_USE_YES ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_mobile_use").attr("checked",<?php echo $set_boolean; ?>);


<?php
/*
			$j("#send_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n',$this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT']); ?>");
			$j("#regist_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n',$this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_USER']); ?>");
			$j("#mail_from").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_FROM']; ?>");
			$j("#mail_returnPath").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_RETURN_PATH']; ?>");
*/
?>

			$j("input[name=\"config_load_tab\"]").val([<?php echo $this->config_datas['SALON_CONFIG_LOAD_TAB']; ?>]);

			<?php
				//
				$setMinutes = $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'];
				$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN;
				if ( (60 * 24 ) <= $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']
				|| $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] % (60 * 24 ) == 0	 ) {
					$setMinutes = round($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] / (60 * 24));
					$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY;
				}
				elseif  ($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] % 60  == 0 ) {
					$setMinutes = $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] / 60;
					$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR;
				}
			?>
			$j("#reserve_deadline").val(<?php echo $setMinutes; ?>);
			$j("#config_deadline_time_unit").val(<?php echo $setIndex; ?>);

			<?php if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_STAFF'] == Salon_Config::SHOW_TAB ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_show_staff").attr("checked",<?php echo $set_boolean; ?>);
			<?php if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_MONTH'] == Salon_Config::SHOW_TAB ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_show_month").attr("checked",<?php echo $set_boolean; ?>);
			<?php if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_WEEK'] == Salon_Config::SHOW_TAB ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_show_week").attr("checked",<?php echo $set_boolean; ?>);
			<?php if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_DAY'] == Salon_Config::SHOW_TAB ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_show_day").attr("checked",<?php echo $set_boolean; ?>);

			<?php if ( $this->config_datas['SALON_CONFIG_USE_SESSION_ID'] == Salon_Config::USE_SESSION ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_use_session").attr("checked",<?php echo $set_boolean; ?>);

			$j("#sl_confirm_style").val(<?php echo $this->config_datas['SALON_CONFIG_CONFIRM_STYLE']; ?>);


		});


		function fnClickUpdate() {
			if ( ! checkItem("data_detail","config_deadline_time_unit") ) return false;
			$j("#reserve_deadline").css({"width":"100px","margin":"3px 5px 0px 10px"});
			if ( $j("input[name=\"config_staff_holiday_set\"]:checked").val() == <?php echo Salon_Config::SET_STAFF_REVERSE;?> &&
				$j("#config_is_no_preference").prop("checked")  ) {
					alert("<?php _e('can\'t check \"No Designation of Staff\"',SL_DOMAIN); ?>");
					return false;
			}
			var set_deadline = $j("#reserve_deadline").val();

			if ( $j("#config_deadline_time_unit").val() == <?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?> ) {
				set_deadline = set_deadline * 24 * 60;
			}
			else if ($j("#config_deadline_time_unit").val() == <?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?> ) {
				set_deadline = set_deadline * 60;
			}
			else if ( (60 * 24 ) <= $j("#config_deadline_time_unit").val() ) {
				set_deadline = round(set_deadline / (60 * 24)) * (60 * 24);
			}


			var config_is_user_login = null;
			if ($j("#config_is_user_login").prop("checked") ) config_is_user_login = "checked";
			var config_is_log_need = null;
			if ($j("#config_is_log_need").prop("checked") ) config_is_log_need = "checked";
			var config_is_delete_record = null;
			if ($j("#config_is_delete_record").prop("checked") ) config_is_delete_record = "checked";
			var config_is_no_preference = null;
			if ($j("#config_is_no_preference").prop("checked") ) config_is_no_preference = "checked";
			var config_is_show_detail_msg = null;
			if ($j("#config_is_show_detail_msg").prop("checked") ) config_is_show_detail_msg = "checked";
			var config_maintenance_include_staff = null;
			if ($j("#config_maintenance_include_staff").prop("checked") ) config_maintenance_include_staff = "checked";
			var config_mobile_use = null;
			if ($j("#config_mobile_use").prop("checked") ) config_mobile_use = "checked";

			var config_mobile_use = null;
			if ($j("#config_mobile_use").prop("checked") ) config_mobile_use = "checked";

			var config_show_tab_staff = null;
			if ($j("#config_show_staff").prop("checked") ) config_show_tab_staff = "checked";
			var config_show_tab_month = null;
			if ($j("#config_show_month").prop("checked") ) config_show_tab_month = "checked";
			var config_show_tab_week = null;
			if ($j("#config_show_week").prop("checked") ) config_show_tab_week = "checked";
			var config_show_tab_day = null;
			if ($j("#config_show_day").prop("checked") ) config_show_tab_day = "checked";

			var config_use_session = null;
			if ($j("#config_is_use_session").prop("checked") ) config_use_session = "checked";



			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slconfig",
					dataType : "json",
					data: {
						"config_branch":<?php echo Salon_Config::MULTI_BRANCH; ?>
						,"config_user_login":config_is_user_login
						,"config_log":config_is_log_need
						,"config_delete_record":config_is_delete_record
						,"config_delete_record_period":$j("#delete_record_period").val()
						,"config_after_day":$j("#after_day").val()
						,"config_staff_holiday_set":$j("input[name=\"config_staff_holiday_set\"]:checked").val()
						,"config_name_order_set":$j("input[name=\"config_name_order_set\"]:checked").val()
						,"config_no_preference":config_is_no_preference
						,"config_show_detail_msg":config_is_show_detail_msg
						,"config_before_day":$j("#before_day").val()
						,"config_after_day":$j("#after_day").val()
						,"config_timeline_y_cnt":$j("#timeline_y_cnt").val()
						,"config_maintenance_include_staff":config_maintenance_include_staff
						,"config_mobile_use":config_mobile_use
						,"config_load_tab":$j("input[name=\"config_load_tab\"]:checked").val()
						,"config_reserve_deadline":set_deadline
						,"config_show_tab_staff":config_show_tab_staff
						,"config_show_tab_month":config_show_tab_month
						,"config_show_tab_week":config_show_tab_week
						,"config_show_tab_day":config_show_tab_day
						,"config_use_session":config_use_session
						,"config_confirm_style":$j("#sl_confirm_style").val()
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Config_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });
		}

		<?php parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Environment Setting',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('update',SL_DOMAIN); ?>"/>
	</div>
	<div id="data_detail" >
		<div id="config_is_user_login_wrap" class="config_item_wrap" >
			<input id="config_is_user_login" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::USER_LOGIN_OK; ?>" />
		</div>
		<div id="config_is_log_need_wrap" class="config_item_wrap" >
			<input id="config_is_log_need" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;"  value="<?php echo Salon_Config::LOG_NEED; ?>" />
		</div>
		<div id="config_is_delete_record_wrap" class="config_item_wrap" >
			<input id="config_is_delete_record" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;"  value="<?php echo Salon_Config::DELETE_RECORD_YES; ?>" />
		</div>
		<input type="text" id="delete_record_period" />
		<div id="config_is_show_detail_msg_wrap" class="config_item_wrap" >
			<input id="config_is_show_detail_msg" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::DETAIL_MSG_OK; ?>" />
		</div>
		<div id="config_staff_holday_set_wrap" class="config_item_wrap" >
			<input id="config_staff_holiday_normal" name="config_staff_holiday_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SET_STAFF_NORMAL; ?>" />
			<label for="config_staff_holiday_normal"  style="margin:5px;text-align:left;width:150px;"><?php _e('unable to enter when holidays',SL_DOMAIN); ?></label>
			<input id="config_staff_holiday_reverse" name="config_staff_holiday_set" type="radio" style="display:inline-block;width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SET_STAFF_REVERSE;?>" />
			<label for="config_staff_holiday_reverse"  style="margin:5px;display:inline-block;float:none;text-align:left;width:150px;"><?php _e('unable to enter other than when attendant',SL_DOMAIN); ?></label>
		</div>
		<div id="config_name_order_set_wrap" class="config_item_wrap" >
			<input id="config_name_order_japan" name="config_name_order_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NAME_ORDER_JAPAN; ?>" />
			<label for="config_name_order_japan"  style="width:auto;margin:5px;text-align:left;"><?php _e('Sur Name first',SL_DOMAIN); ?></label>
			<input id="config_name_order_other" name="config_name_order_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NAME_ORDER_OTHER;?>" />
			<label for="config_name_order_other" style="width:auto;margin:5px;text-align:left;"><?php _e('Given Name first',SL_DOMAIN); ?></label>
		</div>

		<div id="config_is_no_preference_wrap" class="config_item_wrap" >
			<input id="config_is_no_preference" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NO_PREFERNCE_OK; ?>" />
		</div>
		<div id="config_maintenance_include_staff_wrap" class="config_item_wrap" >
			<input id="config_maintenance_include_staff" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::MAINTENANCE_INCLUDE_STAFF; ?>" />
		</div>
		<div id="config_mobile_use_wrap" class="config_item_wrap" >
			<input id="config_mobile_use" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::MOBILE_USE_YES; ?>" />
		</div>
		<input type="text" id="before_day" />
		<input type="text" id="after_day" />
		<input type="text" id="timeline_y_cnt" />
<?php
/*
		<input type="text" id="mail_from" />
		<input type="text" id="mail_returnPath" />
		<textarea id="send_mail_text"  ></textarea>
		<textarea id="regist_mail_text"  ></textarea>
*/
?>
		<div id="config_load_tab_wrap" class="config_item_wrap" >
			<input id="config_load_staff" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_STAFF; ?>" />
			<label for="config_load_staff"  style="width:auto;margin:5px;text-align:left;"><?php _e('Staff',SL_DOMAIN); ?></label>
			<input id="config_load_month" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_MONTH; ?>" />
			<label for="config_load_month"  style="width:auto;margin:5px;text-align:left;"><?php _e('Month',SL_DOMAIN); ?></label>
			<input id="config_load_week" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_WEEK; ?>" />
			<label for="config_load_week"  style="width:auto;margin:5px;text-align:left;"><?php _e('Week',SL_DOMAIN); ?></label>
			<input id="config_load_day" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_DAY; ?>" />
			<label for="config_load_day"  style="width:auto;margin:5px;text-align:left;"><?php _e('Day',SL_DOMAIN); ?></label>
		</div>


		<div id="config_deadline_wrap" class="config_item_wrap" >
			<input type="text" id="reserve_deadline" style="width:100px;margin:3px 5px 0px 10px;" />
			<select id="config_deadline_time_unit" style="width:100px;margin:3px 5px 0px 10px;">
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN; ?>"  ><?php _e('Minute',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?>" ><?php _e('Hour',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?>" ><?php _e('Day',SL_DOMAIN); ?></option>
			</select>

		</div>

		<div id="config_show_tab_wrap" class="config_item_wrap" >
			<input id="config_show_staff" name="config_show_tab" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SHOW_TAB; ?>" />
			<label for="config_show_staff"  style="width:auto;margin:5px;text-align:left;"><?php _e('Staff',SL_DOMAIN); ?></label>
			<input id="config_show_month" name="config_show_tab" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SHOW_TAB; ?>" />
			<label for="config_show_month"  style="width:auto;margin:5px;text-align:left;"><?php _e('Month',SL_DOMAIN); ?></label>
			<input id="config_show_week" name="config_show_tab" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SHOW_TAB; ?>" />
			<label for="config_show_week"  style="width:auto;margin:5px;text-align:left;"><?php _e('Week',SL_DOMAIN); ?></label>
			<input id="config_show_day" name="config_show_tab" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SHOW_TAB; ?>" />
			<label for="config_show_day"  style="width:auto;margin:5px;text-align:left;"><?php _e('Day',SL_DOMAIN); ?></label>
		</div>

		<div id="config_is_use_session_wrap" class="config_item_wrap" >
			<input id="config_is_use_session" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::USE_SESSION; ?>" />
		</div>

		<div id="config_confirm_wrap" class="config_item_wrap" >
			<select id="sl_confirm_style"  >
				<option value="<?php echo Salon_Config::CONFIRM_BY_ADMIN; ?>" ><?php _e('Confirmation by an administrator',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::CONFIRM_NO; ?>"  ><?php _e('No confirm',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::CONFIRM_BY_MAIL; ?>" ><?php _e('Confirmation via user e-mail',SL_DOMAIN); ?></option>
			</select>
		</div>


		<div class="spacer"></div>
	</div>

<?php
	}	//show_page
}		//class

