<?php


	$url =   get_permalink();
	$parts = explode('/',$url);
	$addChar = "?";
	if (strpos($parts[count($parts)-1],"?") ) {
		$addChar = "&";
	}
	$url = $url.$addChar."sl_desktop=true";

	//スタッフデータの編集
	$edit_staff = array();
	if ($this->_is_noPreference() ) {
		$edit_staff[Salon_Default::NO_PREFERENCE]['label'] = __('Anyone',SL_DOMAIN);
		$edit_staff[Salon_Default::NO_PREFERENCE]['img']='<span class="slm_noimg" >'. __('Anyone',SL_DOMAIN).'</span>';
	}
	$reserve_possible_cnt = 0;
	foreach ($this->staff_datas as $k1 => $d1 ) {

		if ($this->config_datas['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] != Salon_Config::MAINTENANCE_NOT_INCLUDE_STAFF
			|| $d1['position_cd'] != Salon_Position::MAINTENANCE ) {

			if ($this->config_datas['SALON_CONFIG_MOBILE_NO_PHOTO'] == Salon_Config::MOBILE_NO_PHOTO || empty($d1['photo_result'][0]) ) {
				$tmp='<span class="slm_noimg" >'.htmlspecialchars($d1['name'],ENT_QUOTES).'</span>';
			}
			else {
				//ファイルが存在する場合のみ表示
				$tmpFileName = str_replace(SALON_UPLOAD_URL, "", $d1['photo_result'][0]['photo_path']);
				$tmpFileResizeName = str_replace(SALON_UPLOAD_URL, "", $d1['photo_result'][0]['photo_resize_path']);
				//httpsの場合の対処
				if (strpos($tmpFileName,"http") !== false ){
					$httpsUrl = str_replace("http","https",SALON_UPLOAD_URL);
					$tmpFileName = str_replace($httpsUrl, "", $d1['photo_result'][0]['photo_path']);
					$tmpFileResizeName = str_replace($httpsUrl, "", $d1['photo_result'][0]['photo_resize_path']);
				}
				if (file_exists (SALON_UPLOAD_DIR . $tmpFileName)
				&& file_exists (SALON_UPLOAD_DIR . $tmpFileResizeName)) {
								$tmp = "<img src='".$d1['photo_result'][0]['photo_resize_path']."'  /></a>";
					$url = site_url();
					$url = substr($url,strpos($url,':')+1);
					$url = str_replace('/','\/',$url);
					if (is_ssl() ) {
						$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
					}
					else {
						$tmp = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp);
					}
				}
				else {
					$tmp='<span class="slm_noimg" >'.htmlspecialchars($d1['name'],ENT_QUOTES).'</span>';
				}

			}
			if ($this->config_datas['SALON_CONFIG_MOBILE_NO_PHOTO'] != Salon_Config::MOBILE_NO_PHOTO ) {
				$tmp = apply_filters('salon_booking_show_setImage',$tmp,$d1);
 			}

			$edit_staff[$d1['staff_cd']]['img'] = $tmp;
			$edit_staff[$d1['staff_cd']]['label'] = htmlspecialchars($d1['name'],ENT_QUOTES);
		}
	}
	$init_target_day = date_i18n('Ymd');
	if ( $this->last_hour > 23  && $this->branch_datas["open_time"] > $this->current_time && $this->close_24 >= $this->current_time)  {
		$init_target_day = date('Ymd',strtotime(date_i18n('Y-m-d')." -1 day"));
	}

	//
	$staff_holiday_class = "slm_holiday";
	$staff_holiday_set = $this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY'];
	if (!$this->_is_staffSetNormal() ) {
		$staff_holiday_class = "slm_on_business";
		$staff_holiday_set = $this->config_datas['SALON_CONFIG_DISPLAY_ONBUSINESS'];
	}

?>
	<style>
<?php if ( strval($this->config_datas['SALON_CONFIG_CAL_SIZE']) != '') : ?>
		.ui-datepicker {
			font-size: <?php echo $this->config_datas['SALON_CONFIG_CAL_SIZE']; ?>%;
		}
<?php endif; ?>
<?php if (($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_NORMAL)
		&& ( +substr($this->last_hour,0,4) <= 2400 )) : ?>
		#slm_calendar .ui-state-default {
		  padding-bottom:21px ;
		}
<?php endif; ?>
</style>

<div id="sl_content" role="main">
	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;
		var top_pos;
		var bottom_pos;
		var today = "<?php echo $init_target_day; ?>";

		var target_day_from = new Date();
		var target_day_to = new Date();
		var save_item_cds = "";
		var operate = "";
		var save_id = "";
		var is_holiday= false;

		var save_user_login = "";

		var ajaxOn = false;


<?php /*
		var isTouch = ('ontouchstart' in window);
		var tap_interval = <?php echo Salon_Config::TAP_INTERVAL; ?>;
*/ ?>
		var staff_items = new Array();

	<?php parent::set_datepicker_date($this->branch_datas['branch_cd'],null ,unserialize($this->branch_datas['sp_dates'])); ?>

	<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				echo 'var target_yyyymmdd;';
			}
?>

		slmSchedule.config={
					day_full:[<?php _e('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN); ?>],
					day_short:[<?php _e('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN); ?>]
		};

		<?php parent::echoItemFromto($this->item_datas); ?>
		<?php parent::echoPromotionArray($this->promotion_datas); ?>

		$j(window).on('resize', function(){
			if ($j("#slm_searchdate").val()) {
				_fnCalcDisplayMonth();
				AutoFontSize();
				var dt = _fnDateConvert($j("#slm_searchdate").val() );
				var setDate = fnDayFormat(dt,"%Y%m%d");
				slmSchedule.clearWidthData();
				setDayData(setDate);
}
		});

		$j(window).load(function(){
			<?php parent::echoSetItemLabelMobile(); ?>
			<?php
				$res =  parent::echoMobileData($this->reservation_datas,$init_target_day
						,$this->first_hour,$this->last_hour
						,$this->user_inf['user_login'],$this->role);
				//現状1件だが複数件でも大丈夫なように
// 				foreach($res as $k1 => $d1 ) {
// 					echo "slmSchedule._daysStaff[\"$k1\"] = $d1;";
// 				}
			$from = new DateTime(date_i18n('Y-m-d') . " 00:00");
			if ( $this->last_hour > 23  && $this->branch_datas["open_time"] > $this->current_time && $this->close_24 >= $this->current_time)  {
// 				$from->sub(new DateInterval("P1D"));
				$from->modify("-1 day");
			}
			$to = clone $from;
//			$to->add(new DateInterval("P".$this->config_datas['SALON_CONFIG_AFTER_DAY']."D"));
			$to->modify("+".$this->config_datas['SALON_CONFIG_AFTER_DAY']." day");
			do {
				$yyyymmdd = $from->format("Ymd");
				if (isset($res[$yyyymmdd])) {
					echo "slmSchedule._daysStaff[\"$yyyymmdd\"] = $res[$yyyymmdd];";
				}
				else {
					echo "slmSchedule._daysStaff[\"$yyyymmdd\"] = \"e:0\";";
				}
//				$from->add(new DateInterval("P1D"));
				$from->modify("+1 day");
			} while($from <= $to);
		?>

			_fnCalcDisplayMonth();


			$j("#slm_main").show();
			AutoFontSize();

			<?php /*?>ヘッダがどんなかわからないのでいちづけとく<?php */?>
			top_pos = $j("#slm_main").offset().top;
			bottom_pos = top_pos + $j("#slm_main").height();
<?php //複数個別ページをひとつにまとめてTOPページにするようなテーマ(newspapere）向けの対応
			$initAnimate = '$j("html,body").animate({ scrollTop: top_pos }, "fast");';
			$initAnimate = apply_filters('salon_booking_set_init_display_for_mobile',$initAnimate);
			echo $initAnimate;
?>

			$j("#slm_page_login").hide();
			$j("#slm_page_regist").hide();
			$j("#slm_holiday").hide();
			$j("#slm_holidayBefore").hide();
			$j("#slm_holidayAfter").hide();



			<?php	if ($this->config_datas['SALON_CONFIG_DISPLAY_VALID_DATE'] == Salon_YesNo::No ) : ?>
				setDayData(today);
				var tmp = new Date(today.substr(0,4),(+today.substr(4,2))-1,today.substr(6,2));
			<?php	else : ?>
				load_day = "<?php echo $this->first_valid_yyyymmdd; ?>";
				setDayData(load_day);
				var tmp = new Date(load_day.substr(0,4),(+load_day.substr(4,2))-1,load_day.substr(6,2));
			<?php	endif; ?>

			<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) : ?>
			$j("#slm_calendar").datepicker("setDate", fnDayFormat(tmp,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
			<?php endif; ?>

		});
<?php
		if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) {
			//月データの設定
			//特殊な営業日・休業日の設定
			$tmp_table = array();
			echo "\n";
			echo 'var month_datas = {';
			if (0 < count($this->month_datas) ) {
				foreach ($this->month_datas as $k1 => $d1) {
					$tmp_table[] = '"'.$k1.'":{patern:'.$d1['emptyFull'].'}';
				}
				echo implode(',',$tmp_table);
			}
			echo '};';
		}

		if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) {
			//カテゴリーのパターンを設定する
			echo 'var category_patern = new Object();';
			foreach($this->category_datas as $k1 => $d1 ) {
				echo 'category_patern["i'.$d1['category_cd'].'"]='.$d1['category_patern'].';';
			}
		}

?>

		$j(document).ready(function() {


			var timer;

			<?php  parent::set_datepickerDefault(); ?>

			$j.datepicker.setDefaults({maxDate:new Date(<?php echo $this->datepicker_max_day; ?>)
									,minDate:new Date(<?php echo $this->datepicker_min_day; ?>)
									,showOtherMonths:true
									,selectOtherMonths:true
			});

			<?php
				if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) {
					parent::set_datepickerMobileFront("slm_calendar",$this->branch_datas,$this->month_datas);
				}
			?>


			<?php //[2014/06/22]
			foreach ($this->staff_datas as $k1 => $d1 ) {
				echo 'staff_items['.$d1['staff_cd'].'] = "'.$d1['in_items'].'";';
			}
			?>
			<?php if ($this->_is_staffSetNormal() )  : ?>
			$j(".slm_time_li").click(function() {
				var tmp_staff_cd = this.parentElement.id.split("_")[2];
				var tmp_time = +$j(this).children().text();
				if (! tmp_time ) tmp_time = <?php echo  substr($this->branch_datas["open_time"],0,2); ?>;

				_fnAddReservation(tmp_time);
				$j("#sl_staff_cd").val(tmp_staff_cd).change();
			});

			<?php endif; ?>

			<?php parent::echoSetHolidayMobile($this->branch_datas,$this->working_datas,$this->target_year,$this->first_hour);	?>

			$j("#slm_exec_login").click(function(){

				$j("#sl_content").append('<form id="sl_form" method="post" action="<?php echo wp_login_url(get_permalink() ) ?>" ><input  id="sl_log" name="log" type="hidden"/><input  id="sl_pass" name="pwd" type="hidden"/></form>');
				$j("#sl_log").val($j("#sl_login_username").val());
				$j("#sl_pass").val($j("#sl_login_password").val());
				$j("#sl_form").submit();
			});

			$j("#slm_desktop").click(function(){
				$j("#sl_content").append('<form id="sl_form" method="post" action="<?php echo get_permalink();?>" data-ajax="false" ><input id="sl_desktop" name="sl_desktop" type="hidden"/></form>');
				$j("#sl_desktop").val(true);
				$j("#sl_form").submit();
			});
			$j("#slm_login").click(function(){
				$j("#slm_page_main").hide();
				$j("#slm_page_login").show();
			});
			$j("#slm_regist_button").click(function(){
				var now = new Date();
			<?php
				if ( (60 * 24 ) <= $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] ) {
					$days = round($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] / (60 * 24));
					echo 'now.setDate(now.getDate()+'. $days .');';
				}
				else {
					echo 'now.setMinutes(now.getMinutes()+'. $this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'] .');';
				}
			?>
				var hh = _fnCheckAndGetStartHour(now.getHours()+1);
				_fnAddReservation(hh);

				$j('#sl_staff_cd').prop('selectedIndex', 0).change();
				<?php //メニューがひとつ
					if (count($this->item_datas) == 1 ) {
						echo '$j("#sl_item_cds :'.$this->set_menu_type.'").attr("checked",true);';
					}
				?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php
				if ($this->_is_editBooking() ) {
					$new_name = '';
					$new_mail = '';
					$new_tel = '';
				}
				else {
					$new_name = $this->user_inf['user_name'];
					$new_mail = $this->user_inf['user_email'];
					$new_tel = $this->user_inf['tel'];
				}
				?>
				$j("#sl_name").val("<?php echo $new_name; ?>");
				$j("#sl_tel").val("<?php echo $new_tel; ?>");
				$j("#sl_mail").val("<?php echo $new_mail; ?>");
			<?php else : ?>
				$j("#sl_name").val("");
				$j("#sl_tel").val("");
				$j("#sl_mail").val("");
			<?php endif; ?>


				$j("#sl_name").css("background","#FFFFFF");
				if ($j("#sl_tel").val() == "<?php _e('This field can\'t be empty',SL_DOMAIN); ?>" ){
					$j("#sl_tel").val("");
				}
				if ($j("#sl_mail").val() == "<?php _e('This field can\'t be empty',SL_DOMAIN); ?>" ){
					$j("#sl_mail").val("");
				}
				$j("#sl_tel").css("background","#FFFFFF");
				$j("#sl_mail").css("background","#FFFFFF");
				$j("#sl_name").focus();

			});
			$j("#slm_search").click(function(){
				var dt = _fnDateConvert($j("#slm_searchdate").val() );
				var setDate = fnDayFormat(dt,"%Y%m%d");
				setDayData(setDate);
			});
			$j("#slm_today").click(function(){

				setDayData(today);
<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) : ?>
				var tmp = new Date(today.substr(0,4),(+today.substr(4,2))-1,today.substr(6,2));
				$j("#slm_calendar").datepicker("setDate", fnDayFormat(tmp,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
<?php endif; ?>
			});
			$j("#slm_prev").click(function(){
				var dt = _fnDateConvert($j("#slm_searchdate").val() );
				setDayData(_fnCalcDay(dt,-1));
			});
			$j("#slm_next").click(function(){
				var dt = _fnDateConvert($j("#slm_searchdate").val() );
				setDayData(_fnCalcDay(dt,1));

			});
			$j("#slm_mainpage").click(function(){
				$j("#slm_page_main").show();
				$j("#slm_page_login").hide();
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			});
			$j("#slm_mainpage_regist").click(function(){
				$j("#slm_page_main").show();
				$j("#slm_page_regist").hide();
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			});
			$j("#slm_exec_regist").click(function(){
				_UpdateEvent();
			});

			$j("#slm_exec_delete").click(function() {
				if (confirm("<?php _e("This reservation delete ?",SL_DOMAIN); ?>") ) {
					operate = "deleted";
					_UpdateEvent();
				}
			});

			$j("#sl_start_time").change(function(){
				var start  = $j(this).val();
				if (start && start != -1 )	{

<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				//設定された時間で今日か明日かを判定する
				echo 'if (+start.substr(0,2) >= '.$this->first_hour.'){ '.
						'target_day_from = new Date(target_yyyymmdd);}'.
					'else {target_day_from = new Date(target_yyyymmdd);target_day_from.setDate(target_yyyymmdd.getDate()+1);}';
			}
?>


					target_day_from.setHours(start.substr(0,2));
					target_day_from.setMinutes(+start.substr(3,2));
					fnUpdateEndTime();
				}
				else {
					$j("#sl_start_time option:first").prop("selected",true);
					target_day_from.setHours($j("#sl_start_time").val().slice(0,2));
					target_day_from.setMinutes($j("#sl_start_time").val().slice(-2));
}
			});

			$j("#sl_item_cds input[type=<?php $this->echo_menu_type(); ?>]").click(function(){
				fnUpdateEndTime();
			});

			$j("#sl_coupon").change(function () {
					fnUpdateEndTime();
			});

			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>
			$j("#sl_staff_cd").change(function(){
				var checkday = +fnDayFormat(target_day_from,"%Y%m%d");
				<?php //スタッフが１件の時は自動で設定する。
				if (count($this->staff_datas) == 1 ) {
					echo '$j("#sl_staff_cd").val("'.$this->staff_datas[0]["staff_cd"].'");';
				}
				?>
		<?php //メニューがひとつ
				if (count($this->item_datas) == 1 ) : ?>
					$j("#sl_item_cds :<?php $this->echo_menu_type(); ?>").attr("checked",true);
					fnUpdateEndTime();
		<?php else: 	?>
				if ($j(this).val() == <?php echo Salon_Default::NO_PREFERENCE; ?> ) {
					$j("#sl_item_cds input").parent().show();
					$j("#sl_item_cds input").attr("disabled",false);
					$j("#sl_item_cds :<?php $this->echo_menu_type(); ?>").each(function(){
						if (checkday < item_fromto[+$j(this).val()].f ||  checkday > item_fromto[+$j(this).val()].t)  {
							$j("#sl_item_cds #slm_chk_"+$j(this).val()).attr("disabled",true);
							$j("#sl_item_cds #slm_chk_"+$j(this).val()).parent().hide();
						}
					})
				}
				else {
					var staff_cd = $j(this).val();
					$j("#sl_item_cds input").attr("disabled",true);
					$j("#sl_item_cds input").parent().hide();
					if (staff_cd) {
						var item_array = staff_items[staff_cd].split(",");
						var max_loop = item_array.length;
						for	 (var i = 0 ; i < max_loop; i++) {
							<?php //メニューの有効期間を判定する　?>
							if (item_fromto[+item_array[i]].f <= checkday && checkday <= item_fromto[+item_array[i]].t) {
								$j("#sl_item_cds #slm_chk_"+item_array[i]).attr("disabled",false);
								$j("#sl_item_cds #slm_chk_"+item_array[i]).parent().show();
							}
						}
						$j("#sl_item_cds :<?php $this->echo_menu_type(); ?>").each(function(){
							if($j(this).attr("disabled") ){
								$j(this).attr("checked",false);
							}
						})
						<?php //値段を再計算する ?>
						fnUpdateEndTime();
					}
				}
		<?php endif; ?>
			});

			<?php
				if ($this->_is_editBooking() ) {
					parent::echoClientBlur(array('customer_name'),true);
				}
				else {
					parent::echoClientBlur(array('customer_name','mail','branch_tel'),true);
				}
			?>



			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>

			$j(document).on('click','.slm_on_business',function(){
				var tmp_val = $j(this.children).text();
				_fnAddReservation(+tmp_val.split(":")[1]);
				$j("#sl_staff_cd").val(tmp_val.split(":")[0]).change();
			});

		<?php
			$echoData = "";
			echo apply_filters('salon_booking_set_documentReady',$echoData);
		?>

		});
<?php /*?>
		//登録ボタンを範囲外になったら消す。動きがいまいちなのでコメント
		$j(function() {
			$j(window).scroll(function () {
				var s = $j(this).scrollTop();
				var b = s + window.innerHeight;
				if (s + 50 < top_pos || b - 50 > bottom_pos) $j("#slm_regist").fadeOut('slow');
				else $j("#slm_regist").fadeIn('slow');

			})
		});

<?php */?>

		function _fnCheckAndGetStartHour(hh) {
			<?php //24時をまたがる営業時間の場合は、開始HHより小さい場合がある。?>
			var editHH = hh;
			if (hh <  <?php echo +$this->first_hour; ?> ) {
				editHH += 24;
			}
			if (editHH < <?php echo +$this->first_hour;?>
				||  <?php echo +$this->last_hour;?> < editHH ) {
				return <?php echo +$this->first_hour;?>;
			}
			return hh;
		}


		function _fnAddReservation (startHour) {
			<?php //過去は予約できないようにしとく ?>
			var chk_date = _fnDateConvert($j("#slm_searchdate").val() );
			if (startHour) {
				chk_date.setHours(startHour);
			}
			var now = new Date();
<?php /*
			if (now > chk_date ) {
				alert("<?php _e('The past times can not reserve',SL_DOMAIN); ?>");
				return;
			}
*/ ?>
<?php 		//24時間超えの場合はクライアント側で予約開始時刻のチェックを行わない
			if ( $this->last_hour < 24 ) {
				echo 'if (!_checkDeadline(chk_date,
						"'.$this->branch_datas["open_time"].'","'.$this->branch_datas["close_time"].'") ) return;';
			}
?>


			$j("#slm_page_main").hide();
			$j("#slm_page_regist").show();
			$j("#slm_exec_delete").hide();
			$j('#slm_exec_regist').text("<?php _e('Create Reservation',SL_DOMAIN); ?>");
			$j("#slm_target_day").text($j("#slm_searchdate").val());
			target_day_from = _fnDateConvert($j("#slm_searchdate").val() );
			if (startHour) {
				target_day_from.setHours(startHour);
			}
			target_day_to = new Date(target_day_from.getTime());
<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				//設定された時間で今日か明日かを判定する
				echo <<<EOT3
					target_yyyymmdd = new Date(target_day_from);
EOT3;
			}
?>
			save_item_cds = "";
			operate = "inserted";
			save_id = "";
			save_user_login = "";
			<?php if ( is_user_logged_in() && ! $this->_is_editBooking()) echo 'save_user_login = "'.$this->user_inf['user_login'].'"'; ?>

<?php	if ($this->_is_userlogin() && is_user_logged_in() && ! $this->_is_editBooking() ) : ?>
			$j("#sl_start_time").focus();
<?php	else : ?>
			$j("#sl_name").focus();
<?php	endif; ?>

			$j("#sl_start_time").val(toHHMM(target_day_from));
			$j("#sl_item_cds input[type=<?php $this->echo_menu_type(); ?>]").attr("checked",false);

			$j("#sl_start_time").trigger("change");
			<?php //名前電話メールは消さずに１度入力したのそのまま ?>
<?php /*?>
			$j("#sl_name").val("");
			$j("#sl_tel").val("");
			$j("#sl_mail").val("");
			$j("#sl_remark").val("");
<?php */?>

		}

		function _fnCalcDay(ymd,add) {
			var clas = Object.prototype.toString.call(ymd).slice(8, -1);
			if (clas !== 'Date') {
				return ymd;
			}
			var tmpDate = ymd.getDate();
			ymd.setDate(tmpDate + add);
			return fnDayFormat(ymd,"%Y%m%d");
		}

		function setDayData(yyyymmdd) {
			yyyymmdd=yyyymmdd+"";
			var yyyy = yyyymmdd.substr(0,4);
			var mm = yyyymmdd.substr(4,2);
			var dd = yyyymmdd.substr(6,2);
			var tmpDate = new Date(yyyy, +mm - 1,dd);



			$j("#slm_searchdate").val(fnDayFormat(tmpDate,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
			$j(".slm_tile").off("click");
			$j(".slm_tile").remove();
			$j(".slm_staff_holiday").remove();

			$j("#slm_searchdays").text(slmSchedule.config.day_full[tmpDate.getDay()]);

<?php			//予約の部分でも使用 ?>
			<?php //marginとline分として2加算　→やめ borderの左を0にする?>
			var left_start = $j("#slm_main_data ul li:first-child").outerWidth()+1;

<?php			//各liの幅が異なるので配列で ?>

			var tmp_width = Array();
			$j("#slm_main_data ul:nth-child(1) li.slm_time_li").each(function(){
				tmp_width.push($j(this).outerWidth());
			});
			var setWidth = tmp_width.join(",");
			slmSchedule.setWidth(setWidth);

<?php			//休みだったら ?>
			if (slmSchedule.chkHoliday(tmpDate) ) {
				var top = 	$j("#slm_main_data").outerHeight()	- $j("#slm_holiday").css("font-size").toLowerCase().replace("px","");
				$j("#slm_holiday").css("padding-top",top / 2 + "px");
				$j("#slm_holiday").height($j("#slm_main_data").outerHeight()- (top/2));
				$j("#slm_holiday").css("left",slmSchedule.getHolidayLeft(tmpDate,left_start));
				$j("#slm_holiday").width(slmSchedule.getHolidayWidth(tmpDate));
				$j("#slm_holiday").show();
				if (slmSchedule.chkFullHoliday(tmpDate) ) {
					$j("#slm_regist_button").hide();
					return;
				}
				else {
					$j("#slm_regist_button").show();
				}
			}
			else {
				$j("#slm_holiday").hide();
				$j("#slm_regist_button").show();
			}
<?php	//営業日にしたけど全日ではない場合 ?>
		if (slmSchedule.chkOnBusiness(tmpDate)) {
			if  (!slmSchedule.chkFullOnBusiness(tmpDate) ) {
				<?php	//営業時間の前と後ろを休みにする ?>
				var top = 	$j("#slm_main_data").outerHeight() - $j("#slm_holidayBefore").css("font-size").toLowerCase().replace("px","");
				$j("#slm_holidayBefore").css("padding-top",top / 2 + "px");
				$j("#slm_holidayBefore").height($j("#slm_main_data").outerHeight()- (top/2));
				$j("#slm_holidayBefore").css("left",left_start);
	 			$j("#slm_holidayBefore").width(slmSchedule.getOnBusinessWidthBefore(tmpDate,left_start));
				$j("#slm_holidayBefore").show();
				$j("#slm_holidayAfter").css("padding-top",top / 2 + "px");
				$j("#slm_holidayAfter").height($j("#slm_main_data").outerHeight()- (top/2));
				$j("#slm_holidayAfter").css("left",slmSchedule.getOnBusinessLeftAfter(tmpDate,left_start));
	 			$j("#slm_holidayAfter").width(slmSchedule.getOnBusinessWidthAfter(tmpDate));
				$j("#slm_holidayAfter").show();
	 			//	 			$j("#slm_holidayBefore").css("left",slmSchedule.getOnBusinessLeftBefore(tmpDate,left_start));
//	 			$j("#slm_holidayBefore").width(slmSchedule.getOnBusinessWidthBefore(tmpDate,left_start));
			}
		}
		else {
			$j("#slm_holidayBefore").hide();
			$j("#slm_holidayAfter").hide();
}
<?php			//スタッフの出退勤 ?>
			if (slmSchedule.config.staff_holidays[yyyymmdd] ) {
				for(var staff_cd_h in slmSchedule.config.staff_holidays[yyyymmdd]) {
					var tmpH = slmSchedule.config.staff_holidays[yyyymmdd][staff_cd_h];
					for(var seqH in tmpH ) {
						<?php //開始位置が終了時間より右の場合は無視する。 ?>
						if (tmpH[seqH][0] <= slmSchedule.config.close_width ) {
							var width = slmSchedule.calcWidthBase(tmpH[seqH][0] ,tmpH[seqH][1]);
							var left =  left_start + slmSchedule.calcLeftArray(tmpH[seqH][0]);
							var height = $j("#slm_st_" + staff_cd_h).outerHeight();
							var fromH = tmpH[seqH][2].substr(0,2);
							var setH = '<div class="<?php echo $staff_holiday_class; ?> slm_staff_holiday" style="position:absolute; top:0px; height: '+height+'px; left:'+left+'px; width:'+width+'px;"><?php echo $staff_holiday_set; ?><div style="display:none">'+staff_cd_h+':'+fromH+'</div></div>';

//							$j("#slm_st_"+staff_cd_h).prepend(setH);
							$j("#slm_st_"+staff_cd_h+"_dummy").before(setH);
}
					}
				}
			}
<?php //couponの組立 ?>
			if (isNeedToCheckPromotionDate ) {
				$j("#sl_coupon").remove();
				var target = yyyymmdd;
				var cn = '<select id="sl_coupon" name="coupon" class="slm_sel"><option value="">'+"<?php _e('select please',SL_DOMAIN); ?>"+'</option>';
				for(var id in promotions) {
					if(promotions[id]['from'] == 0 && promotions[id]['to'] == 20991231) {
						cn += '<option value="'+promotions[id]['key']+'">'+promotions[id]['val']+'</option>';
					}
					else {
						if (target >= promotions[id]['from'] && target <= promotions[id]['to'] ) {
							cn += '<option value="'+promotions[id]['key']+'">'+promotions[id]['val']+'</option>';
						}
					}
				}
				$j("#sl_coupon_wrap").html(cn);
				$j("#sl_coupon").change(function () {
					fnUpdateEndTime();
				});

			}



			if (slmSchedule._daysStaff[yyyymmdd]) {
				if (slmSchedule._daysStaff[yyyymmdd]["e"] == 0) {
					return;
				}
			}
			//初めての日付はサーバへ
			else {
				_GetEvent(yyyymmdd);
				return;		//抜けてデータを取ってきたらもう一度
			}

			for(var seq0 in slmSchedule._daysStaff[yyyymmdd]["d"]){
				for(var staff_cd in slmSchedule._daysStaff[yyyymmdd]["d"][seq0]){
					var base=+slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["s"];
					var height = Math.floor($j("#slm_st_" + staff_cd).outerHeight()/base)-2;	//微調整

					for(var seq1 in slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"]) {
						for(var level in slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1]) {
							var tmpb = slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1][level]["b"];
							var tmpd = slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1][level]["d"];

							var width = slmSchedule.calcWidthBase(tmpb[0] ,tmpb[1]);
							var left =  left_start + slmSchedule.calcLeftArray(tmpb[0]);

							var top = (+level) * height;
							var eid = 'slm_event_'+staff_cd+'_'+tmpb[2];
							slmSchedule._events[tmpb[2]]={"staff_cd":staff_cd,"from":tmpb[3],"to":tmpb[4]};

							var set_class = "slm_tile";
							if (tmpb[5]=="<?php echo Salon_Reservation_Status::COMPLETE; ?>") {
								set_class += " slm_myres_comp";
							}
							else if (tmpb[5]=="<?php echo Salon_Reservation_Status::TEMPORARY; ?>") {
								set_class += " slm_myres_temp";
							}

							var setcn = '<div id="'+eid+'" class="'+set_class+'"style="position:absolute; top:'+top+'px; height: '+height+'px; left:'+left+'px; width:'+width+'px;"><span title="'+tmpb[3]+'-'+tmpb[4]+'"/></div>';

							$j("#slm_st_"+staff_cd+"_dummy").prepend(setcn);

							if ((tmpb[5]=="<?php echo Salon_Reservation_Status::COMPLETE; ?>" )
<?php if ($this->isSalonAdmin())  : ?>
								|| (tmpb[5]=="<?php echo Salon_Reservation_Status::TEMPORARY; ?>")
<?php endif; ?>
								){
								slmSchedule.setEventDetail(tmpb[2],tmpd);
								$j("#"+eid).on("click",function(){
									$j("#slm_page_main").hide();
									$j("#slm_page_regist").show();
									$j("#slm_exec_delete").show();
									$j("#slm_exec_regist").text("<?php _e('Update Reservation',SL_DOMAIN); ?>");
									var ids = this.id.split("_");
									save_id = ids[3];
									var ev_tmp = slmSchedule._events[save_id];

									var settime = ev_tmp["from"].substr(0,2)+":"+ev_tmp["from"].substr(2,2);

									//target_day_from = new Date($j("#slm_searchdate").val()+" "+settime);
									target_day_from = _fnDateConvert($j("#slm_searchdate").val(),settime );

									$j("#sl_start_time").val(settime);
									save_item_cds =ev_tmp["item_cds"];


<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				//設定された時間で今日か明日かを判定する
				echo <<<EOT4
					target_yyyymmdd = new Date(target_day_from);
EOT4;
			}
?>



									$j("#sl_item_cds input").attr("checked",false);
									var item_array = save_item_cds.split(",");
									for	 (var i = 0 ,max_loop = item_array.length; i < max_loop; i++) {
										$j("#slm_chk_"+item_array[i]).attr("checked",true);
									}
									$j("#sl_staff_cd").val(ev_tmp["staff_cd"]).change();

									$j("#sl_name").val(htmlspecialchars_decode(ev_tmp["name"]));
									$j("#sl_tel").val(ev_tmp["tel"]);
									$j("#sl_mail").val(ev_tmp["mail"]);
									$j("#sl_remark").val(htmlspecialchars_decode(ev_tmp["remark"]));
									$j("#slm_target_day").text($j("#slm_searchdate").val());
									operate = "updated";
									save_user_login = ev_tmp["user_login"];
									$j("#sl_start_time").trigger("change");
									$j("#sl_coupon").val(ev_tmp["coupon"]);

						<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
									<?php parent::echoSubMenuUpdate($this->category_datas,"ev_tmp"); ?>
						<?php endif; ?>

								});
							}
						}
					}
				}
			}
		}

		<?php
		parent::echoClientItemMobile(array('booking_user_login','booking_user_password','customer_name','mail_norequired','booking_tel','staff_cd','start_time','remark','coupon'));
		?>
		<?php parent::echoDayFormat(); ?>
		<?php parent::echoCheckDeadline	($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']
										,$this->getFirstValidYYYYMMDD($this->branch_datas
		  		,$this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'])); ?>
		<?php parent::echoDateConvert(); ?>


		function AutoFontSize(){

			<?php //Div内に収まっている場合は従来通りにするが、収まっていない場合は逆算する。 ?>
			var each = $j("#slm_main_data ul li:nth-child(2)").outerWidth();
			if ($j("#slm_main_data").outerWidth() <= $j("#slm_main_data ul").outerWidth() ) {
				var sum = $j("#slm_main_data").outerWidth() - $j("#slm_main_data ul li:nth-child(1)").outerWidth();
                var div = <?php echo  +$this->last_hour - $this->first_hour + 1; ?>;
                each = sum / div;
			}
			<?php //字は12px。時間はゼロ埋めしているので2ケタ。初期表示で０の場合があるので判定をいれとく ?>
			if (each > 0 ) {
				var fpar = Math.floor(each/24*100);
 				$j(".slm_main_line li").css("font-size",fpar+"%");
 				$j(".slm_main_line li:first-child").css("font-size","100%");
			}




//			$j(".slm_main_line li:first-child").css("font-size","100%");
		}

		function _GetEvent(targetDay) {
			if (ajaxOn) return;
			ajaxOn = true;
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbooking",
					dataType : "json",
					data: {
						"target_day":targetDay
						,"branch_cd":<?php echo $this->branch_datas['branch_cd']; ?>
						,"first_hour":<?php echo $this->first_hour; ?>
						,"last_hour":<?php echo $this->last_hour; ?>
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Booking_Get_Mobile"
					},
					success: function(data) {
						ajaxOn = false;
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							slmSchedule._daysStaff[targetDay] = data.set_data[targetDay];
							setDayData(targetDay)
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						ajaxOn = false;
						alert (textStatus);
						return false;
					}
			 });
		}

		function _checkItem(target ) {
			var is_error = false;
			var item_errors = Array();
			var focusId = "";
			$j("#"+target).find("input").each(function(){
				if ($j(this).hasClass("sl_nocheck") ) return;
				var id = $j(this).attr("id");
				var cl = $j(this).attr("class");
				if (cl) {
					var val = $j(this).val();
					if ($j(this).hasClass("chk_required") ) {
						if(val == "" || val === null || val == "<?php _e('This field can\'t be empty',SL_DOMAIN); ?>" ){
							is_error = true;
							if (focusId == "") focusId = this.id;
							item_errors.push( "<?php _e("This field is mandatory. ",SL_DOMAIN); ?>" + "[" + $j("#" + id + "_lbl").text().replace(/:/g,"")+"]");
						}
					}
				}
			});

			if ( save_item_cds == "") {
				var setFieldName = "<?php _e('Menu',SL_DOMAIN); ?>"
				if ($j("#sl_staff_cd").val() == "" ) {
					setFieldName = "<?php _e('Assistant',SL_DOMAIN); ?>"
					if (focusId == "") focusId = "staff_cd";
				}
				item_errors.push( "<?php _e("This field is mandatory. ",SL_DOMAIN); ?>" + "["+ setFieldName + "]");
				is_error = true;
			}

			if (is_error) {
				$j("#"+focusId).focus();
				var msg = item_errors.join("\n");
				alert(msg);
				return false;
			}
			return true;
		}

		function _checkOther() {
			if ( save_item_cds == "") {

			}
		}

		function _UpdateEvent() {
			var temp_p2 = '';

<?php  parent::echoOver24Confirm($this->last_hour,$this->branch_datas["open_time"] ,$this->close_24,$this->current_time); ?>

			if (operate != 'inserted') {
				temp_p2 = slmSchedule._events[save_id]['p2'];
			}
			<?php	if ($this->_is_userlogin() && is_user_logged_in() && ! $this->_is_editBooking() ) : ?>
				var name = "<?php echo $this->user_inf['user_name']; ?>";
				<?php
				if (empty($this->user_inf['tel']) ) {
					echo 'var tel = $j("#sl_tel").val();';
				}
				else {
					echo 'var tel = "'.$this->user_inf['tel'].'";';
				}
				if (empty($this->user_inf['user_email']) ) {
					echo 'var mail = $j("#sl_mail").val();';
				}
				else {
					echo 'var mail = "'.$this->user_inf['user_email'].'";';
				}
				?>
			<?php else: ?>
				if (operate != 'deleted' ) {
					if (_checkItem("slm_regist_detail" ) == false ) {
						return;
					}
				}
				var name = $j("#sl_name").val();
				var tel = $j("#sl_tel").val();
				var mail = $j("#sl_mail").val();
			<?php   endif; ?>
			if (ajaxOn) return;
			ajaxOn = true;
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbooking",
					dataType : "json",
					data: {
						"staff_cd":$j("#sl_staff_cd").val()
						,"id":save_id
						,"name":name
						,"mail": mail
						,"start_date":toYYYYMMDD(target_day_from)
						,"end_date":toYYYYMMDD(target_day_to)
						,"type":operate
						,"remark": $j("#sl_remark").val()
						,"branch_cd":<?php echo $this->branch_datas['branch_cd']; ?>
						,"item_cds": save_item_cds
						,"tel": tel
						,"user_login":save_user_login
						,"coupon":$j("#sl_coupon").val()
<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
			,"sl_memo" : _getRecordArray()
<?php else : ?>
			,"sl_memo" : ""
<?php endif; ?>
						,"p2":temp_p2
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Booking_Mobile_Edit"
					},
					success: function(data) {
						ajaxOn = false;
						if (data.status == "Error" ) {
							alert(data.message);
							if (data.message.substr(0,4) == "E921" ) {
								$j("#slm_mainpage_regist").trigger("click");
							}
							if (data.field) {
								$j("#sl_"+data.field).focus();
							}
							return false;
						}
						else {

							var dtConvert = _fnDateConvert($j("#slm_searchdate").val() );
							var setDate = fnDayFormat(dtConvert,"%Y%m%d");
//							var setDate = fnDayFormat(new Date($j("#slm_searchdate").val()),"%Y%m%d");
							slmSchedule._daysStaff[setDate] = data.set_data[setDate];
							month_datas[setDate] = data.month_data[setDate];
							$j("#slm_mainpage_regist").trigger("click");
							setDayData(setDate);
							<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) : ?>
								$j("#slm_calendar").datepicker("refresh");
							<?php endif; ?>
							if (operate != "deleted")	alert(data.message);
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						ajaxOn = false;
						alert (textStatus);
						return false;
					}
			 });
		}

		function _getRecordArray() {
			var record_array = Object();
<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
	<?php parent::echoSubMenuSet(); ?>
<?php endif; ?>

			return record_array;
		}



		function fnUpdateEndTime() {
			var tmp = new Array();
			var price = 0;
			var minute = 0;
			$j("#sl_item_cds input[type=<?php $this->echo_menu_type(); ?>]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
					price += +$j(this).prev().prev().val();
					minute += +$j(this).prev().val();
				}
			});
			if ($j("#sl_coupon") && coupons[$j("#sl_coupon").val()]) {
				var coupon = coupons[$j("#sl_coupon").val()];
				if (coupon.discount_patern_cd == <?php echo Salon_Discount::PERCENTAGE; ?> ) {
					price = (1 - coupon.discount/100) * price;
					price = Math.round(price);
				}
				else {
					price -= coupon.discount;
				}
			}
			if (price < 0 ) price = 0;

			$j("#slm_price").text(Number(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
			target_day_to = new Date(target_day_from.getTime());
			target_day_to.setMinutes(target_day_to.getMinutes() + minute);
			$j("#sl_end_time").text(' - '+ toHHMM(target_day_to));

			save_item_cds = tmp.join(",");
		}

		function toYYYYMMDD( date ){
			var month = date.getMonth() + 1;
			return  [date.getFullYear(),( '0' + month ).slice( -2 ),('0' + date.getDate()).slice(-2)].join( "-" ) + " "+ ('0' + date.getHours() ).slice(-2)+ ":" + ( '0' + date.getMinutes() ).slice( -2 );
		}

		function toHHMM( date ) {
			return ('0'+date.getHours()).slice(-2)+ ":" + ('0'+date.getMinutes()).slice(-2);
		}

		function _fnCalcDisplayMonth() {

			var screen_cnt = $j(".ui-datepicker-inline").children().length;
			var base = $j(".ui-datepicker-group-first").width();
			if ( ! base  ) {
				base = $j("#slm_calendar").children().width();
				if (! base ) return;
			}
			var w = $j("#sl_content").width() ;
			if (w > base * 3 ) {
				$j("#slm_calendar").datepicker("option", "numberOfMonths", 3);
			}
			else if (w > base * 2 ) {
				$j("#slm_calendar").datepicker("option", "numberOfMonths", 2);
			}
			else {
				$j("#slm_calendar").datepicker("option", "numberOfMonths", 1);
			}
		}


</script>


<div id="slm_main" style="display:none" >
    <div id="slm_page_main" >
	<?php

		$firstRemark = "";
		$firstRemark = apply_filters('salon_booking_first_remark',$firstRemark);
		if (!empty($firstRemark)) {
			echo '<div id="sl_booking_first_remark" class="sl_add_remark" >'
			.$firstRemark
			.'</div>';
		}
	?>

		<?php if ($this->_is_userlogin() ) : ?>
        <div id="slm_header_r1" class="slm_line">
            <ul>
                <?php if (is_user_logged_in() ) : ?>
                    <li><a data-role="button"  href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('Log Out',SL_DOMAIN); ?></a></li>
                <?php else : ?>
                    <li><a data-role="button" id="slm_login" href="#slm-page-login"><?php _e('Log in',SL_DOMAIN); ?></a></li>
                <?php  endif; ?>
            </ul>
        </div>
        <?php  endif; ?>
        <div id="slm_header_r2" class="slm_line" >
<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::No) : ?>
            <ul>
			<li><a data-role="button" id="slm_prev" ><?php _e('Prev',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" id="slm_today"><?php _e('Today',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" id="slm_next"><?php _e('Next',SL_DOMAIN); ?></a></li>
            </ul>
<?php endif; ?>
		</div>
        <div id="slm_header_r3" class="slm_line" >
            <ul>
            	<li class="slm_label" ><label id="slm_searchdate_lbl" for="slm_searchdate"><?php _e('Date',SL_DOMAIN); ?>:</label></li>
            	<li class="slm_li_4" >
            		<input type="text" id="slm_searchdate" name="slm_searchdate" >
            		<span id="slm_searchdays"></span>
            	</li>
<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::Yes) : ?>

		<?php
			$today_label = __('Today',SL_DOMAIN);
			if (isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['set_label']) ) {
				$today_label = $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['set_label'];
			}
		?>
		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['is_display'] ) : ?>
            <li><a data-role="button" id="slm_today" style="display:none"></a></li>

		<?php else : ?>
            <li><a data-role="button" id="slm_today"><?php echo $today_label; ?></a></li>
		<?php endif; ?>

<?php endif; ?>

            	</ul>
            <ul><li><div id="slm_calendar"></div></li></ul>

<?php if ($this->config_datas['SALON_CONFIG_USE_DATEPICKER'] == Salon_YesNo::No) : ?>
            <ul>
            	<li><a data-role="button" id="slm_search" ><?php _e('When?',SL_DOMAIN); ?></a></li>
            </ul>
<?php endif; ?>

        </div>
	<?php
		$addTitle = "";
		$addTitle = apply_filters('salon_booking_title01',$addTitle);
		if (!empty($addTitle)) {
			echo '<div id="sl_booking_time_title" class="slm_add_title01 slm_line" >'
			.$addTitle
			.'</div>';
		}
	?>
        <div id="slm_main_data" class="slm_line slm_main_line">
            <?php
            foreach ($edit_staff as $k1 => $d1) {

                echo "<ul id=\"slm_st_{$k1}\"><li class=\"slm_first_li\"  >".$d1['img'].'</li>';
                for($i = +$this->first_hour ; $i < $this->last_hour ; $i++ ) {
					$set_i = $i;
					if ( $this->last_hour > 23 ) {
						if ($i > 23) $set_i -= 24;
					}
                    echo '<li class="slm_time_li"><span>'.sprintf("%02d",$set_i).'</span></li>';
                }
				echo "<div id=\"slm_st_{$k1}_dummy\"></div>";
                echo '</ul>';
            }
            ?>
        	<div id="slm_holiday" class="slm_holiday" style="display:none" ><?php echo $this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY']; ?></div>
        	<div id="slm_holidayBefore" class="slm_holiday" style="display:none" ><?php echo $this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY']; ?></div>
        	<div id="slm_holidayAfter" class="slm_holiday" style="display:none" ><?php echo $this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY']; ?></div>
        	<a  data-role="button"  id="slm_regist_button" class="slm_tran_button" href="javascript:void(0)" ><?php _e('Booking',SL_DOMAIN); ?></a>
        </div>

    </div>

	<?php if ($this->_is_userlogin() ) : ?>
    <div id="slm_page_login" style="display:none" >
        <div id="slm_login_detail" class="slm_line" >
	<?php if (SALON_DEMO) : ?>
		<p><?php _e('Please try settings.',SL_DOMAIN); ?></p>
		<?php echo _e('Username'); ?>: demologin
		<br /><?php echo _e('Password'); ?>: demo001
		<br /><br />
	<?php endif; ?>
        <ul><li><?php _e('Reservations are avalable without log in',SL_DOMAIN); ?></li></ul>
            <ul><li><input type="text" id="sl_login_username" value="" /></li></ul>
            <ul><li><input type="password" id="sl_login_password" value="" /></li></ul>
        </div>
        <div id="slm_footer_r2" class="slm_line">
            <ul><li><a data-role="button" id="slm_mainpage" href="#slm-page-main"><?php _e('Close',SL_DOMAIN); ?></a></li></ul>
            <ul><li><a data-role="button" id="slm_exec_login"  href="javascript:void(0)" ><?php _e('Log in',SL_DOMAIN); ?></a></li></ul>

        </div>
    </div>
    <?php endif; ?>

    <div id="slm_page_regist" style="display:none">
        <div id="slm_regist_detail" class="slm_line" >
		<ul>
        	<li class="slm_label" ><label ><?php _e('Date',SL_DOMAIN); ?>:</label></li>
			<li><span id="slm_target_day"></span></li>
        </ul>


		<?php
			//必須チェック
			$nameCheckRequired = "chk_required";
			$telCheckRequired = "chk_required";
			$mailCheckRequired = "chk_required";
			if ($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['name']['exist_check']) {
				if (array_search("chk_required", $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['name']['check']) === false) {
					$nameCheckRequired = "";
				}
			}
			if ($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['tel_customer']['exist_check']) {
				if (array_search("chk_required", $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['tel_customer']['check']) === false) {
					$telCheckRequired = "";
				}
			}
			if ($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['mail_customer']['exist_check']) {
				if (array_search("chk_required", $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['mail_customer']['check']) === false) {
					$mailCheckRequired = "";
				}
			}
			//ログインしている顧客
			if ($this->_is_userlogin() && is_user_logged_in() && ! $this->_is_editBooking() ) {
					if (empty($this->user_inf['tel']) ) {
						echo '<ul><li><input type="tel" id="sl_tel" class="'.$telCheckRequired.'" /></li></ul>';
					}
					if (empty($this->user_inf['user_email']) ) {
						echo '<ul><li><input type="mail" id="sl_mail" class="'.$mailCheckRequired.'" /></li></ul>';
					}
			}
			else {
				//スタッフは電話とメールはチェックなし
				if ( is_user_logged_in() &&  $this->_is_editBooking() ) {
					$telCheckRequired = "";
					$mailCheckRequired = "";
				}

				echo <<<EOT
					<ul><li><input type="text" id="sl_name"  class="{$nameCheckRequired}"  /></li></ul>
					<ul><li><input type="tel" id="sl_tel" class="{$telCheckRequired}" /></li></ul>
					<ul><li><input type="email" id="sl_mail"  class="{$mailCheckRequired}" /></li></ul>
EOT;
			}
		?>
		<ul>
        <li  ><select id="sl_start_time" name="start_time" class="slm_sel" >
<?php

		$dt = new DateTime($this->branch_datas['open_time']);
		$close_time = $this->branch_datas['close_time'];
		$last_hh = substr($close_time,0,2);
		if ($last_hh > 23 ) {
			$last_hh -= 24;
			$last_hour = $last_hh .":".substr($close_time,2,2);
			if ($last_hour == "0:00") $last_hour = "23:59";
			$dt_max = new DateTime($last_hour);
			$dt_max->modify('+1 days');
		}
		else {
			$last_hour = $last_hh .":".substr($close_time,2,2);
			$dt_max = new DateTime($last_hour);
		}
		$echo_data =  '';
		while($dt < $dt_max ) {
			$echo_data .= '<option value="'.$dt->format("H:i").'" >'.$dt->format("H:i").'</option>';
			$dt->modify("+".$this->branch_datas['time_step']." minutes");
		}
		echo $echo_data;
?>
			</select></li>

            <li><span id="sl_end_time" ></span></li>


	    </ul>
<?php if ( count($this->staff_datas) == 1
		&& $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['staff']['is_display'] === false) : ?>
		<ul style="display:none">
<?php else : ?>
        <ul >
<?php endif; ?>
        <li class="slm_li" >
        <select id="sl_staff_cd" name="staff_cd" class="slm_sel">

<?php
		$echo_data = '';
		if ($this->_is_noPreference() ) {
			$echo_data .= '<option value="'.Salon_Default::NO_PREFERENCE.'">'.__('Anyone',SL_DOMAIN).'</option>';
		}
		else {
			$echo_data .= '<option value="">'.__('select please',SL_DOMAIN).'</option>';
		}
		foreach($this->staff_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['staff_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
		}
		echo $echo_data;
?>
        </select></li></ul>

<?php if ( count($this->item_datas) == 1
		&& $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['menu']['is_display'] === false) : ?>
		<div id="sl_item_cds" style="display:none">
<?php else : ?>
        <div id="sl_item_cds" >
<?php endif; ?>


<?php
		if ($this->item_datas) {
			$echo_data = "";
			$echo_data .= '<ul class="slm_chk">';
			for($i = 0,$loop_max = count($this->item_datas); $i < $loop_max ; $i ++ ){
				$d1 = $this->item_datas[$i];
				$edit_price = number_format($d1['price']);
          		//$edit_name = htmlspecialchars($d1['short_name'],ENT_QUOTES);
          		$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
				$echo_data .= <<<EOT
					<li>
					<input type="hidden" id="sl_check_price_{$d1['item_cd']}" value="{$d1['price']}" />
					<input type="hidden" id="sl_check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
					<input type="{$this->set_menu_type}" name="slm_item_chk" id="slm_chk_{$d1['item_cd']}" value="{$d1['item_cd']}" />
					<label for="slm_chk_{$d1['item_cd']}">{$edit_name}<br>({$edit_price})</label>
					</li>
EOT;
			}
			$echo_data .= "</ul>";

			echo $echo_data;
		}
?>
		</div>
<?php if (count($this->promotion_datas) == 0 ) :?>
		<ul style="display:none" >
<?php else :?>
		<ul>
<?php endif; ?>
		<li class="slm_li" id="sl_coupon_wrap" >
		<select id="sl_coupon" name="coupon" class="slm_sel">
		<?php parent::echoCouponSelect("coupon",$this->promotion_datas,true); ?>
        </select></li></ul>
		<?php parent::echoSubMenu($this->config_datas['SALON_CONFIG_USE_SUBMENU'], $this->category_datas); ?>

		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['remark']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['remark']['is_display'] ) : ?>
			<ul style="display:none" ><li><textarea id="sl_remark" ></textarea></li></ul>
		<?php else : ?>
			<ul><li><textarea id="sl_remark"  ></textarea></li></ul>
		<?php endif; ?>




		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['is_display'] ) : ?>
			<ul><li><span id="slm_price" style="display:none" ></span></li></ul>
		<?php else : ?>
		<ul>
			<li class="slm_label">
				<label  >
				<?php
				 if (isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['set_label']) ) {
				 	echo $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['set_label'];
				 }
				 else {
					_e('price',SL_DOMAIN);
				 }
				?>:
				</label>
			</li>
			<li>
				<span id="slm_price"></span>
			</li>
		</ul>
		<?php endif; ?>




        </div>
        <div id="slm_footer_r3" class="slm_line">
            <ul>
            <li><a data-role="button" class="slm_tran_button" id="slm_mainpage_regist" href="#slm-page-main"><?php _e('Close',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" class="slm_tran_button" id="slm_exec_delete"  href="javascript:void(0)" ><?php _e('Cancel Reservation',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" class="slm_tran_button" id="slm_exec_regist"  href="javascript:void(0)" ><?php _e('Create Reservation',SL_DOMAIN); ?></a></li>
            </ul>

        </div>
    </div>
    <div id="slm_footer" class="slm_line">
	<?php if (Salon_Component::isMobile(false) ) : ?>

            <ul><li><a id="slm_desktop" href="javascript:void(0)" ><?php _e('Desktop',SL_DOMAIN); ?></a></li></ul>
<?php endif; ?>
<?php
	if (! $this->is_no_change_booking_items
		&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['call']['is_display'] )
		&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['call']['is_display'] ) : ?>
<?php else : ?>

		<ul><li><a class="footer-tel" href="tel:<?php echo $this->branch_datas['tel']; ?>" >
			<?php
			 if (isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['call']['set_label']) ) {
			 	echo $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['call']['set_label'];
			 }
			 else {
				_e('Telephone Here',SL_DOMAIN);
			 }
			?>
		</a></li></ul>
<?php endif; ?>

    </div>


<?php /*?>
  <div data-role="footer">
    Copyright 2013-2014, Kuu
  </div>r
<?php */?>
</div>


</div>
