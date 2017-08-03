<div id="sl_content" role="main">

	<link rel="stylesheet" href="<?php echo SL_PLUGIN_URL.SALON_CSS_DIR; ?>dhtmlxscheduler.css" type="text/css" charset="utf-8" />

	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;
		var target_day_from = new Date();
		var target_day_to = new Date();
		var save_target_event = "";
		var save_item_cds = "";
		var all_duplicate_cnt;
		var staff_duplicates = new Array();
		var staff_items = new Array();
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";
		var save_name = "";
		var item_name = new Array();


<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				echo 'var target_yyyymmdd;';
			}
?>

		var is_collision_err = false;

		<?php parent::echoItemFromto($this->item_datas); ?>
		<?php parent::echoPromotionArray($this->promotion_datas); ?>
<?php
	if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) {
		//カテゴリーのパターンを設定する
		echo 'var category_patern = new Object();';
		foreach($this->category_datas as $k1 => $d1 ) {
			echo 'category_patern["i'.$d1['category_cd'].'"]='.$d1['category_patern'].';';
		}
	}
?>
		$j(document).ready(function() {
			<?php parent::echoSearchCustomer($this->url); //検索画面 ?>

			scheduler.config.multi_day = true;
			scheduler.config.all_timed = true;
			scheduler.config.prevent_cache = true;
			scheduler.config.first_hour= <?php echo +$this->first_hour; ?>;
			scheduler.config.last_hour= <?php echo +$this->last_hour; ?>;
			scheduler.config.last_hour_48 = <?php echo +$this->close_48; ?>;
<?php 		//24時間超えの場合
			$over24 = false;
			if ( $this->close_48 > 2400 ) {
				echo 'scheduler.is_one_day_event = function(ev) {return true;};';
				$over24 = true;
			}
			$this->echo_customize_dhtmlx($over24);
?>
			scheduler.config.time_step = <?php echo $this->branch_datas['time_step']; ?>;
	<?php //予約の必須時間 ?>
			scheduler.config.event_duration = 60;
			scheduler.config.auto_end_date = true;
			scheduler.config.xml_date= "%Y-%m-%d %H:%i";
			scheduler.config.details_on_create=true;
			scheduler.config.details_on_dblclick=true;
	<?php //小さいメニューバーを出さない ?>
			scheduler.xy.menu_width = 0;

	<?php //現時点のどっど表示は出さない（位置がずれる） ?>
			scheduler.config.mark_now = false;
			scheduler.config.check_limits = false;
	<?php //locale_jaを使用しないように
			$today_label = "";
			if (isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['set_label']) ) {
				$today_label = $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['set_label'];
			}
			parent::echoLocaleDef($today_label);
	?>

	<?php //休業日
			$is_todayholiday = parent::echoSetHoliday($this->branch_datas,$this->target_year);
			//スタッフの設定
			$tmp_staff_index = array();
			$index = 1;
			if ($this->_is_noPreference() ) {
				echo 'var staffs=[{key:'.Salon_Default::NO_PREFERENCE.', label:"'.__('Anyone',SL_DOMAIN).'" },';
			}
			else {
				echo 'var staffs=[ ';
			}
			$comma = '';
			$reserve_possible_cnt = 0;
			foreach ($this->staff_datas as $k1 => $d1 ) {
				if ($this->config_datas['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] != Salon_Config::MAINTENANCE_NOT_INCLUDE_STAFF
					|| $d1['position_cd'] != Salon_Position::MAINTENANCE ) {
					//写真大きさを50pxにしとく。IEだと自動で補正してくれない？
	//				$tmp = preg_replace("/(width|height)(=\\\'\d+\\\')/","$1=\'50\'",$d1['photo']);
					$tmp = "";
					if (!empty($d1['photo_result'][0]) ) {
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
							$tmp = "<a href='".$d1['photo_result'][0]['photo_path']."' rel='staff".$d1['staff_cd']."' ' class='lightbox' ><img src='".$d1['photo_result'][0]['photo_resize_path']."' alt='' style='max-width=50px; max-height:50px; margin:0;' class='alignnone size-thumbnail wp-image-186' /></a>";
						}
					}
					$url = site_url();
					$url = substr($url,strpos($url,':')+1);
					$url = str_replace('/','\/',$url);
					if (is_ssl() ) {
						$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
					}
					else {
						$tmp = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp);
					}
					echo $comma.'{key:'.$d1['staff_cd'].', label:"<div class=\'sl_staff_name\' >'.htmlspecialchars($d1['name'],ENT_QUOTES).$tmp.'</div>" }';
					$tmp_staff_index[$d1['staff_cd']] = $index;
					$index++;
					$comma = ',';
				}
			}
			echo '];';
			$reserve_possible_cnt = 0;
			$is_duplicate_ok = false;
			foreach ($this->staff_datas as $k1 => $d1 ) {
				echo 'staff_duplicates['.$d1['staff_cd'].'] = '.$d1['duplicate_cnt'].';';
				if ($d1['duplicate_cnt'] > 0 ) {
					$is_duplicate_ok = true;
				}
				echo 'staff_items['.$d1['staff_cd'].'] = "'.$d1['in_items'].'";';
				$reserve_possible_cnt += 1+$d1['duplicate_cnt'];

			}


			$timeline_array = array();
// 			foreach ($this->working_datas as $k1 => $d1 ) {
// 				$tmp = (string)(intval(substr($k1,0,4))).','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0);
// 				$next_day  = new DateTime($k1);
// 				$next_day->modify('+1 days');
// 				$tmp_next = $next_day->format("Ymd");
// 				$tmp_next = (string)(intval(substr($tmp_next,0,4))).','.(string)(intval(substr($tmp_next,4,2))-1).','.(string)(intval(substr($tmp_next,6,2))+0);
// 				foreach ($d1 as $k2 => $d2 ) {
// 					$start_time = ','.(string)(intval(substr($d2['in_time'],8,2))).','.(string)(intval(substr($d2['in_time'],10,2)));
// 					$end_time = ','.(string)(intval(substr($d2['out_time'],8,2))).','.(string)(intval(substr($d2['out_time'],10,2)));
// 					if (substr($d2['in_time'],0,8) == substr($d2['out_time'],0,8) ){
// 						$tmp_timeline = '{ "start_date":new Date('.$tmp.$start_time.'),"end_date":new Date('.$tmp.$end_time.'),"staff_cd":"'.$d2['staff_cd'].'"}';
// 					}
// 					else {
// 						$tmp_timeline = '{ "start_date":new Date('.$tmp.$start_time.'),"end_date":new Date('.$tmp_next.$end_time.'),"staff_cd":"'.$d2['staff_cd'].'"}';
// 					}
// 					$timeline_array[] = $tmp_timeline;
// 				}
// 			}
			foreach ($this->working_datas as $k1 => $d1 ) {
				$tmp = (string)(intval(substr($k1,0,4))).','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0);
				$next_day  = new DateTime($k1);
				$next_day->modify('+1 days');
				$tmp_next = $next_day->format("Ymd");
				$tmp_next = (string)(intval(substr($tmp_next,0,4))).','.(string)(intval(substr($tmp_next,4,2))-1).','.(string)(intval(substr($tmp_next,6,2))+0);
				foreach ($d1 as $k2 => $d2 ) {
					$in = $d2['in_time'];
					$ot = $d2['out_time'];
if ($over24 && (intval(substr($in,8,2))) > 24  ){
	$work_hh = intval(substr($in,8,2)) - 24;
					$start_time =
					(string)(intval(substr($in,0,4))).','.(string)(intval(substr($in,4,2))-1).','.(string)(intval(substr($in,6,2))+0)
					.','.(string)($work_hh).','.(string)(intval(substr($in,10,2)));
}
else {
					$start_time =
					(string)(intval(substr($in,0,4))).','.(string)(intval(substr($in,4,2))-1).','.(string)(intval(substr($in,6,2))+0)
					.','.(string)(intval(substr($in,8,2))).','.(string)(intval(substr($in,10,2)));
}
if ($over24 && (intval(substr($ot,8,2))) > 24  ){
	$work_hh = intval(substr($ot,8,2)) - 24;
					$end_time =
					(string)(intval(substr($ot,0,4))).','.(string)(intval(substr($ot,4,2))-1).','.(string)(intval(substr($ot,6,2))+0)
					.','.(string)($work_hh).','.(string)(intval(substr($ot,10,2)));
}
else {
					$end_time =
					(string)(intval(substr($ot,0,4))).','.(string)(intval(substr($ot,4,2))-1).','.(string)(intval(substr($ot,6,2))+0)
					.','.(string)(intval(substr($ot,8,2))).','.(string)(intval(substr($ot,10,2)));

}
					$tmp_timeline = '{ "start_date":new Date('.$start_time.'),"end_date":new Date('.$end_time.'),"staff_cd":"'.$d2['staff_cd'].'"}';
					$timeline_array[] = $tmp_timeline;
				}
			}

			foreach ($this->item_datas as $k1 => $d1 ) {
				echo 'item_name['.$d1['item_cd'].']= "'.$d1['name'].'";';
			}


			if ($this->_is_staffSetNormal() ) {
				$tmp_css = 'dhx_sl_holiday';
				$tmp_type = 'dhx_time_block';
				$tmp_html = htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY'],ENT_QUOTES);
			}
			else {
				$tmp_css = 'on_business';
				$tmp_type = '';
				$tmp_html = htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_ONBUSINESS'],ENT_QUOTES);
			}


			echo 'var staff_holidays = [ '.implode(',',$timeline_array).' ];';
			echo <<<EOT3
				for (var i=0; i<staff_holidays.length; i++) {
					var options = {
						start_date: staff_holidays[i].start_date,
						end_date: staff_holidays[i].end_date,
						type: "{$tmp_type}",
						css: "{$tmp_css}",
						sections: { timeline:[staff_holidays[i].staff_cd] },
						html: "{$tmp_html}"
					};
					scheduler.addMarkedTimespan(options);
				}
EOT3;

			echo sprintf('all_duplicate_cnt = %d;',$reserve_possible_cnt + $this->branch_datas['duplicate_cnt']);
	?>

			var durations = {
				day: 24 * 60 * 60 * 1000,
				hour: 60 * 60 * 1000,
				minute: 60 * 1000
			};

			var get_formatted_duration = function(start, end) {
				var diff = end - start;
				var days = Math.floor(diff / durations.day);
				diff -= days * durations.day;
				var hours = Math.floor(diff / durations.hour);
				diff -= hours * durations.hour;
				var minutes = Math.floor(diff / durations.minute);
				var results = [];
				if (days) results.push(days + " days");
				if (hours) results.push(hours + " hours");
				if (minutes) results.push(minutes + " minutes");
				return results.join(", ");
			};
			var resize_date_format = scheduler.date.date_to_str(scheduler.config.hour_date);
			scheduler.templates.event_bar_text = function(start, end, event) {
				if (event.edit_flg == <?php echo Salon_Edit::OK; ?> )  {
					var state = scheduler.getState();
					if (state.drag_id == event.id) {
						return resize_date_format(start) + " - " + resize_date_format(end) + " (" + get_formatted_duration(start, end) + ")";
					}
				}
				return htmlspecialchars(event.text); // default
			};


	<?php //担当者画面のタブ ?>
			scheduler.locale.labels.timeline_tab = "<?php _e('Staff',SL_DOMAIN); ?>";
	<?php //section_autoheightはスタッフの人数が多い場合はfalseにする
		  //height/dx(10人)より小さい場合はsection_autoheightをtrueにする
		  //calculate_dayはminuteだと日単位で移動しないのでカスタマイズ ?>
			scheduler.createTimelineView({
					section_autoheight: false,
					name: "timeline",
					x_unit: "minute",
					x_date: "%H",
					x_step: 60,
					x_size: <?php echo $this->last_hour - $this->first_hour; ?>,
					x_start: <?php echo +$this->first_hour; ?>,
					x_length:24,
					y_unit: staffs,
					y_property:"staff_cd",
					folder_events_available: true,
					dx:50,
					dy:<?php echo self::Y_PIX/$this->config_datas['SALON_CONFIG_TIMELINE_Y_CNT']; ?>,
					render:"bar" ,
					event_dy: "full"
			});

		<?php	if ($this->config_datas['SALON_CONFIG_DISPLAY_VALID_DATE'] == Salon_YesNo::No ) : ?>
			scheduler.init('scheduler_here',new Date("<?php echo date_i18n('Y/m/d'); ?>"),"<?php $this->_echoLoadTab(); ?>");
		<?php	else : ?>
			load_day = "<?php echo $this->first_valid_yyyymmdd; ?>";
			var tmp = new Date(load_day.substr(0,4),(+load_day.substr(4,2))-1,load_day.substr(6,2));
			scheduler.init('scheduler_here',tmp,"<?php $this->_echoLoadTab(); ?>");
		<?php	endif; ?>

			scheduler.templates.event_text=function(start,end,event){
				var title_name = htmlspecialchars(event.name);
				if ((event.edit_flg == <?php echo Salon_Edit::OK; ?> ) && (title_name != '')) {
							title_name = "<?php _e('Dear %s',SL_DOMAIN); ?>".replace("%s",title_name);
				}
				return "<b>"+title_name+"</b>";
			}
			scheduler.load("<?php echo $this->url; ?>/wp-admin/admin-ajax.php?action=slbooking&menu_func=Booking_Get_Event&branch_cd=<?php echo $this->branch_datas['branch_cd']; ?>",function() {
				$j(".lightbox").colorbox(
					{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
					,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
			});
			var dp = new dataProcessor("<?php echo $this->url; ?>/wp-admin/admin-ajax.php?action=slbooking&menu_func=Booking_Edit");
			dp.init(scheduler);
			dp.defineAction("error",function(response){
				if (response.getAttribute('sid') )	{
					var id = response.getAttribute('sid') ;
					if (response.getAttribute('func') == "inserted" ) 	scheduler.deleteEvent(id);
					else {
						if (save_target_event ) {
							save_target_event._dhx_changed = false;
							scheduler._lame_copy(scheduler._events[id],save_target_event);
							scheduler.updateEvent(id);
						}
					}
				}
				alert(response.getAttribute("message"));
				return false;
			})

			dp.setTransactionMode("POST",false);
			dp.attachEvent("onBeforeUpdate",function(id,status, data){
				data.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return true;
			})

			dp.attachEvent("onAfterUpdate",function(sid,action,tid,xml_node){
				if (action == "invalid" ) {
					if (save_target_event ) {
						save_target_event._dhx_changed = false;
						scheduler._lame_copy(scheduler._events[sid],save_target_event);
						scheduler.updateEvent(save_target_event.id);
					}
				}
				else if (action != "invalid" && action != "deleted") {
					scheduler._events[tid].type = '';
					scheduler._events[tid].edit_flg = xml_node.getAttribute("edit_flg");
					scheduler._events[tid].name = xml_node.getAttribute("name");
					scheduler._events[tid].text = _edit_text_name(xml_node.getAttribute("name"));
					scheduler._events[tid].status = xml_node.getAttribute("status");
					scheduler._events[tid].p2 = xml_node.getAttribute("p2");
					scheduler._events[tid].user_login = xml_node.getAttribute("user_login");
					var setAfterDate = scheduler.date.str_to_date(scheduler.config.xml_date,scheduler.config.server_utc);
					scheduler._events[tid].end_date = setAfterDate(xml_node.getAttribute("end_date"));
					scheduler.updateEvent(tid);
					$j(".lightbox").colorbox(
							{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
							,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});

					if (xml_node.getAttribute("alert_msg") ) {
						alert(xml_node.getAttribute("alert_msg"));
					}

				}
				return true;
			})

			scheduler.templates.event_class=function(start,end,event){
				if (event.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) return "user_temporary";
				if (event.edit_flg == <?php  echo Salon_Edit::NG; ?> ) return "user_no_edit";
			}

			scheduler.attachEvent("onEventCreated",function(id){
				var ev = this.getEvent(id);
				ev.edit_flg = <?php  echo Salon_Edit::OK; ?>;
					<?php if ( is_user_logged_in() ) : ?>
						<?php
						if ($this->_is_editBooking() ) {
							$new_name = '';
							$new_mail = '';
							$new_tel = '';
							$user_login = '';
						}
						else {
							$new_name = $this->user_inf['user_name'];
							$new_mail = $this->user_inf['user_email'];
							$new_tel = $this->user_inf['tel'];
							$user_login = $this->user_inf['user_login'];
						}
						?>
						ev.name = '<?php echo $new_name; ?>';
						ev.mail = '<?php echo $new_mail; ?>';
						ev.tel = '<?php echo $new_tel; ?>';
						ev.status = <?php echo Salon_Reservation_Status::INIT; ?>;
						ev.user_login = '<?php echo $user_login; ?>';
					<?php else : ?>
						ev.name = '';
						ev.mail = '';
						ev.tel = '';
						ev.status = <?php echo Salon_Reservation_Status::TEMPORARY; ?>;
						ev.user_login = '';
					<?php endif; ?>
					ev.remark = '';
					ev.item_cds = '';
					ev.type = 'new';
					ev.memo = JSON.stringify("");
					var start = ev.start_date.getHours() * 100 + ev.start_date.getMinutes();
			<?php if ( $this->close_48 <= 2400) : ?>
					if (start <  <?php	echo +$this->branch_datas['open_time']; ?> ) {
			<?php else: ?>
					var states = scheduler.getState();
					if ( states.mode == "month"
					&& start <  <?php	echo +$this->branch_datas['open_time']; ?>) {
			<?php endif; ?>
						ev.start_date.setHours(<?php	echo +substr($this->branch_datas['open_time'],0,2); ?>);
						ev.start_date.setMinutes(<?php	echo +substr($this->branch_datas['open_time'],2,2); ?>);
					}
			});
			<?php //ここがドラッグドロップ部分でのイベント ?>
			scheduler.attachEvent("onBeforeEventChanged", function(ev, native_event, is_new){
				$j(".lightbox").colorbox(
						{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
						,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
				if (!is_collision_err ) return;
				if (!is_new && ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					alert("<?php _e('This reservation is not completed.So you can not move',SL_DOMAIN); ?>");
					return;
				}
				var is_check = true;
				if (ev.staff_cd) {
					is_check = checkStaffHolidayLogic(ev.staff_cd,ev.start_date,ev.end_date);
				}
<?php /*
				if ( (new Date() ) > ev.start_date ) {
					is_check = false;
					alert("<?php _e('The past times can not reserve',SL_DOMAIN); ?>");
				}
*/ ?>
				if ( !_checkDeadline(ev.start_date,"<?php echo $this->branch_datas["open_time"]; ?>","<?php echo $this->branch_datas["close_time"]; ?>") ) {
				is_check = false;
				}
				if ( ev.start_date > new Date(<?php echo $this->insert_max_day; ?>) ) {
					is_check = false;
					alert("<?php echo sprintf(__('The future times can not reserved. please less than %s days ',SL_DOMAIN),$this->config_datas['SALON_CONFIG_AFTER_DAY']); ?>");
				}

				if (scheduler._drag_event.staff_cd  && ev.staff_cd != <?php echo Salon_Default::NO_PREFERENCE; ?> && ev.staff_cd != scheduler._drag_event.staff_cd ) {
					var item_array = staff_items[ev.staff_cd].split(",");
					var set_item_array = ev.item_cds.split(",");
					var max_loop = set_item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						if (item_array.indexOf(set_item_array[i]) == -1) {
							is_check = false;
							alert("<?php echo _e('This staff member can not treat this menu ',SL_DOMAIN); ?>["+ item_name[set_item_array[i]] + "]");
							break;
						}
					}
				}

				if (this._drag_mode){
					save_target_event = scheduler._lame_clone(scheduler._drag_event);
				}
				else {
					save_target_event = "";
				}
				return is_check;
			});
			scheduler.attachEvent("onClick",allow_own);
			scheduler.attachEvent("onDblClick",allow_own);
			function allow_own(id){

				var is_check = true;
				var ev = this.getEvent(id);
<?php /*
				if ( (new Date() ) > ev.start_date ) {
					is_check = false;
					alert("<?php _e('past data can not edit',SL_DOMAIN); ?>");
				}
*/ ?>
				if (!_checkDeadline(ev.start_date,"<?php echo $this->branch_datas["open_time"]; ?>","<?php echo $this->branch_datas["close_time"]; ?>") ) {
					is_check = false;
				}

				<?php if ( ! $this->_is_editBooking() ) : ?>
				else if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
						is_check = false;
						alert("<?php _e('tempolary data can not update',SL_DOMAIN); ?>");
				}
				<?php endif; ?>

				else if ( ev.edit_flg == <?php  echo Salon_Edit::NG; ?> ) {
						is_check = false;
						alert("<?php _e('this data can not edit',SL_DOMAIN); ?>");
				}
				if ( ev.start_date > new Date(<?php echo $this->insert_max_day; ?>) ) {
					is_check = false;
					alert("<?php echo sprintf(__('future data can not reserved. please less than %s days ',SL_DOMAIN),$this->config_datas['SALON_CONFIG_AFTER_DAY']); ?>");
				}
				if (is_check ) 	ev.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return is_check;
			}
<?php /*
			scheduler.attachEvent("onBeforeViewChange",function(from_mode, from_date, to_mode, to_date) {
				console.log(from_mode + ' ' + from_date + ' ' + to_mode + ' ' + to_date);
				return true;
			});
*/ ?>

			function checkDisplayDate(mode) {
				$j("dhx_cal_date").hide();
				if (mode == "timeline" || mode == "month" ) {
					if (mode == "timeline" && 320 < $j(".dhx_cal_navline").width() ) {
						$j("dhx_cal_date").show();
					}
					if (mode == "month" && 315 < $j(".dhx_cal_navline").width() ) {
						$j("dhx_cal_date").show();
					}
				}
			}
<?php /*
// 			scheduler.attachEvent("onBeforeViewChange",function(from_mode, from_date, to_mode, to_date) {
// 				checkDisplayDate(to_mode);
// 				return true;
v// 			});
  */
?>

			scheduler.attachEvent("onSchedulerResize",function() {
				var state = scheduler.getState();
				checkDisplayDate(state.mode);
				return true;
			});

			scheduler.attachEvent("onViewChange", function(mode, date) {

 				checkDisplayDate(mode);

				if (mode == "timeline" ) {
					$j(".lightbox").colorbox(
					{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
					,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
				}
			});

			$j( '#sl_login_password' ).keypress( function ( e ) {
				if ( e.which == 13 ) {
					$j("#sl_button_login").click();
					return false;
				}
			} );

			$j("#sl_button_login").click(function(){
				var val = $j("#sl_login_username").val();
				if(val == "" || val === null){
					$j("#sl_login_username").focus();
					return;
				}
				var val = $j("#sl_login_password").val();
				if(val == "" || val === null){
					$j("#sl_login_password").focus();
					return;
				}

				$j("#sl_booking_login_div").append('<form id="sl_form" method="post" action="<?php echo wp_login_url(get_permalink() ) ?>" ><input  id="sl_log" name="log" type="hidden"/><input  id="sl_pass" name="pwd" type="hidden"/></form>');
				$j("#sl_log").val($j("#sl_login_username").val());
				$j("#sl_pass").val($j("#sl_login_password").val());
				$j("#sl_form").submit();
			});

			$j("#sl_button_mobile").click(function(){
				$j("#sl_booking_mobile").append('<form id="sl_form" method="post" action="<?php echo get_permalink();?>" ><input id="sl_desktop" name="sl_desktop" type="hidden"/></form>');
				$j("#sl_desktop").val(false);
				$j("#sl_form").submit();
			});

			$j("#sl_button_insert").click(function(){
				$j("#sl_search_result").html("");
				$j("#sl_search").hide();
				save_form();
			});

			$j("#sl_button_close").click(function(){
				$j("#sl_search_result").html("");
				$j("#sl_search").hide();
				close_form();
			});

			$j("#sl_button_delete").click(function(){
				var msg = "<?php _e("This reservation delete ?",SL_DOMAIN); ?>";
				var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					msg ="<?php _e('This is temporary reservation.\nContinue?',SL_DOMAIN); ?>";
				}
				if (confirm(msg) ) {
						$j("#sl_search_result").html("");
						$j("#sl_search").hide();
						delete_booking_data();
					}
			});

			$j("#sl_coupon").change(function () {
				fnUpdateEndTime();
			});


			$j("#sl_item_cds input[type=<?php $this->echo_menu_type(); ?>]").click(function(){
				fnUpdateEndTime();
			});

			$j("#sl_start_time").change(function(){
				var start  = $j(this).val();
				if (start != -1 )	{
					if (start.length < 5 ) start="0"+start;
<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				//設定された時間で今日か明日かを判定する
				echo 'if (+start.substr(0,2) >= '.+$this->first_hour.'){ '.
						'target_day_from = new Date(target_yyyymmdd);}'.
					'else {target_day_from = new Date(target_yyyymmdd);target_day_from.setDate(target_yyyymmdd.getDate()+1);}';
			}
?>
					target_day_from.setHours(start.substr(0,2));
					target_day_from.setMinutes(+start.substr(3,2));
					fnUpdateEndTime();
				}
			});


<?php
	if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) {
		echo '$j("#sl_end_time").change(function(){';
		echo 'var end  = $j(this).val();';
		echo 'if (end != -1 )	{';
		echo 'if (end.length < 5 ) end="0"+end;';
		//24時間超えの場合
		if ( $this->last_hour > 23 ) {
			//設定された時間で今日か明日かを判定する
			echo 'if (+end.substr(0,2) >= '.$this->first_hour.'){ '.
					'target_day_to = new Date(target_yyyymmdd);}'.
				'else {target_day_to = new Date(target_yyyymmdd);target_day_from.setDate(target_yyyymmdd.getDate()+1);}';
		}
		echo 'target_day_to.setHours(end.substr(0,2));';
		echo 'target_day_to.setMinutes(+end.substr(3,2));';
		echo '}';
		echo '});';
	}
?>

			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>
			$j("#sl_staff_cd").change(function(){
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
				var checkday = +fnDayFormat(target_day_from,"%Y%m%d");
				if (!$j(this).val()  ) {
					$j("#sl_item_cds input").attr("disabled",true);
					$j("#sl_item_cds .sl_items_label").addClass("sl_items_disable");
				}
				else if ( $j(this).val() == <?php echo Salon_Default::NO_PREFERENCE; ?>) {
					$j("#sl_item_cds input").attr("disabled",false);
					$j("#sl_item_cds .sl_items_label").removeClass("sl_items_disable");
					$j("#sl_item_cds :<?php $this->echo_menu_type(); ?>").each(function(){
						if (checkday < item_fromto[+$j(this).val()].f ||  checkday > item_fromto[+$j(this).val()].t) {
							$j("#sl_item_cds #sl_check_"+$j(this).val()).attr("disabled",true);
							$j("#sl_item_cds #sl_items_lbl_"+$j(this).val()).addClass("sl_items_disable");
						}
					})
				}
				else {
					var staff_cd = $j(this).val();
					$j("#sl_item_cds input").attr("disabled",true);
					$j("#sl_item_cds .sl_items_label").addClass("sl_items_disable");
					var item_array = staff_items[staff_cd].split(",");
					var max_loop = item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						<?php //メニューの有効期間を判定する?>
						if (item_array[i] != "" ) {
							if (item_fromto[+item_array[i]].f <= checkday && checkday <= item_fromto[+item_array[i]].t) {
								$j("#sl_item_cds #sl_check_"+item_array[i]).attr("disabled",false);
								$j("#sl_item_cds #sl_items_lbl_"+item_array[i]).removeClass("sl_items_disable");
							}
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
				<?php endif; ?>
			});
			<?php
			if ( $this->config_datas['SALON_CONFIG_USE_BLUR'] == Salon_YesNo::Yes ) {
 				if ($this->_is_editBooking() ) {
					parent::echoClientBlur(array('customer_name'));
				}
				else {
					parent::echoClientBlur(array('customer_name','mail','branch_tel'));
				}
			}
			?>
			<?php //[2014/06/22]ここまで ?>
			<?php parent::echoSetItemLabel(false); ?>
			<?php parent::echo_clear_error(); ?>

			$j("#sl_booking_button_div input").addClass("sl_button");
			$j("#sl_customer_booking_form").hide();
			$j("#sl_customer_booking_form").prependTo("body");
			$j("#sl_price").addClass("sl_detail_out");
			var prev = $j("#sl_price").prev();
			$j(prev).addClass("sl_detail_out");
			$j("#sl_detail_out span").addClass("sl_detail_out");
			$j("#sl_detail_out label").addClass("sl_detail_out");

<?php if ( $this->last_hour > 23  && $this->branch_datas["open_time"] > $this->current_time && $this->close_24 >= $this->current_time)  : ?>

			scheduler.setCurrentView(scheduler.date.add( scheduler.date[scheduler._mode+"_start"](scheduler._date),(-1),scheduler._mode));
<?php endif; ?>

			$j(".lightbox").colorbox(
				{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
				,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});


		});

		function fnUpdateEndTime() {
			var tmp = new Array();
			var price = 0;
			var minute = 0;
			$j("#sl_item_cds input[type=<?php $this->echo_menu_type(); ?>]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
					price += +$j(this).next().val();
					minute += +$j(this).next().next().val();
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

			$j("#sl_price").text(Number(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
			target_day_to = new Date(target_day_from.getTime());
			target_day_to.setMinutes(target_day_to.getMinutes() + minute);
<?php
		if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) {
			echo '$j("#sl_end_time").val(target_day_to.getHours() + ":" + (target_day_to.getMinutes()<10?"0":"") + target_day_to.getMinutes());';
		}
		else {
			echo '$j("#sl_end_time").text(" - "+ ("0"+target_day_to.getHours()).slice(-2) + ":" + (target_day_to.getMinutes()<10?"0":"") + target_day_to.getMinutes());';
		}
?>
			save_item_cds = tmp.join(",");
		}

		<?php Salon_Page::echoDayFormat(); ?>

		function fnDetailInit( ev ) {
			if (ev) {
				$j("#sl_target_day").text(fnDayFormat(ev.start_date,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
<?php 		//24時間超えの場合
			if ( $this->last_hour > 23 ) {
				//設定された時間で今日か明日かを判定する
				$msg_format = __('%m/%d/%Y',SL_DOMAIN);
				$tmpFirst = +$this->first_hour;
				echo <<<EOT3
					target_yyyymmdd = new Date(ev.start_date.getTime());
					if (ev.start_date.getHours() < {$tmpFirst}
					&& dhtmlXScheduler._mode != "month")  {
						target_yyyymmdd.setDate(target_yyyymmdd.getDate()-1);
						\$j("#sl_target_day").text(fnDayFormat(target_yyyymmdd,"{$msg_format}"));
					}
EOT3;
			}
?>
				target_day_from = new Date(ev.start_date.getTime());
				$j("#sl_item_cds input").attr("checked",false);
				if (ev.type) {
					$j("#sl_button_insert").val("<?php _e('Create Reservation',SL_DOMAIN); ?>");
					$j("#sl_button_delete").hide();
					<?php	if ($this->_is_editBooking() ) 	echo '$j("#sl_button_search").show();'; ?>
				}
				else {
					$j("#sl_button_insert").val("<?php _e('Update Reservation',SL_DOMAIN); ?>");
					$j("#sl_button_delete").show();
					<?php	if ($this->_is_editBooking() ) echo '$j("#sl_button_search").hide();'; ?>
				}
				save_user_login = ev.user_login;

				var item_array = ev.item_cds.split(",");
				var max_loop = item_array.length;
				for	 (var i = 0 ; i < max_loop; i++) {
					$j("#sl_item_cds #sl_check_"+item_array[i]).attr("checked",true);
				}
				$j("#sl_name").val( htmlspecialchars_decode(ev.name) );
				$j("#sl_mail").val( ev.mail );
				$j("#sl_tel").val( ev.tel );
				$j("#sl_remark").val( htmlspecialchars_decode(ev.remark) );
				$j("#sl_staff_cd").val( ev.staff_cd ).change();
				$j("#sl_name").attr("readonly", false);
				$j("#sl_mail").attr("readonly", false);
				$j("#sl_tel").attr("readonly", false);
				<?php if ( !is_user_logged_in() ||  $this->_is_editBooking() ) : ?>
					$j("#sl_name").focus();
				<?php else : ?>
					$j("#sl_name").attr("readonly", true);
					$j("#sl_mail").attr("readonly", true);
					if (ev.tel) $j("#sl_tel").attr("readonly", true);
					$j("#sl_staff_cd").focus();
				<?php endif; ?>
				$j("#sl_start_time").val(('0'+ev.start_date.getHours()).slice(-2)+":"+('0'+ev.start_date.getMinutes()).slice(-2));
				save_target_event = scheduler._lame_clone(ev);
				<?php	if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) : ?>
					$j("#sl_end_time").val(('0'+ev.end_date.getHours()).slice(-2)+":"+('0'+ev.end_date.getMinutes()).slice(-2));
				<?php else : ?>
					fnUpdateEndTime();
				<?php endif; ?>

				$j("#sl_rstatus").text("");
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					$j("#sl_rstatus").text("<?php _e('tentative',SL_DOMAIN); ?>");
				}
				else if (ev.status == <?php echo Salon_Reservation_Status::COMPLETE; ?> ) {
					$j("#sl_rstatus").text("<?php _e('completed',SL_DOMAIN); ?>");
				}

				if (isNeedToCheckPromotionDate ) {
					$j("#sl_coupon").remove();
					var target = fnDayFormat(ev.start_date,"%Y%m%d");
					var cn = '<select id="sl_coupon"><option value="">'+"<?php _e('select please',SL_DOMAIN); ?>"+'</option>';
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
					$j("#sl_coupon_lbl").after(cn);
					$j("#sl_coupon").change(function () {
						fnUpdateEndTime();
					});
				}

				$j("#sl_coupon").val(ev.coupon).change();
				<?php	if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) : ?>
					$j("#sl_end_time").val(('0'+ev.end_date.getHours()).slice(-2)+":"+('0'+ev.end_date.getMinutes()).slice(-2));
				<?php endif; ?>

	<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
					var tmpDetail = Array();
					tmpDetail['memo'] = JSON.parse( htmlspecialchars_decode(ev.memo)) ;
				<?php parent::echoSubMenuUpdate($this->category_datas,"tmpDetail"); ?>
	<?php endif; ?>

				<?php parent::echo_clear_error(); ?>
			}
		}
		<?php
			if ($this->_is_editBooking() ) {
				parent::echoClientItem(array('customer_name','mail_norequired','booking_tel','staff_cd','item_cds','start_time','remark','booking_user_login','booking_user_password','regist_customer','coupon'));
			}
			else {
				parent::echoClientItem(array('customer_name','mail','branch_tel','staff_cd','item_cds','start_time','remark','booking_user_login','booking_user_password','regist_customer','coupon'));
			}
		?>


		scheduler.showLightbox = function(id){
			$j("#sl_customer_booking_form").show();
			$j("#sl_data_detail").show();
			var ev = scheduler.getEvent(id);
			scheduler.startLightbox(id, $j("#sl_customer_booking_form").get(0));
			fnDetailInit(ev);
		}

		scheduler.checkCollision = function(ev) {
			if (ev.type && (ev.type == "new") && (scheduler._mode == "month") ) {
				ev.nonce = "<?php echo $this->nonce; ?>";
				is_collision_err = true;
				return true;
			}
			if (ev.edit_flg && (ev.edit_flg == <?php echo Salon_Edit::NG; ?> ) ) return false;
			res = checkDuplicate(ev);
			if (res ) 	res = checkStaffHoliday(ev,'','',true);
			ev.nonce = "<?php echo $this->nonce; ?>";
			is_collision_err = res;
			return res;
		}

		function checkHolidayLogic(from,to) {
			var global = scheduler._marked_timespans.global;
			<?php //_marked_timespansには、該当日の0:0:0が設定されている?>
			var t_sd = scheduler.date.date_part(new Date(from));
			var fromZone = from.getHours() * 60 + from.getMinutes();
			var toZone = to.getHours() * 60 + to.getMinutes();
<?php 		//24時間超えの場合
			if ( $this->close_48 > 2400 ) {
				echo <<<EOT1
			var tmpfrom = ("0" + from.getHours()).slice(-2) + ("0" + from.getMinutes()).slice(-2);
			if (tmpfrom <  "{$this->branch_datas["open_time"]}" ) {
				t_sd.setDate(t_sd.getDate() - 1);
				fromZone += 60 * 24;
				toZone += 60 * 24;
			}
EOT1;
			}
?>

			if ( global[t_sd.valueOf()] ) {
<?php /*
				if (global[t_sd.valueOf()]["dhx_time_block"]) return false;	//特別な休み
				if (global[t_sd.valueOf()]["default"]) return true;			//特別な営業日
*/ ?>
				if (global[t_sd.valueOf()]["dhx_time_block"]) {	//特別な休み
					if ( toZone <= global[t_sd.valueOf()]["dhx_time_block"][0]["zones"][0] || global[t_sd.valueOf()]["dhx_time_block"][0]["zones"][1] <= fromZone ) {
					}
					else {
						return false;
					}
				}
				if (global[t_sd.valueOf()]["default"]) {	//特別な営業日
					if ( global[t_sd.valueOf()]["default"][0]["zones"][0] <= fromZone &&  toZone <= global[t_sd.valueOf()]["default"][0]["zones"][1]   ) {
						return true;
					}
					else {
						return false;
					}
				}

			}
<?php //[2014/10/01]半休対応 曜日で判定すると全部NGになるのでコメント
//			if ( global[from.getDay()] && global[from.getDay()]["dhx_time_block"]) return false;
//[2017/04/18]以下は不要なチェック
//			if ( global[from.valueOf()] && global[from.valueOf()]["dhx_time_block"]) return false;
?>
		}

<?php //[2014/10/01]半休対応 曜日のチェック?>
		function checkHolidayZone (from,to) {
			var global = scheduler._marked_timespans.global;

			var fromZone = from.getHours() * 60 + from.getMinutes();
			var toZone = to.getHours() * 60 + to.getMinutes();

			var t_sd = scheduler.date.date_part(new Date(from));

<?php 		//24時間超えの場合
			if ( $this->close_48 > 2400 ) {
				echo <<<EOT1
			var tmpfrom = ("0" + from.getHours()).slice(-2) + ("0" + from.getMinutes()).slice(-2);
			if (tmpfrom <  "{$this->branch_datas["open_time"]}" ) {
				t_sd.setDate(t_sd.getDate() - 1);
				fromZone += 60 * 24;
				toZone += 60 * 24;
			}
EOT1;
			}
?>

			if ( global[t_sd.valueOf()] ) {
<?php /*
				if (global[t_sd.valueOf()]["dhx_time_block"]) return false;	//特別な休み
				if (global[t_sd.valueOf()]["default"]) return true;			//特別な営業日
*/ ?>
				if (global[t_sd.valueOf()]["dhx_time_block"]) {	//特別な休み
					if ( toZone <= global[t_sd.valueOf()]["dhx_time_block"][0]["zones"][0] || global[t_sd.valueOf()]["dhx_time_block"][0]["zones"][1] <= fromZone ) {
						return true;
					}
					else {
						return false;
					}
				}
				if (global[t_sd.valueOf()]["default"]) {	//特別な営業日
					if ( global[t_sd.valueOf()]["default"][0]["zones"][0] <= fromZone &&  toZone <= global[t_sd.valueOf()]["default"][0]["zones"][1]  ) {
						return true;
					}
					else {
						return false;
					}

				}
			}

			if ( !global[t_sd.getDay()] ) return true;

			if ( toZone <= global[t_sd.getDay()].dhx_time_block[0]["zones"][0] || global[t_sd.getDay()].dhx_time_block[0]["zones"][1] <= fromZone ) {
			}

			else {
				return false;
			}
			return true;
		}
		function checkStaffHolidayLogic(staff_cd,from,to) {
			if (staff_cd) {
				var timeline = scheduler._marked_timespans.timeline;
<?php 		//24時間超えの場合
			if ( $this->close_48 >= 2400 ) {
				echo <<<EOT
					if (from.getDate() == scheduler.getState().date.getDate()) {
						var tmp_st = scheduler.date.date_part(new Date(from));
					}
					else {
						var tmp_st = scheduler.date.date_part(new Date(scheduler.getState().date));
					}
EOT;
			}
			else {
				echo 'var tmp_st = scheduler.date.date_part(new Date(from));';
			}
?>
			<?php if ($this->_is_staffSetNormal() ) : ?>
				if (timeline && timeline[staff_cd]) {
					<?php //日付単位　?>
					var tmp_working = timeline[staff_cd][tmp_st.valueOf()];
					if (tmp_working) {
						var tmp_working_detail = tmp_working["dhx_time_block"];
						for(var l=0; l<tmp_working_detail.length; l++){

							var zones = tmp_working_detail[l].zones;
							if (zones) {
								for (var k=0; k<zones.length; k += 2) {
									var zone_start = zones[k];
									var zone_end = zones[k+1];

									var start_date = new Date(+tmp_working_detail[l].days + zone_start*60*1000);
									var end_date = new Date(+tmp_working_detail[l].days + zone_end*60*1000);
								}
								if (from <= to && start_date <= from && from <= end_date && start_date <= to && to <= end_date ) return false;
							}

						}
					}
				}
			<?php else: ?>
				if (timeline && timeline[staff_cd]) {
					<?php //２４時間を超えた場合は翌日分もほしい。[TODO]２４時超えしない場合の考慮 ?>
					for (var m = 0 ; m < 2 ; m++ ) {
						var tmp_yyyymmdd = 	new Date(tmp_st);
						tmp_yyyymmdd.setDate(tmp_yyyymmdd.getDate()+m);
						var tmp_working = timeline[staff_cd][tmp_yyyymmdd.valueOf()];
						if (tmp_working) {

							var tmp_working_detail = tmp_working["default"];
							for(var l=0; l<tmp_working_detail.length; l++){

								var zones = tmp_working_detail[l].zones;
								if (zones) {
									for (var k=0; k<zones.length; k += 2) {
										var zone_start = zones[k];
										var zone_end = zones[k+1];

										var start_date = new Date(+tmp_working_detail[l].days + zone_start*60*1000);
										var end_date = new Date(+tmp_working_detail[l].days + zone_end*60*1000);
									}
									<?php //あっていればここでひっかかる ?>
									if (start_date <= from  && to <= end_date ) return true;
								}
							}
						}
					}
					return false;
				}
				else return false;
			<?php endif; ?>
			}
			return true;

		}

		function checkStaffHoliday(ev,from,to,isMove) {
			var day_from,day_to,staff_cd;
			if (! from) {
				day_from = ev.start_date;
				day_to = ev.end_date;
				staff_cd = ev.staff_cd;
			}
			else {
				day_from = from;
				day_to = to;
				staff_cd = $j("#sl_staff_cd").val();
			}
			var msg = '';
			if (isMove && isMove==true) {
				if (checkHolidayLogic(day_from,day_to) == false ) {
					msg = "<?php _e('can not be reserved ',SL_DOMAIN); ?>";
				}
			}
			if (checkStaffHolidayLogic (staff_cd,day_from,day_to) == false ) {
				<?php if ($this->_is_staffSetNormal() ) : ?>
					msg = "<?php _e('today this staff can not be reserved ',SL_DOMAIN); ?>";
				<?php else: ?>
					msg = "<?php _e('this staff can not be reserved in this time range',SL_DOMAIN); ?>";
				<?php endif; ?>
			}
			if (msg != '' ) {
				if ( $j("#sl_data_detail").is(":hidden")) {
					alert(msg);
				}
				else {
					var label = $j("#sl_staff_cd").prev().children(".small");
					label.text(msg)
					label.addClass("error small");
				}
				return false;
			}
			return true;
		}

		function checkDuplicate(ev,from,to) {
		<?php
		//スタッフ単位の重複を許す場合のチェックはサーバ側にまかせる
		if ($is_duplicate_ok) {
			echo "return true;";
		}
		?>
			var staff_cd;
			var is_do_form = true;
			var from;
			var to;
			<?php //ドラッグでの起動の場合、fromはなし ?>
			if (! from) {
				from = ev.start_date;
				to = ev.end_date;
				staff_cd = ev.staff_cd;
				is_do_form = false;
			}
			else {
				staff_cd = $j("#sl_staff_cd").val();
			}
			<?php //登録しようとしている予約の範囲の予約を全部取得する ?>
			var evs = scheduler.getEvents(from, to);
			var ev_cnt = 0;
			var staff_array = new Array();
			for (var i=0; i<evs.length; i++) {
				if (evs[i].id != ev.id) {
					ev_cnt++;
					if (staff_array[evs[i].staff_cd]) staff_array[evs[i].staff_cd] += 1;
					else staff_array[evs[i].staff_cd] = 1;

					if (ev.user_login && evs[i].user_login == ev.user_login) {
						if ( is_do_form) {
							var label = $j("#sl_start_time").prev().children(".small" );
							label.text("<?php _e('your reservation is duplicated',SL_DOMAIN); ?>")
							label.addClass("error small");
							var label = $j("#sl_name").prev().children(".small" );
							label.text("<?php _e('your reservation is duplicated',SL_DOMAIN); ?>")
							label.addClass("error small");
						}
						return false;
					}
				}
			}
			var is_error = false;

			if (staff_cd  != <?php echo Salon_Default::NO_PREFERENCE; ?> ) {
				if (staff_array[staff_cd] > staff_duplicates[staff_cd] ) is_error = true;
			}
<?php	//全体数のチェックはスタッフごとの重複がない場合でもサーバにまかせる
// 			if ( ev_cnt >= all_duplicate_cnt ) is_error = true;
?>
			if ( is_error && is_do_form) {
				if (ev.staff_cd == $j("#sl_staff_cd").val()) {
					var label = $j("#sl_start_time").prev().children(".small" );
					label.text("<?php _e('reservation_time is duplicated ',SL_DOMAIN); ?>")
					label.addClass("error small");
				}
				else {
					var label = $j("#sl_staff_cd").prev().children(".small");
					label.text("<?php _e('this staff is reserved ',SL_DOMAIN); ?>")
					label.addClass("error small");
				}
			}

			return !is_error;
		}

		function _edit_text_name (name ) {
			var edit_name = "<?php _e('Dear %s',SL_DOMAIN); ?>";
			return edit_name.replace("%s",name);
		}

		function save_form() {

<?php
		if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) {
			echo 'if ( ! checkItem("sl_data_detail","sl_end_time") ) return false;';
		}
		else {
			echo 'if ( ! checkItem("sl_data_detail") ) return false;';
		}
?>
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
			<?php if ( $this->_is_editBooking() ) : ?>
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					if (!confirm("<?php _e('This is temporary reservation.\nIf you will update,this reservation is completed.\nContinue?',SL_DOMAIN); ?>") ) return false;
				}
			<?php endif; ?>
			<?php //ドラッグでの移動チェックはサーバ側にまかせる。こちらはチェックを入れる。そうしないとまた再入力になってしまう） ?>
			if (! checkHolidayZone(target_day_from,target_day_to) ) {
				alert("<?php _e("This time zones can not be reserved",SL_DOMAIN); ?>");
				return false;
			}
			if ( ! checkStaffHoliday(ev,target_day_from,target_day_to) ) return false;
			<?php
			//ここでuser_loginを入れておく。そうしないとここのチェックを正常になった後で、
			//onEventAddedの中のcheckDuplicatedでエラーになり
			//eventが消されてしまい後続処理ができない
			//新規で検索した後に名前などを一部変更した場合は、userloginをクリアする
			?>
			if (ev.type && ev.type == 'new' && (save_name != $j("#sl_name").val() || save_tel !=  $j("#sl_tel").val() || save_mail != $j("#sl_mail").val() ))
				save_user_login = "";
			ev.user_login =	save_user_login;
			if ( ! checkDuplicate(ev,target_day_from,target_day_to) ) return false;
			if (!_checkDeadline(target_day_from,"<?php echo $this->branch_datas["open_time"]; ?>","<?php echo $this->branch_datas["close_time"]; ?>") ) return false;


<?php  parent::echoOver24Confirm($this->last_hour,$this->branch_datas["open_time"] ,$this->close_24,$this->current_time); ?>
<?php /* if ( $this->last_hour > 23  && $this->branch_datas["open_time"] > $this->current_time && $this->close_24 >= $this->current_time)  : ?>
			var time_from_hhmm = ('0'+target_day_from.getHours()).slice(-2)+('0'+target_day_from.getMinutes()).slice(-2);
			 if (<?php echo $this->branch_datas["open_time"]; ?>  > time_from_hhmm ) {
				 if (! confirm("<?php _e('Is this Date OK ? ',SL_DOMAIN); ?>"+"["+fnDayFormat(target_day_from,"<?php _e('%m/%d/%Y',SL_DOMAIN); ?>")+"]") ) return false;
			}


<?php endif; */ ?>

			ev.name = $j("#sl_name").val();
			ev.text = _edit_text_name($j("#sl_name").val());
			ev.tel =  $j("#sl_tel").val();
			ev.mail = $j("#sl_mail").val();
			ev.start_date = target_day_from;
			ev.end_date = target_day_to;
			ev.staff_cd = $j("#sl_staff_cd").val();
			ev.item_cds = save_item_cds;
			ev.remark = $j("#sl_remark").val();
			ev.coupon = $j("#sl_coupon").val();
<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
			ev.memo = JSON.stringify(_getRecordArray());
<?php else: ?>
			ev.memo = "";
<?php endif; ?>
			scheduler.endLightbox(true, $j("#sl_data_detail").get(0));


			$j("#sl_customer_booking_form").hide();
 			$j(".lightbox").colorbox(
					{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
					,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
		}

		function _getRecordArray() {
			var record_array = Object();
<?php if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) : ?>
	<?php parent::echoSubMenuSet(); ?>
<?php endif; ?>

			return record_array;
		}
		<?php parent::echoCheckDeadline	($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']

				,$this->getFirstValidYYYYMMDD($this->branch_datas
								  		,$this->config_datas['SALON_CONFIG_RESERVE_DEADLINE'])); ?>

		function delete_booking_data() {
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
			ev.nonce = "<?php echo $this->nonce; ?>";
			scheduler.deleteEvent(ev.id);
			scheduler.endLightbox(false, $j("#sl_data_detail").get(0));
			$j("#sl_customer_booking_form").hide();
			$j(".lightbox").colorbox(
				{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
				,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
		}
		function close_form(argument) {
			$j("#sl_customer_booking_form").hide();
			scheduler.endLightbox(false, $j("#sl_data_detail").get(0));
			$j(".lightbox").colorbox(
				{maxWidht:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"
				,maxHeight:"<?php echo $this->config_datas['SALON_CONFIG_PHOTO_SMALL_RATIO']; ?>"});
		}

<?php
		if ($this->config_datas['SALON_CONFIG_MENU_TYPE'] == Salon_Category::RADIO) {
			parent::echoCheckClinet(array('chk_required','chkMail','chkTime','lenmax','reqCheck_radio','chkSpace','chkTel','reqOther'));
		}
		else {
			parent::echoCheckClinet(array('chk_required','chkMail','chkTime','lenmax','reqCheck','chkSpace','chkTel','reqOther'));
		}
?>
		<?php parent::echoRemoveModal(); ?>

	</script>
	<style>
<?php 	if ( SALON_FOR_THEME
	|| isset($this->config_datas['SALON_CONFIG_DO_NEW_FUNCTION'])) : ?>
<?php //if (Salon_Color::PC_BACK != strtoupper($this->config_datas['SALON_CONFIG_PC_BACK_COLOR']) ) : ?>
.dhx_cal_container
,.dhx_cal_tab.active
,.dhx_scale_bar
,.dhx_scale_hour
,.dhx_month_head
{
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_BACK_COLOR'];?>;
}


.dhx_cal_event .dhx_header
, .dhx_cal_event .dhx_title
, .dhx_cal_event .dhx_body
, .dhx_cal_event .dhx_footer
, .dhx_cal_event_line {
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_EVENT_COLOR'];?>;
	border-color:<?php echo $this->config_datas['SALON_CONFIG_PC_EVENT_LINE_COLOR'];?>;
}

.dhx_cal_event_line .dhx_event_resize{
	background:transparent;
}

.dhx_cal_event .dhx_footer {
	height:0px;
	border-width:0px;
}
.dhx_cal_tab {
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_UNSELECTED_BACK_COLOR'];?>;
}

.dhx_now .dhx_month_head
,  .dhx_now .dhx_month_body{
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_SELECTED_BACK_COLOR'];?>;
}

.dhx_cal_prev_button {
	background-image:none;
	border:1px dotted;
	text-align:center;
}
.dhx_cal_prev_button:after {
	content: "<";
}
.dhx_cal_next_button {
	background-image:none;
	border:1px dotted;
	text-align:center;
	left:37px;
}
.dhx_cal_next_button:after {
	content: ">";
}
.dhx_cal_today_button {
	background-image:none;
	border:1px dotted;
	left:73px;
}

.dhx_sl_holiday {
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_HOLIDAY_COLOR'];?>;
}

.on_business {
	background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_ONBUSINESS_COLOR'];?>;
}

.dhx_cal_data td {
/*     background: transparent ; */
}
.dhx_cal_navline .dhx_cal_date {
	left:126px;
}

.dhx_cal_container {
	font-family: 'Raleway', sans-serif;
}
	<?php endif;?>
.dhx_scale_cell_plus
{
background-color:#ffffff;
}
#sl_data_detail input:focus
,#sl_data_detail textarea:focus
,#sl_data_detail select:focus {
	background: <?php echo $this->config_datas['SALON_CONFIG_PC_FOCUS_COLOR'];?>;
}

	</style>

	<?php if (Salon_Component::isMobile(false) ) : ?>
    	<div id="sl_booking_mobile" >
			<input type="button" value="<?php _e('Mobile Version',SL_DOMAIN); ?>" id="sl_button_mobile"  >
    	</div>
    <?php endif; ?>

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
		<div id="sl_booking_login_div" >
	<?php if (SALON_DEMO) : ?>
		<p><?php _e('Please try settings.',SL_DOMAIN); ?></p>
		<?php echo _e('Username'); ?>: demologin
		<br /><?php echo _e('Password'); ?>: demo001
		<br /><br />
	<?php endif; ?>
		<?php if ( is_user_logged_in() ) : ?>
			<?php if ($this->_is_editBooking() ) : ?>
				<a href="<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin" ><?php _e('admin here',SL_DOMAIN); ?></a><br>
				<a href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('logout here',SL_DOMAIN); ?></a>
			<?php else : ?>
				<?php echo sprintf( __('Dear %s',SL_DOMAIN),$this->user_inf['user_name']); ?>
				<a href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('logout here',SL_DOMAIN); ?></a>
			<?php endif; ?>
		<?php else : ?>
				<p><?php _e('Reservations are available without log in',SL_DOMAIN); ?></p>
				<div class="login_inner" >
				<input type="text" id="sl_login_username" value="" />
				</div>
				<div class="login_inner" >
				<input type="password" id="sl_login_password" value="" />
				</div>
				<div class="login_inner" >
				<label  >&nbsp;</label>
				<input type="button" value="<?php _e('Log in',SL_DOMAIN); ?>" id="sl_button_login" class="sl_button"  />
				</div>
		<?php endif; ?>
			<div class="spacer"></div>
		</div>
	<?php endif; ?>
	<?php
		$onlyPcRemark = "";
		$onlyPcRemark = apply_filters('salon_booking_only_pc_remark',$onlyPcRemark);
		if (!empty($onlyPcRemark)) {
			echo '<div id="sl_booking_only_pc_remark" class="sl_add_remark" >'
			.$onlyPcRemark
			.'</div>';
		}
	?>

	<div id="scheduler_here" class="dhx_cal_container" >
		<div class="dhx_cal_navline">
			<div class="dhx_cal_prev_button">&nbsp;</div>
			<div class="dhx_cal_next_button">&nbsp;</div>
		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['today']['is_display'] ) : ?>
				<?php if (Salon_Color::PC_BACK == strtoupper($this->config_datas['SALON_CONFIG_PC_BACK_COLOR']) ) : ?>
					<div class="dhx_cal_date" id="dhx_cal_date" style="left:60px;"></div>
				<?php else : ?>
					<div class="dhx_cal_date" id="dhx_cal_date" style="left:78px;"></div>
				<?php endif; ?>
		<?php else : ?>
			<div class="dhx_cal_today_button"></div>
			<div class="dhx_cal_date" id="dhx_cal_date" ></div>
		<?php endif; ?>
		<?php
			$set_tab_right = 132;
			$set_tab_width = 42;
			if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_STAFF'] == Salon_Config::SHOW_TAB) {
				echo '<div class="dhx_cal_tab" name="timeline_tab" style="right:'.$set_tab_right.'px; width:50px;"></div>';
				$set_tab_right -= $set_tab_width;
			}
			if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_DAY'] == Salon_Config::SHOW_TAB) {
				echo '<div class="dhx_cal_tab" name="day_tab" style="right:'.$set_tab_right.'px;"></div>';
				$set_tab_right -= $set_tab_width;
			}
			if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_WEEK'] == Salon_Config::SHOW_TAB) {
				echo '<div class="dhx_cal_tab" name="week_tab" style="right:'.$set_tab_right.'px;"></div>';
				$set_tab_right -= $set_tab_width;
			}
			if ( $this->config_datas['SALON_CONFIG_PC_DISPLAY_TAB_MONTH'] == Salon_Config::SHOW_TAB) {
				echo '<div class="dhx_cal_tab" name="month_tab" style="right:'.$set_tab_right.'px;"></div>';
			}
		?>
		</div>
		<div class="dhx_cal_header"></div>
		<div class="dhx_cal_data"></div>
	</div>

	<div id="sl_customer_booking_form" class="salon_form">
	<div id="sl_data_detail" >
		<div id="sl_detail_out">
			<label  ><?php _e('Date',SL_DOMAIN); ?>:</label>
			<span id="sl_target_day" ></span>
			<label  ><?php _e('Status',SL_DOMAIN); ?>:</label>
			<span id="sl_rstatus"  ></span>

		</div>
<?php if ($this->_is_editBooking() ) : ?>
		<input type="text" id="sl_name" class="sl_name_short" />
		<input id="sl_button_search" type="button" class="sl_button" value=<?php _e('Search',SL_DOMAIN); ?> />
<?php else: ?>
		<input type="text" id="sl_name" />
<?php endif; ?>
		<input type="text" id="sl_tel"/>
		<input type="text" id="sl_mail"  />

		<div id="sl_date_time_wrap" >
				<?php parent::echoTimeSelect("sl_start_time",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step']); ?>
				<?php
				if (($this->_is_editBooking())&&(!empty($this->branch_datas['notes']))) {
					parent::echoTimeSelect("sl_end_time",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step'],false,'class="sl_patern_sel"',true);
				}
				else {
					echo '<span id="sl_end_time" ></span>';
				}
				?>
		</div>
		<?php parent::echoStaffSelect("sl_staff_cd",$this->staff_datas
				,$this->_is_noPreference(),false
				,$this->config_datas['SALON_CONFIG_FRONT_ITEMS']['staff']['is_display']); ?>
		<?php
		if ($this->config_datas['SALON_CONFIG_DISPLAY_COLUMN'] == 2 ) {
			parent::echoItemInputCheckTable($this->item_datas
					,false
					,$this->config_datas['SALON_CONFIG_FRONT_ITEMS']['menu']['is_display']
					,$this->set_menu_type);
		}
		else {
			parent::echoItemInputCheckTableForOneColunn($this->item_datas
					,$this->config_datas['SALON_CONFIG_FRONT_ITEMS']['menu']['is_display']
					,$this->set_menu_type);
		}
		?>
		<?php parent::echoCouponSelect("sl_coupon",$this->promotion_datas); ?>
		<?php parent::echoSubMenuPc($this->config_datas['SALON_CONFIG_USE_SUBMENU'], $this->category_datas); ?>


		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['remark']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['remark']['is_display'] ) : ?>
			<div style="display:none" >
			<textarea id="sl_remark" style="display:none" ></textarea>
			</div>

		<?php else : ?>
			<textarea id="sl_remark"  ></textarea>
		<?php endif; ?>



		<?php
			if (! $this->is_no_change_booking_items
				&& isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['is_display'] )
				&& ! $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['is_display'] ) : ?>
			<span id="sl_price" style="display:none" ></span>

		<?php else : ?>
			<label >
		<?php
			 if (isset($this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['set_label']) ) {
			 	echo $this->config_datas['SALON_CONFIG_FRONT_ITEMS']['price']['set_label'];
			 }
			 else {
				_e('price',SL_DOMAIN);
			 }
		?>:
			</label>
			<span id="sl_price"></span>
		<?php endif; ?>

		<div class="spacer"></div>
		<div id="sl_booking_button_div" >
			<input type="button" value="<?php _e('Close',SL_DOMAIN); ?>" id="sl_button_close"  />
			<input type="button" value="<?php _e('Cancel Reservation',SL_DOMAIN); ?>" id="sl_button_delete"  />
			<input type="button" value="<?php _e('Create Reservation',SL_DOMAIN); ?>" id="sl_button_insert"  />
		</div>

	</div>
<?php if ($this->_is_editBooking() ) : ?>
	<div id="sl_search" class="modal">
		<div class="modalBody">
			<div id="sl_search_result"></div>
		</div>
	</div>
<?php endif; ?>
	<div id="sl_hidden_photo_area">
<?php
	foreach ($this->staff_datas as $k1 => $d1 ) {
		if (!empty($d1['photo_result'][0]) ) {
			for($i = 1;$i<count($d1['photo_result']);$i   ++  ){
				$tmp = "<a href='".$d1['photo_result'][$i]['photo_path']."' rel='staff".$d1['staff_cd']."' class='lightbox' ></a>";
				$url = site_url();
				$url = substr($url,strpos($url,':')+1);
				if (is_ssl() ) {
					$tmp = preg_replace("$([hH][tT][tT][pP]:".$url.")$","https:".$url,$tmp);
				}
				else {
					$tmp = preg_replace("$([hH][tT][tT][pP][sS]:".$url.")$","http:".$url,$tmp);
				}
				echo $tmp;
			}
		}
	}



?>
	<?php //<!-- sl_hidden_photo_area --> ?>
    </div>
     <?php //<!-- customer_booking_form  --> ?>
	</div>
	 <?php //<!-- sl_content --> ?>
	</div>
