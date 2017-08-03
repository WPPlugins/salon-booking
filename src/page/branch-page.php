<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Branch_Page extends Salon_Page {

	private $branch_column = 3;

	private $set_items = null;


	private $branch_datas = null;
	private $position_datas = null;
	private $setting_patern_datas = null;



	public function __construct($use_session) {
		parent::__construct(true,$use_session);
		$this->set_items = array('branch_name','zip','address','branch_tel','mail','open_time','close_time','time_step','closed_day_check','remark','duplicate_cnt','is_setting_patern','setting_patern_cd','original_name');

	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_setting_patern_datas($datas) {
		$this->setting_patern_datas = $datas;
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_closed = "";
		var save_closed_detail = "";

		var save_result_select_id = "";

		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php Salon_Country::echoZipTable(); //for only_branch?>

		$j(document).ready(function() {
			<?php parent::echoClosedDetail($this->branch_datas[0]['closed'],"closed_day"); ?>


			<?php parent::echoSetItemLabel(); ?>
			<?php Salon_Country::echoZipFunc("zip","address");	?>
			<?php //parent::echoCommonButton();			//共通ボタン	?>

			fnDetailInit();

			$j("#salon_button_div input").addClass("sl_button");
			$j("#button_insert").click(function(){
				if ($j("#data_detail").is(":hidden")) {
					$j("#data_detail").show();
					return;
				}
				fnClickAddRow("inserted");
			});
			$j("#button_update").click(function(){
				fnClickAddRow("updated");
			});
			$j("#button_clear").click(function(){
				fnDetailInit(true);
				$j(target.fnSettings().aoData).each(function (){
					$j(this.nTr).removeClass("row_selected");
				});
			});
			$j("#button_detail").click(function(){
				$j("#data_detail").toggle();
				$j("#shortcode_wrap").toggle();
				if ($j("#data_detail").is(":visible") ) $j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);?>")
				else $j("#button_detail").val("<?php _e('show detail',SL_DOMAIN); ?>");
			});

<?php
/*
			$j("#sl_setting_patern_cd").change(function(){
				save_result_select_id = "";
				$j("#sl_original_result").children().remove();

				if ($j(this).val() == <?php echo Salon_Config::SETTING_PATERN_TIME; ?> ) {
					$j("#sl_setting_data_wrap").hide();
					$j("#sl_original_name").val("");
					$j("#sl_original_from").val("");
					$j("#sl_original_to").val("");
				}
				else {
					$j("#sl_setting_data_wrap").show();
				}
			});

			$j("#sl_is_setting_patern").click(function(){
				$j("#sl_setting_data_wrap").hide();
				$j("#sl_setting_patern_cd_lbl").hide();
				$j("#sl_setting_patern_cd").hide();
				if ($j("#sl_is_setting_patern").prop("checked") ) {
					$j("#sl_setting_patern_cd_lbl").show();
					$j("#sl_setting_patern_cd").show();
					if ($j("#sl_setting_patern_cd").val() == <?php echo Salon_Config::SETTING_PATERN_ORIGINAL;?> ) {
						$j("#sl_setting_data_wrap").show();
					}
				}
			});
			$j("#sl_original_add").click(function(){
				if ($j("#sl_original_name").val() && $j("#sl_original_from").val() && $j("#sl_original_to").val() ) {
					var from = +$j("#sl_original_from").val().replace(":","");
					var to = +$j("#sl_original_to").val().replace(":","");
					if (from < to )  {

						var last = $j("#sl_original_result div:last-child");
						var id = 1;
						if (last[0]) {
							var tmp_id = last.attr("id").split("sl_res_each_");
							id = +tmp_id[1]+1;
						}
						_fnEditSetting(id,"add");
					}
				}

			});
			$j("#sl_original_upd").click(function(){
				if (save_result_select_id == "") return;
				_fnEditSetting(save_result_select_id,"upd");

			});
*/
?>



			<?php parent::echoClosedDetail('',"closed_day"); ?>

			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbranch",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('branch_name','remark'),false,true); //for only_branch?>



				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Branch_Init" } )
				},



				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("branch"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("name"); ?>

				}
			});
			<?php parent::echo_clear_error(); ?>
			$j("#data_detail").hide();
			$j("#shortcode_wrap").hide();
			$j("#button_detail").val("<?php _e('show detail',SL_DOMAIN); ?>");
			$j("#sl_setting_patern_cd").hide();


		});

		function fnSelectRow(target_col) {
			$j("#data_detail").show();
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			save_k1 = position[0];
			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));
			$j("#zip").val(setData['aoData'][position[0]]['_aData']['zip']);
			$j("#address").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['address']));
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['mail']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));
			$j("#open_time").val(setData['aoData'][position[0]]['_aData']['open_time']).trigger("change");
			$j("#close_time").val(setData['aoData'][position[0]]['_aData']['close_time']).trigger("change");
			$j("#time_step").val(setData['aoData'][position[0]]['_aData']['time_step']);
			$j("#duplicate_cnt").val(setData['aoData'][position[0]]['_aData']['duplicate_cnt']);
			//
			save_closed = setData['aoData'][position[0]]['_aData']['closed'];
			var tmp = setData['aoData'][position[0]]['_aData']['closed'].split(",");
			$j(".sl_holiday_detail_wrap").hide();
			$j("#closed_day_check input").prop("checked",false);
			<?php //[2014/10/01]半休対応 ?>
			save_closed_detail = setData['aoData'][position[0]]['_aData']['memo'];
			if (save_closed_detail == "MEMO" ) save_closed_detail = "";
			var tmp_detail = save_closed_detail.split(";");
			for (var i=0; i < tmp.length; i++) {
				$j("#closed_day_"+tmp[i]).attr("checked",true);
				var tmp_time_array = Array();
				if (tmp_detail[i]) {
					tmp_time_array = tmp_detail[i].split(",");
				}
				else {
					tmp_time_array[0] = "<?php echo $this->branch_datas[0]['open_time']; ?>";
					tmp_time_array[1] = "<?php echo $this->branch_datas[0]['close_time']; ?>";
				}
				$j("#closed_day_"+tmp[i]+"_fr").val(tmp_time_array[0].slice(0,2)+":"+tmp_time_array[0].slice(-2));
				$j("#closed_day_"+tmp[i]+"_to").val(tmp_time_array[1].slice(0,2)+":"+tmp_time_array[1].slice(-2));
				$j("#sl_holiday_detail_wrap_"+tmp[i]).show();

			}


			$j("#display_shortcode").val(setData['aoData'][position[0]]['_aData']['shortcode']);

			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();
			$j("#data_detail").show();
			$j("#shortcode_wrap").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");


			$j("#sl_setting_data_wrap").hide();
			$j("#sl_setting_patern_cd_lbl").hide();
			$j("#sl_setting_patern_cd").hide();

			$j("#sl_is_setting_patern").prop("checked",false);

			if (setData['aoData'][position[0]]['_aData']['notes']) {
				$j("#sl_is_setting_patern").prop("checked",true);
<?php
/*
				var setting_array = setData['aoData'][position[0]]['_aData']['notes'].split(";");
				$j("#sl_setting_patern_cd").val(setting_array[0]).change();
				$j("#sl_setting_patern_cd_lbl").show();
				$j("#sl_setting_patern_cd").show();

				if (setting_array[0] == <?php echo Salon_Config::SETTING_PATERN_ORIGINAL;?> ) {
					for(var i = 1 , max_loop = setting_array.length; i<max_loop;i++){
						var setting_item_array = setting_array[i].split(",");
						$j("#sl_original_name").val(setting_item_array[0]);
						$j("#sl_original_from").val(setting_item_array[1]);
						$j("#sl_original_to").val(setting_item_array[2]);
						_fnEditSetting(i+1,"add");
					}
				}
				$j("#sl_original_name").val("");
				$j("#sl_original_from").val("");
				$j("#sl_original_to").val("");
*/
?>
			}
		}
		<?php parent::echoDataTableEditColumn("branch"); ?>
		<?php parent::echoDataTableDeleteRow("branch"); ?>
		<?php parent::echoTime25Check(); ?>

		function fnClickAddRow(operate) {
			var except_item = "sl_original_name,sl_original_from,sl_original_to,sl_setting_patern_cd"
			if ( ! checkItem("data_detail",except_item) ) return false;
			var op = $j("#open_time").val();
			if (!_fnCheckTimeStep(+$j("#time_step").val(),op.slice(-2) ) ) return false;
			var cl = $j("#close_time").val();
			if (!_fnCheckTimeStep(+$j("#time_step").val(),cl.slice(-2) ) ) return false;

			<?php //半休対応　?>
			if (!_fnCheckClosedDetail(+$j("#time_step").val()) ) return false;
			$j(".sl_from").triggerHandler("change");

			if (+(cl.replace(":","")) - +(op.replace(":","")) > 2400) {
				alert("<?php _e("within 24 hours",SL_DOMAIN); ?>");
				$j("#close_time").focus()
				return false;
			}

			var item_cd = "";
			var branch_cd = "";
			if ( save_k1 !== ""  ) {
				var setData = target.fnSettings();
				branch_cd = setData['aoData'][save_k1]['_aData']['branch_cd'];
			}
			var setting_data = "";
			if ($j("#sl_is_setting_patern").prop("checked") ) {
				setting_data = <?php echo Salon_Config::SETTING_PATERN_TIME; ?>+";";
<?php
/*
				if ($j("#sl_setting_patern_cd").val() == <?php echo Salon_Config::SETTING_PATERN_ORIGINAL; ?> ) {
					var setting_data_array = Array();
					$j("#sl_original_result").children().each(function (){
						var tmp_id = $j(this).attr("id").split("sl_res_each_");
						setting_data_array.push($j("#sl_res_each_name_"+tmp_id[1]).val()+','+$j("#sl_res_each_from_"+tmp_id[1]).val()+','+$j("#sl_res_each_to_"+tmp_id[1]).val());
					});
					if (setting_data_array.length == 0 ) {
						alert("<?php _e('Original select data is empty',SL_DOMAIN); ?>");
						return;
					}
					setting_data = <?php echo Salon_Config::SETTING_PATERN_ORIGINAL; ?> +";"+ setting_data_array.join(";");
				}
				else {
					setting_data = <?php echo Salon_Config::SETTING_PATERN_TIME; ?>+";";
				}
*/
?>
			}



			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbranch",
					dataType : "json",

					data: {
						"branch_cd":branch_cd,
						"no":save_k1,
						"type":operate,
						"name":$j("#name").val(),
						"position_cd":$j("#position_cd").val(),
						"zip":$j("#zip").val(),
						"address":$j("#address").val(),
						"tel":$j("#tel").val(),
						"mail":$j("#mail").val(),
						"open_time":$j("#open_time").val(),
						"close_time":$j("#close_time").val(),
						"time_step":$j("#time_step").val(),
						"closed":save_closed,
						"remark":$j("#remark").val(),
						"memo":save_closed_detail,
						"notes":setting_data,
						"menu_func":"Branch_Edit",
						"nonce":"<?php echo $this->nonce; ?>",
						"duplicate_cnt":$j("#duplicate_cnt").val()

					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if (operate =="inserted" ) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}
							fnDetailInit();
							$j(target.fnSettings().aoData).each(function (){
								$j(this.nTr).removeClass("row_selected");
							});

						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}


		function fnDetailInit() {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#closed_day_check input").prop("checked",false);
			$j("#data_detail textarea").val("");
			$j("#button_update").attr("disabled", "disabled");
			$j("#display_shortcode").val("");


			$j("#duplicate_cnt").val("0");

			save_k1 = "";

			save_result_select_id = "";
			$j("#sl_is_setting_patern").prop("checked",false)
			$j("#sl_original_result").children().remove();
			$j("#sl_setting_data_wrap").hide();
			$j("#sl_setting_patern_cd_lbl").hide();
			$j("#sl_setting_patern_cd").hide();

			<?php parent::echo_clear_error(); ?>

		}

		function _fnEditSetting(id,func) {
			var set_data = $j("#sl_original_name").val() + ":" + $j("#sl_original_from").val() + "-" + 	$j("#sl_original_to").val();

			var setcn = '<div id="sl_res_each_'+id+'" ><span class="sl_in_span">'+set_data+'</span><input type="button" class="sl_in_button sl_button sl_button_short sl_short_width_no_margin" value="<?php _e('Select',SL_DOMAIN); ?>" id="sl_res_each_sel_'+id+'"/><input  type="button"  class="sl_in_button sl_button sl_button_short sl_short_width_no_margin" value="<?php _e('Delete ',SL_DOMAIN); ?>"id="sl_res_each_del_'+id+'"/><input type="hidden" id="sl_res_each_name_'+id+'" value="'+$j("#sl_original_name").val()+'" /><input type="hidden" id="sl_res_each_from_'+id+'" value="'+$j("#sl_original_from").val()+'" /><input type="hidden" id="sl_res_each_to_'+id+'" value="'+$j("#sl_original_to").val()+'" /></div>';
			if (func == "add" )
				$j("#sl_original_result").append(setcn);
			else
				$j("#sl_res_each_"+save_result_select_id).replaceWith(setcn);

			$j("#sl_res_each_sel_"+id).click(function () {
				$j("#sl_original_name").val($j("#sl_res_each_name_"+id).val());
				$j("#sl_original_from").val($j("#sl_res_each_from_"+id).val());
				$j("#sl_original_to").val($j("#sl_res_each_to_"+id).val());
				save_result_select_id = id;
			});
			$j("#sl_res_each_del_"+id).click(function () {
				$j(this).parent().remove();
				save_result_select_id = "";

			});
			$j("#sl_original_name").val("");
			$j("#sl_original_from").val("");
			$j("#sl_original_to").val("");
			save_result_select_id = "";
		}

	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','range','reqCheck','num')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>
	<?php parent::echoClosedDetailCheck(); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Shop Information',SL_DOMAIN); ?></h2>

	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>

	<div id="shortcode_wrap"><h3><?php _e('Please copy and paste this tag to insert to the page',SL_DOMAIN); ?><input id="display_shortcode" /></h3></div>
	<?php echo parent::echoShortcode(); ?>
	<div id="data_detail" >
		<input type="text" id="name" />
		<input type="text" id="zip"/>
		<textarea id="address" ></textarea>
		<input type="text" id="tel"/>
		<input type="text" id="mail"/>
		<input type="text" id= "duplicate_cnt"  />
		<input type="text" id="open_time"/>
		<input type="text" id="close_time"/>
		<?php parent::echoTimeStepSelect('time_step'); ?>
		<?php parent::echoClosedCheck('','closed_day'); ?>
		<textarea id="remark"  ></textarea>
		<input type="checkbox" id="sl_is_setting_patern" >
		<?php echo parent::echoSettingPaternSelect("sl_setting_patern_cd",$this->setting_patern_datas); ?>
		<div id="sl_setting_data_wrap" >
			<input type="text" id="sl_original_name"/>
			<label id="sl_original_time_lbl" for="original_from" ><?php _e('Original time',SL_DOMAIN); ?>:</label>
			<?php parent::echoOpenCloseTime("sl_original_from",1000,1800,15,'sl_middle_width_no_margin');?>
			<?php parent::echoOpenCloseTime("sl_original_to",1000,1800,15,'sl_middle_width_no_margin');?>
			<input type="button" id="sl_original_add" value="<?php _e('Add',SL_DOMAIN); ?>" class="sl_button sl_button_short sl_short_width_no_margin" >

			<input type="button" id="sl_original_upd" value="<?php _e('Update',SL_DOMAIN); ?>" class="sl_button sl_button_short sl_short_width_no_margin " >

			<div id="sl_original_result" ></div>
		</div>


		<div class="spacer"></div>
		<div id="uploadedImageView"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class

