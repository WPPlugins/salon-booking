<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Working_Page extends Salon_Page {



	private $all_branch_datas = null;
	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;

	private $current_user_branch_cd = '';

	private $first_hour = '';
	private $last_hour = '';
	private $close_48 = '';

	private $reseration_cd = '';


	private $target_year = '';




	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->target_year = date_i18n("Y");
	}

	public function set_all_branch_datas ($branch_datas) {
		$this->all_branch_datas = $branch_datas;
	}


	public function set_item_datas ($item_datas) {
		$this->item_datas = $item_datas;
	}

	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}

	public function set_staff_datas ($staff_datas) {
		$this->staff_datas = $staff_datas;
	}

	public function get_set_branch_cd () {
		if (empty($_POST['set_branch_cd']) ) return '';
		else return $_POST['set_branch_cd'];
	}




	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
		$this->first_hour = substr($this->branch_datas['open_time'],0,2);
		$this->last_hour = substr($this->branch_datas['close_time'],0,2);
		if (intval(substr($this->branch_datas['close_time'],2,2)) > 0 ) $this->last_hour++;
		$this->close_48 = +$this->branch_datas['close_time'];
	}



	public function set_is_user_login($is_user_login) {
		$this->is_user_login = ($is_user_login == Salon_Config::USER_LOGIN_OK);
	}

	public function show_page() {
?>



	<link rel="stylesheet" href="<?php echo SL_PLUGIN_URL.SALON_CSS_DIR; ?>dhtmlxscheduler.css" type="text/css" charset="utf-8">

	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;

		var target;
		var staff_cd;
		var staff_name;
		var key_in_time;
		var save_in_time;
		var save_out_time;
		var paste_error = false;

<?php 		//24時間超えの場合
			if ( $this->close_48 > 2400 ) {
				echo 'var target_yyyymmdd;';
			}
?>
		scheduler.config.multi_day = true;
		scheduler.config.all_timed = true;

		scheduler.config.prevent_cache = true;
		scheduler.config.first_hour= <?php echo $this->first_hour; ?>;
		scheduler.config.last_hour= <?php echo $this->last_hour; ?>;
		scheduler.config.last_hour_48 = <?php echo +$this->close_48; ?>;
		scheduler.config.time_step = <?php echo $this->branch_datas['time_step']; ?>;
		scheduler.config.event_duration = 60;
		scheduler.config.auto_end_date = true;
		scheduler.config.xml_date= "%Y-%m-%d %H:%i";
		scheduler.config.details_on_create=true;
		scheduler.config.details_on_dblclick=true;
		scheduler.config.mark_now = false;
		scheduler.config.check_limits = false;
<?php //小さいメニューバーを出さない ?>
		scheduler.xy.menu_width = 0;
<?php 		//24時間超えの場合
			$over24 = false;
			if ( $this->close_48 > 2400 ) {
				echo 'scheduler.is_one_day_event = function(ev) {return true;};';
				$over24 = true;
			}
			$this->echo_customize_dhtmlx($over24);
?>





<?php //locale_jaを使用しないように
		parent::echoLocaleDef();
?>

		$j(document).ready(function() {
<?php
			if ($this->isSalonAdmin() ) {
				$tmp_dir = SL_PLUGIN_SRC_URL;
				$tmp_action = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
				echo <<<EOT
				\$j("#branch_cd").change(function(){
					\$j("#sl_submit").html('<form id="sl_form" method="post" action="{$tmp_action}" ><input name="set_branch_cd" id="set_branch_cd" type="hidden"/></form>');
					\$j("#set_branch_cd").val(\$j("#branch_cd").val());
					\$j("#sl_form").submit();
				});
EOT;
			}
?>
<?php 	//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
		//ここからdatatables
		//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
?>
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slworking",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('staff_name','remark'),false,$this->is_multi_branch); ?>

				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Working_Init" } );
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("first_name",false); ?>
					element.empty();
					element.append(sel_box);
				}

			});
<?php 	//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
		//ここまでdatatables
		//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
?>


<?php //休業日
			parent::echoSetHoliday($this->branch_datas,$this->target_year,false);
?>
			scheduler.init('scheduler_here',new Date("<?php echo date_i18n('Y/m/d'); ?>"),"week");
			scheduler.templates.event_text=function(start,end,event){
				return "<b>"+event.text+"</b>";
			}

			var dp = new dataProcessor("<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slworking&menu_func=Working_Edit");
			dp.init(scheduler);
			dp.defineAction("error",function(response){
				if (response.getAttribute('sid') )		scheduler.deleteEvent(response.getAttribute('sid'));
				alert(response.getAttribute('message'));


				return true;
			})
			dp.setTransactionMode("POST",false);
			dp.attachEvent("onBeforeUpdate",function(id,status, data){
				data.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return true;
			})
			dp.attachEvent("onAfterUpdate",function(sid,action,tid,xml_node){
				if (action != 'error' && action != 'deleted') {
					if ( xml_node.getAttribute('type') == "inserted" ) {
						scheduler._events[tid].type = '';
					}
					scheduler._events[tid].edit_flg = xml_node.getAttribute('edit_flg');
				}
				return true;
			})


			scheduler.attachEvent("onBeforeEventChanged", function(ev, native_event, is_new,drag_event){
				ev.nonce = "<?php echo $this->nonce; ?>";
				return true;
			});

			scheduler.attachEvent("onEventCopied", function(ev) {
				ev.nonce = "<?php echo $this->nonce; ?>";
				alert("<?php _e('copy[',SL_DOMAIN); ?>"+ev.start_date.getHours()+":"+(ev.start_date.getMinutes()<10?'0':'')+ev.start_date.getMinutes() + " - " + ev.end_date.getHours()+":"+(ev.end_date.getMinutes()<10?'0':'')+ev.end_date.getMinutes()+ "]" );
			});
			scheduler.attachEvent("onEventCut", function(ev) {
				scheduler._buffer_id = '';	//ctrl+xはさせない
				ev.nonce = "<?php echo $this->nonce; ?>";
			});

			scheduler.attachEvent("onEventPasted", function(isCopy, modified_ev, original_ev) {
//				scheduler.validId=function(id){ return true;};
				scheduler._loading=false;
				if ( isCopy ) {
					if (paste_error ) {
						delete scheduler._events[modified_ev.id];
						scheduler.unselect(modified_ev.id)
						scheduler.event_updated(modified_ev)
					}
				}
				else {
					scheduler.updateEvent(modified_ev.id);
				}
				paste_error = false;
//				var evs = scheduler.getEvents(modified_ev.start_date, modified_ev.end_date);
//				if (evs.length > 1) {
//					scheduler.deleteEvent(modified_ev.id);
//				}
			});

			function allow_own(id){
				var ev = this.getEvent(id);
				ev.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return ev.edit_flg == 1;
			}
			scheduler.attachEvent("onClick",allow_own);
			scheduler.attachEvent("onDblClick",allow_own);

			scheduler.attachEvent("onEventCreated",function(id){
					var ev = this.getEvent(id);
					ev.type = "new";
					ev.status = "";
					ev.remark = "";
					ev.working_cds = "";
			});
			scheduler.attachEvent("onBeforeCreate",function(ev){
					return ( (new Date() ) < ev.start_date );
			});
			scheduler.attachEvent("onEventCollision",function(ev,evs) {
				paste_error =false;
				if (scheduler._drag_mode == "move" )return true;
				else {		//pasteの重複
					paste_error =true;
					//[TODO]これをしないと、collision.js内のonEventAddのdeleteeventで
					//postしてしまう。ちょっと汚いので今後の検討
//					scheduler.validId=function(id){ return false;};
					scheduler._loading=true;

					return true;
				}
			});

			scheduler.attachEvent("onBeforeEventDelete",function(id,ev) {
				if (paste_error ) return false;
				return true;
//					delete scheduler._events[ev.id];
//					scheduler.unselect(ev.id)
//					scheduler.event_updated(ev)
//					return false;
//				}
//				return true;
			});

<?php //new */?>
			scheduler.attachEvent("onBeforeDrag", function (event_id, mode, native_event_object){
				if (mode != "create" ) {
					var ev = this.getEvent(event_id);
					ev.key_in_time = ev.start_date;
				}

				return true;
			});


			$j("#button_insert").click(function(){
				save_form();
			});

			$j("#button_close").click(function(){
				close_form();
			});

			$j("#button_delete").click(function(){
				delete_working_data();
			});


//{TODO]
			$j("#in_time").change(function(){
				save_in_time = $j("#in_time").val();
			});
			$j("#out_time").change(function(){
				save_out_time = $j("#out_time").val();
			});

			$j("#working_cds input[type=checkbox]").click(function(){
				_setWorkingCds();

			});




			<?php parent::echoSetItemLabel(false); ?>
			<?php parent::echo_clear_error(); ?>

			$j("#detail_out span").addClass("sl_detail_out");
			$j("#detail_out label").addClass("sl_detail_out");
			$j("#booking_button_div input").addClass("sl_button");
			$j("#working_input_form").hide();
			$j("#working_input_form").prependTo("body");
			<?php
			if ($this->is_multi_branch && $this->isSalonAdmin() ) echo '$j("#branch_cd").val('.$this->current_user_branch_cd.');';
			?>


			$j("#scheduler_here").hide();
<?php //new to */?>
		});


		function fnDetailInit( ev ) {
			if (ev.type) {
				$j("#button_insert").val("<?php _e('Add',SL_DOMAIN); ?>");
				$j("#button_delete").hide();
			}
			else  {
				$j("#button_insert").val("<?php _e('Update',SL_DOMAIN); ?>");
				$j("#button_delete").show();
			}
			$j("#name").text( staff_name );
//			$j("#target_day").text(ev.start_date.getFullYear()+"/"+ ('00' + (ev.start_date.getMonth() + 1)).slice(-2) +"/"+('00' + (ev.end_date.getDate())).slice(-2) );
			$j("#target_day").text(fnDayFormat(ev.start_date,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
<?php 		//24時間超えの場合
			if ( $this->close_48 >= 2400 ) {
				//設定された時間で今日か明日かを判定する
				echo <<<EOT3
					target_yyyymmdd = new Date(ev.start_date.getTime());
					if (ev.start_date.getHours() < {$this->first_hour} )
						target_yyyymmdd.setDate(target_yyyymmdd.getDate()-1);
EOT3;
			}
?>
			save_in_time = ('00' + (ev.start_date.getHours())).slice(-2)+":"+('00' + (ev.start_date.getMinutes())).slice(-2) ;
			key_in_time = new Date(ev.start_date);
			key_out_time = new Date(ev.end_date);
			save_out_time = ('00' + (ev.end_date.getHours())).slice(-2)+":"+('00' + (ev.end_date.getMinutes())).slice(-2) ;
			$j("#in_time").val(save_in_time );
			$j("#out_time").val( save_out_time );
			$j("#remark").val( htmlspecialchars_decode(ev.remark) );
			$j("#working_cds input[type=checkbox]").attr("checked",false);
			$j("#working_cds input[type=checkbox]").attr("disabled",false);
			var working_array = ev.working_cds.split(",");
			var max_loop = working_array.length;
			for	 (var i = 0 ; i<max_loop; i++) {
				$j("#working_cds #check_"+working_array[i]).attr("checked",true);
			}
			_setWorkingCds();
			$j("#name").focus();
			$j("#check_1").prop("checked",true);
			<?php parent::echo_clear_error(); ?>
		}

		function _setWorkingCds() {

			if ($j("#check_<?php echo Salon_Working::DAY_OFF; ?>").is(":checked") ) {
				$j("#check_<?php echo Salon_Working::EARLY_OUT; ?>").attr("disabled",true);
				$j("#check_<?php echo Salon_Working::LATE_IN; ?>").attr("disabled",true);
				$j("#check_<?php echo Salon_Working::HOLIDAY_WORK; ?>").attr("disabled",true);
				$j("#check_<?php echo Salon_Working::USUALLY; ?>").attr("disabled",true);

				$j("#check_<?php echo Salon_Working::HOLIDAY_WORK; ?>").attr("checked",false);
				$j("#check_<?php echo Salon_Working::USUALLY; ?>").attr("checked",false);


				$j("#in_time").attr("disabled",true);
				$j("#in_time").val("<?php echo substr($this->branch_datas['open_time'],0,2).":".substr($this->branch_datas['open_time'],2,2); ?>");
				$j("#out_time").attr("disabled",true);
<?php 		//24時間超えの場合
			if ( $this->close_48 >= 2400 ) {
				$hh = +substr($this->branch_datas['close_time'],0,2) - 24;

				echo '$j("#out_time").val("'.sprintf("%02d",$hh).":".substr($this->branch_datas['close_time'],2,2).'");';
			}
			else {
				echo '$j("#out_time").val("'.substr($this->branch_datas['close_time'],0,2).":".substr($this->branch_datas['close_time'],2,2).'");';
			}

?>
				return;
			}
			else {
				$j("#check_<?php echo Salon_Working::USUALLY; ?>").attr("disabled",false);
				$j("#check_<?php echo Salon_Working::EARLY_OUT; ?>").attr("disabled",false);
				$j("#check_<?php echo Salon_Working::LATE_IN; ?>").attr("disabled",false);
				$j("#check_<?php echo Salon_Working::HOLIDAY_WORK; ?>").attr("disabled",false);
				$j("#in_time").attr("disabled",false);
				$j("#in_time").val(save_in_time);
				$j("#out_time").attr("disabled",false);
				$j("#out_time").val(save_out_time);
			}
			if ($j("#check_<?php echo Salon_Working::USUALLY; ?>").is(":checked") ||
					$j("#check_<?php echo Salon_Working::HOLIDAY_WORK; ?>").is(":checked") ||
					$j("#check_<?php echo Salon_Working::EARLY_OUT; ?>").is(":checked") ||
					$j("#check_<?php echo Salon_Working::LATE_IN; ?>").is(":checked") 	){
				$j("#check_<?php echo Salon_Working::DAY_OFF; ?>").attr("disabled",true);
			}
			else {
				$j("#check_<?php echo Salon_Working::DAY_OFF; ?>").attr("disabled",false);
			}
			if ($j("#check_<?php echo Salon_Working::EARLY_OUT; ?>").is(":checked") ||
				$j("#check_<?php echo Salon_Working::LATE_IN; ?>").is(":checked") ) {
				$j("#check_<?php echo Salon_Working::USUALLY; ?>").attr("checked",true);
			}
		}

		<?php parent::echoClientItem(array('in_time','out_time','working_cds','remark')); ?>
		scheduler.showLightbox = function(id){
			$j("#working_input_form").show();
			$j("#data_detail").show();
			var ev = scheduler.getEvent(id);
			scheduler.startLightbox(id, $j("#data_detail").get(0));
			fnDetailInit(ev);
		}
		function _edit_time (target_day , set_time) {
			var split_data = set_time.split(":");
			target_day.setHours(+split_data[0]);
			target_day.setMinutes(+split_data[1]);
		}
<?php	if ( $this->close_48 > 2400 )  : ?>


		function _edit_over24 (target_day , set_time) {
			var tmp_in = set_time.split(":");
			if (tmp_in[0] < <?php echo $this->first_hour; ?>) {
				target_day.setDate(target_day.getDate()+1);
			}

			_edit_time(target_day,set_time);
		}
<?php   endif; ?>

		function save_form() {
			if ( ! checkItem("data_detail") ) return false;
			//重複のチェック
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
<?php	if ( $this->close_48 > 2400 )  : ?>
			var tmp_in_time = new Date(target_yyyymmdd);
			var tmp_out_time = new Date(target_yyyymmdd);
			_edit_over24(tmp_in_time,$j("#in_time").val());
			<?php //０：００から２４：００営業の場合で終了が００:００指定の場合。とりあえず対処 ?>
			if (+scheduler.config.first_hour == 0 && +scheduler.config.last_hour == 24 && $j("#out_time").val().substr(0,2) == "00") {
				tmp_out_time.setHours(23);
				tmp_out_time.setMinutes(59);
			}
			else {
				_edit_over24(tmp_out_time,$j("#out_time").val());
			}
<?php   else: ?>
			var tmp_in_time = new Date(ev.start_date);
			var tmp_out_time = new Date(ev.end_date);
			_edit_time(tmp_in_time,$j("#in_time").val());
			_edit_time(tmp_out_time,$j("#out_time").val());
<?php   endif; ?>

			<?php //FROM toのチェック ?>
			if (tmp_in_time >= tmp_out_time) {
				var label = $j("#in_time").prev().children().eq(1);
				label.text("<?php _e('in_time or out_time is wrong ',SL_DOMAIN).'(1)'; ?>")
				label.addClass("error small");
				return false;
			}

			var collision_limit = scheduler.config.collision_limit;
			var evs = scheduler.getEvents(tmp_in_time, tmp_out_time);
			for (var i=0; i<evs.length; i++) {
				if (evs[i].id == ev.id) {
					evs.splice(i,1);
					continue;
				}
			}
			if ( evs.length >= collision_limit ) {
				var label = $j("#in_time").prev().children().eq(1);
				label.text("<?php _e('in_time or out_time is duplicated ',SL_DOMAIN); ?>")
				label.addClass("error small");
				var label = $j("#out_time").prev().children().eq(1);
				label.text("<?php _e('in_time or out_time is duplicated ',SL_DOMAIN); ?>")
				label.addClass("error small");
				return false;
			}
<?php
/*
			_edit_time(ev.start_date,$j("#in_time").val());
			_edit_time(ev.end_date,$j("#out_time").val());
*/
?>
			ev.start_date = tmp_in_time;
			ev.end_date = tmp_out_time;

			ev.remark = $j("#remark").val();
			var tmp =  new Array();
			$j("#working_cds input[type=checkbox]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
				}
			});
			ev.working_cds = tmp.join(",");
			if ($j("#check_<?php echo Salon_Working::DAY_OFF; ?>").is(":checked") ){
				ev.color = "<?php echo Salon_Color::HOLIDAY; ?>";
				ev.text = "<?php _e('DAY_OFF',SL_DOMAIN); ?>";
			}
			else {
				ev.color = "<?php echo Salon_Color::USUALLY; ?>";
				ev.text = "";
			}
			ev.nonce = "<?php echo $this->nonce; ?>";



			ev.staff_cd = staff_cd;
			ev.key_in_time = new Date(key_in_time);
			scheduler.endLightbox(true, $j("#data_detail").get(0));
			$j("#working_input_form").hide();
		}

		function delete_working_data() {
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
			ev.nonce = "<?php echo $this->nonce; ?>";
			ev.key_in_time = new Date(key_in_time);
			scheduler.deleteEvent(ev.id);
			scheduler.endLightbox(false, $j("#data_detail").get(0));
			$j("#working_input_form").hide();
		}



		function close_form(argument) {
			scheduler.endLightbox(false, $j("#data_detail").get(0));
			$j("#working_input_form").hide();
		}

		<?php parent::echoCheckClinet(array('chk_required','lenmax','reqCheck','chkSpace')); ?>


		function fnSelectRow(target_col) {
			scheduler.clearAll();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();


			staff_cd = setData['aoData'][position[0]]['_aData']['staff_cd'];
			staff_name = htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']);

			scheduler.load("<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slworking&menu_func=Working_Get_Data&branch_cd=<?php echo $this->branch_datas['branch_cd']; ?>&staff_cd="+staff_cd,fnLoadResult);

			$j("#scheduler_here").show();


		}

		function fnLoadResult() {

			if (scheduler.getUserData("","result") == 'error') {
				alert(scheduler.getUserData("","message"));
			}
		}


		dhtmlxError.catchError("LoadXML",function(a,b,data){
			//data[0] - request object
			//data[0].responseText - incorrect server side response
			alert("here2");
		});

		<?php parent::echoDayFormat(); ?>

	</script>

	<style>
<?php if (Salon_Color::PC_BACK != strtoupper($this->config_datas['SALON_CONFIG_PC_BACK_COLOR']) ) : ?>
.dhx_cal_container
,.dhx_cal_tab.active
,.dhx_scale_bar
,.dhx_scale_hour
{
background-color:<?php echo $this->config_datas['SALON_CONFIG_PC_BACK_COLOR'];?>;
}
.dhx_cal_prev_button {
	background-image:initial;
	border:1px dotted;
	text-align:center;
}
.dhx_cal_prev_button:after {
	content: "<";
}
.dhx_cal_next_button {
	background-image:initial;
	border:1px dotted;
	text-align:center;
	left:37px;
}
.dhx_cal_next_button:after {
	content: ">";
}
.dhx_cal_today_button {
	background-image:initial;
	border:1px dotted;
	left:73px;
}

.dhx_cal_navline .dhx_cal_date {
	left:126px;
}

	<?php endif;?>
.dhx_scale_cell_plus
{
background-color:#ffffff;
}
	</style>
	<h2 id="sl_admin_title"><?php _e('Time Card',SL_DOMAIN); ?>
	<?php
			if ( $this->is_multi_branch ) {	//for only_branch
				if ($this->isSalonAdmin() ) {
					echo '(<select id="branch_cd">';
					foreach($this->all_branch_datas as $k1 => $d1 ) {
						echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
					}
					echo '</select>)';
				}
				else {
					echo $this->branch_datas['name'];
				}
			}
	?>
	</h2>
	<?php echo parent::echoShortcode(); ?>


	<div id="scheduler_here" class="dhx_cal_container" >
		<div class="dhx_cal_navline">
			<div class="dhx_cal_prev_button">&nbsp;</div>
			<div class="dhx_cal_next_button">&nbsp;</div>
			<div class="dhx_cal_today_button"></div>
			<div class="dhx_cal_date"></div>
			<div class="dhx_cal_tab" name="week_tab" style="right:84px;"></div>
			<div class="dhx_cal_tab" name="month_tab" style="right:20px;"></div>
		</div>
		<div class="dhx_cal_header"></div>
		<div class="dhx_cal_data"></div>
	</div>

	<div id="working_input_form" class="salon_form">
	<div id="data_detail" >
		<div id="detail_out">
			<label  ><?php _e('Name',SL_DOMAIN); ?>:</label>
			<span id="name" ></span>
			<label  ><?php _e('Date',SL_DOMAIN); ?>:</label>
			<span id="target_day" ></span>
			<label  >&nbsp;</label>	<span>&nbsp;</span>
		</div>

		<?php parent::echoWorkingCheck(); ?>
<?php 		//24時間超えの場合
			if ( $this->close_48 > 2400 )  {
				parent::echoTimeSelect("in_time",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step']);
				parent::echoTimeSelect("out_time",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step'],false,"",true);
			}
            else {
				parent::echoTimeSelect("in_time",'0000','2359',$this->branch_datas['time_step']);
                parent::echoTimeSelect("out_time",'0000','2359',$this->branch_datas['time_step']);
            }
?>
		<textarea id="remark"  ></textarea>
		<div class="spacer"></div>
		<div id="booking_button_div" >
			<input type="button" value="<?php _e('Close',SL_DOMAIN); ?>" id="button_close"  >
			<input type="button" value="<?php _e('Delete',SL_DOMAIN); ?>" id="button_delete"  >
			<input type="button" value="<?php _e('Add',SL_DOMAIN); ?>" id="button_insert"  >
		</div>
	</div>
	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>

<?php
	if ($this->isSalonAdmin() ) echo '<div id="sl_submit" ></div>';
	}	//show_page
}		//class

