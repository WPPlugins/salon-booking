<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Sales_Page extends Salon_Page {

	private $set_items = null;

	private $all_branch_datas = null;
	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;

	private $current_user_branch_cd = '';

	private $promotion_data = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array('reserved_mail','reserved_tel','customer_name','target_day','staff_cd','item_cds','remark','price','regist_customer','coupon');
	}

	public function set_all_branch_datas ($branch_datas) {
		$this->all_branch_datas = $branch_datas;
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
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

	public function set_promotion_datas ($promotion_datas) {
		$this->promotion_datas = $promotion_datas;

	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_item_cds_aft = "";
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";

		var save_operate = "inserted";

		var staff_items = new Array();


		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php parent::set_datepicker_date($this->current_user_branch_cd,null ,unserialize($this->branch_datas['sp_dates'])); ?>


		<?php parent::echoItemFromto($this->item_datas); ?>
		<?php parent::echoPromotionArray($this->promotion_datas); ?>

		$j(document).ready(function() {

			<?php parent::echoSetItemLabel(); ?>
			<?php parent::echoSearchCustomer(); //検索画面 ?>
			<?php parent::echoDownloadEvent("sales") //ダウンロード画面 From ?>


			<?php  parent::set_datepickerDefault(true); ?>
			<?php  parent::set_datepicker("target_day",true,$this->branch_datas['closed']); ?>

			<?php //[2012/08/02]
			foreach ($this->staff_datas as $k1 => $d1 ) {
				echo 'staff_items['.$d1['staff_cd'].'] = "'.$d1['in_items'].'";';
			}
			?>


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

			$j("#reserved span").addClass("sl_detail_out");
			$j("#reserved label").addClass("sl_detail_out");


			<?php parent::echoCommonButton('save_operate');  ?>


			$j("#item_cds input[type=checkbox]").click(function(){
				_fnCalcPrice();
			});
			$j("#button_redisplay").click(function() {
				target.fnClearTable();					//テーブルデータクリア
				target.fnReloadAjax();				   //再読み込み
				target.fnPageChange( 'first' );		//ページングの最初へ移動
			});


			$j("#check_noreserved").click(function(){
				if ($j("#check_noreserved").is(":checked") ) {
					$j("#reserved").hide();
					$j("#no_reserved").show();
					$j("#data_detail").show();
					save_operate = "inserted_reserve";
					$j("#button_insert").attr("disabled",false);
				}
				else {
					$j("#reserved").show();
					$j("#no_reserved").hide();
					save_operate = "inserted";
					$j("#button_insert").attr("disabled",true);
				}
				fnDetailInit(false);
			});

			<?php //[2014/08/02]スタッフコードにより選択を変更 ?>
			$j("#staff_cd").change(function(){

//				var checkday = $j("#target_day").val();
//				checkday = checkday.replace(/\//g,"");
				var dt = _fnDateConvert($j("#target_day").val() );
				var checkday = dt.getFullYear() + ("0"+(dt.getMonth()+1)).slice(-2)+("0"+dt.getDate()).slice(-2);
				$j("#item_cds input").attr("disabled",true);
				if (checkday && $j(this).val()  ) {
					var staff_cd = $j(this).val();
					var item_array = staff_items[staff_cd].split(",");
					var max_loop = item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						<?php //メニューの有効期間を判定する　?>
						if (item_fromto[+item_array[i]] && item_fromto[+item_array[i]].f <= checkday && checkday <= item_fromto[+item_array[i]].t)
							$j("#item_cds #check_"+item_array[i]).attr("disabled",false);
							$j("#item_cds #check_"+item_array[i]).addClass("sl_color_cant_treat");
					}
					//実績登録の場合は、他の画面と異なりすでにチェックの入っている場合は逆にOK入力可能にする
					//途中から扱えなくなった場合？

					$j("#item_cds :checkbox").each(function(){
						if($j(this).prop("checked") ){
							$j(this).attr("disabled",false)
						}
					})
				}
			});

			$j("#target_day").change(function() {
				$j("#staff_cd").change();
			});

			$j("#coupon").change(function () {
				_fnCalcPrice();
			});




			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slsales",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('reserved_time','customer_name','staff_name_aft','remark'),false,true,'150px'); //for only_branch?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Sales_Init" } );
				  aoData.push( { "name": "target_date_num","value":$j("#target_date_number").val() } );
				  aoData.push( { "name": "target_date_patern","value":$j("#target_date_patern").val() } );
				  aoData.push( { "name": "target_date_zengo","value":"before" } );
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
				},
<?php	//iDisplayIndexFullがデータ上のindexでidisplayIndexがページ上のindexとなる　?>
		//aDataが実際のデータで、nRowがTrオブジェクト
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php  parent::echoDataTableSelecter("name",false,__('result del',SL_DOMAIN),__('result delete?',SL_DOMAIN)); ?>
					if (aData.status == <?php echo Salon_Reservation_Status::SALES_REGISTERD; ?> ) {
//						element.append(checkbox);
						element.append(sel_box);
						element.append(del_box);
					}
					else {
						element.empty();
//						element.append(checkbox);
						element.append(sel_box);
					}
				}
			});

			$j("#button_insert").attr("disabled", true);
			$j("#check_noreserved").attr("checked",false);
			$j("#no_reserved").hide();
			$j("#target_date_patern").children("option[value=<?php echo parent::TARGET_DATE_PATERN; ?>]").attr("selected","selected");

		});

<?php //taregt_colはtdが前提 ?>
		function fnSelectRow(target_col) {
			$j("#reserved").show();
			$j("#no_reserved").hide();
			save_operate = "inserted";
			$j("#button_insert").attr("disabled",true);
			$j("#check_noreserved").attr("checked",false);

			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			save_k1 = position[0];
			$j("#reserved_name").text(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));
			$j("#target_day_bef").text(	setData['aoData'][position[0]]['_aData']['target_day']+' '+
									setData['aoData'][position[0]]['_aData']['time_from_bef']+' - '+
									setData['aoData'][position[0]]['_aData']['time_to_bef']);
<?php //[TODO]日付は原則変えないのでチェックをいれる ?>
			$j("#target_day").val(	setData['aoData'][position[0]]['_aData']['target_day']);
			$j("#staff_name_bef").text(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['staff_name_bef']));
			$j("#item_name_bef").text(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['item_name_bef']));
			$j("#remark_bef").text(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark_bef']));

			$j("#status_name").text(setData['aoData'][position[0]]['_aData']['status_name']);
			$j("#time_from_aft").val(setData['aoData'][position[0]]['_aData']['time_from_aft']);
			$j("#time_to_aft").val(setData['aoData'][position[0]]['_aData']['time_to_aft']);
			$j("#staff_cd").val(setData['aoData'][position[0]]['_aData']['staff_cd_aft']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));
			$j("#price").val(setData['aoData'][position[0]]['_aData']['price']);
<?php 	//[TODO]親子で指定ではなく、ＩＤ指定で色を変える ?>
			if (setData['aoData'][position[0]]['_aData']['status'] == <?php echo Salon_Reservation_Status::SALES_REGISTERD; ?> ) {
				$j("#input_aft *").removeClass("sl_coler_not_complete");
			}
			else {
				$j("#input_aft *").addClass("sl_coler_not_complete");
			}

			save_item_cds_aft = setData['aoData'][position[0]]['_aData']['item_cds_aft'];
			$j("#item_cds input[type=checkbox]").attr("checked",false);
			//selecterでやりたいが、うまくいかんのでIDにコードをくっつける
			for	 (var index in setData['aoData'][position[0]]['_aData']['item_cd_array_aft']) {
				$j("#item_cds #check_"+index).attr("checked",true);
			}
			if (setData['aoData'][position[0]]['_aData']['status'] == <?php echo Salon_Reservation_Status::SALES_REGISTERD; ?> ) {
				$j("#button_update").attr("disabled",false);
				$j("#button_insert").attr("disabled",true);
			}
			else {
				$j("#button_update").attr("disabled",true);
				$j("#button_insert").attr("disabled",false);
			}

			$j("#coupon_name").text(setData['aoData'][position[0]]['_aData']['coupon_name']);
			$j("#coupon").val(setData['aoData'][position[0]]['_aData']['coupon_aft']).change();

			$j("#button_clear").show();
			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");

			save_user_login = setData['aoData'][position[0]]['_aData']['user_login'];
			save_mail = setData['aoData'][position[0]]['_aData']['email'];
			save_tel = setData['aoData'][position[0]]['_aData']['tel'];
			if (save_tel === null ) save_tel ="";
			save_name = setData['aoData'][position[0]]['_aData']['name'];
<?php //予約なしの実績登録以外では不要だが判定するほどのことではないので、そのまま ?>
			$j("#name").val(save_name);
			$j("#mail").val(save_mail);
			$j("#tel").val(save_tel);

			$j("#staff_cd").change();

		}

		<?php parent::echoDataTableDeleteRow("sales","reservation",false); ?>
		<?php parent::echoDisplayErrorLable(); ?>

		function fnClickAddRow(operate) {
			var check_array ;
			if (operate == "inserted_reserve" ) check_array = "data_detail,status,time_from_aft,time_to_aft";
			else check_array = "data_detail,status,time_from_aft,time_to_aft,mail,tel,name";

			var is_normal = true;
			if ( ! checkItem("data_detail",check_array) ) is_normal = false;
			var start = 0;
			if (! $j("#time_from_aft").val()) {
				fnDisplayErrorLabel("target_day_lbl","<?php _e("please input start time",SL_DOMAIN); ?>");
				is_normal = false;
			}
			else {
				start = +$j("#time_from_aft").val().replace(":","");
			}

			if (! $j("#time_to_aft").val()  ) {
				fnDisplayErrorLabel("target_day_lbl","<?php _e("please input end time",SL_DOMAIN); ?>");
				is_normal = false;
			}
			else {
				end = +$j("#time_to_aft").val().replace(":","");
			}
			if (is_normal && start >= end ) {
				fnDisplayErrorLabel("target_day_lbl","<?php _e("end time is earlier then start time",SL_DOMAIN); ?>");
				is_normal = false;
			}
			if (!is_normal ) return false;


			var reservation_cd = "";
			var branch_cd = <?php echo $this->current_user_branch_cd; ?>;

			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				reservation_cd = setData['aoData'][save_k1]['_aData']['reservation_cd'];
				branch_cd = setData['aoData'][save_k1]['_aData']['branch_cd'];
			}
			if ( (save_mail != $j("#mail").val() ) ||
				 (save_tel != $j("#tel").val() )  ){
				save_user_login = "";
			}
			var regist_customer = null;
			if ($j("#regist_customer").prop("checked")) regist_customer = "checked";

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slsales",
					dataType : "json",
<?php //priceに関しては今後セキュリティ上考慮する ?>
					data: {
						"reservation_cd":reservation_cd,
						"no":save_k1,
						"type":operate,
						"branch_cd":branch_cd,
						"staff_cd":$j("#staff_cd").val(),
						"target_day":$j("#target_day").val(),
						"time_from":$j("#time_from_aft").val(),
						"time_to":$j("#time_to_aft").val(),
						"item_cds":save_item_cds_aft,
						"price":$j("#price").val(),
						"remark":$j("#remark").val(),
						"user_login":save_user_login,
						"name":$j("#name").val(),
						"mail":$j("#mail").val(),
						"tel":$j("#tel").val(),
						"coupon":$j("#coupon").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"regist_customer":regist_customer,
						"menu_func":"Sales_Edit"

					},
					success: function(data) {
//						alert(data.name+" "+data.address);
//						target.fnAddData( [data.dat1, data.dat2, data.dat3] );
<?php //[TODO]redrawするが良いか ?>
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
<?php //[TODO]salesに対するinsertなので判定をわけない ?>

							if (operate =="inserted_reserve" ) {
								if ($j("#regist_customer").prop("checked")) {
									alert(data.set_data.regist_msg);
									delete data.set_data.regist_msg;
								}
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
						alert ('<?php echo salon_component::getMsg('E401'); ?>['+textStatus+']');
					}
			 });
		}

		function _fnCalcPrice() {

			var tmp = new Array();
			var price = 0;
			$j("#item_cds input[type=checkbox]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
					price += +$j(this).next().val();
				}
			});
			save_item_cds_aft = tmp.join(",");

			if ($j("#coupon") && coupons[$j("#coupon").val()]) {
				var coupon = coupons[$j("#coupon").val()];
				if (coupon.discount_patern_cd == <?php echo Salon_Discount::PERCENTAGE; ?> ) {
					price = (1 - coupon.discount/100) * price;
				}
				else {
					price -= coupon.discount;
				}
			}

			$j("#price").val(price);
		}

		function fnDetailInit(is_full_init ) {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail textarea").val("");
			$j("#item_cds input").attr("checked",false);
			$j("#item_cds input").attr("disabled",true);
			$j("#data_detail select").val("");
			$j("#button_update").attr("disabled", true);
			$j("#reserved span").text("");
			$j("#time_from_aft").val(-1);
			$j("#time_to_aft").val(-1);
			$j("#input_aft *").removeClass("sl_coler_not_complete");
			$j("#regist_customer").attr("checked", false);


			$j("#coupon").prop("selectedIndex", 0);

			if (is_full_init) {
				$j("#check_noreserved").attr("checked",false);
				$j("#reserved").show();
				$j("#no_reserved").hide();
				$j("#button_insert").attr("disabled", true);

			}



			save_k1 = "";
			save_item_cds_aft = "";
			save_user_login = "";
			<?php
					if ($this->is_multi_branch && $this->isSalonAdmin() ) echo '$j("#branch_cd").val('.$this->current_user_branch_cd.');';
			?>
			<?php parent::echo_clear_error(); ?>
		}

		<?php parent::echoRemoveModal(); ?>
		<?php parent::echoDownloadFunc($this->current_user_branch_cd,"sales"); ?>

		<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkMail','chkTime','chkDate','lenmax','reqOther','reqCheck','chkSpace','chkTel')); ?>

		<?php parent::echoDateConvert(); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Performance Regist',SL_DOMAIN); ?>
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


	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Performance Regist',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Performance Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button" />
	<input id="target_date_number" type="text" value="<?php echo $this->config_datas['SALON_CONFIG_BEFORE_DAY']; ?>" class="sl_short_title_width"/>
	<select id="target_date_patern" >
		<option value="day" ><?php _e('day before',SL_DOMAIN); ?></option>
		<option value="week" ><?php _e('week before',SL_DOMAIN); ?></option>
		<option value="month"  ><?php _e('month before',SL_DOMAIN); ?></option>
		<option value="year" ><?php _e('year before',SL_DOMAIN); ?></option>

	</select>
	<input id="button_redisplay" type="button" value="<?php _e('Redisplay',SL_DOMAIN); ?>"/>
	<input id="button_download" type="button" value="<?php _e('Download',SL_DOMAIN); ?>"/>
	</div>
	<div id="salon_no_reserved">
	<h3><label for="check_noreserved" ><input id="check_noreserved" type="checkbox"  /><?php _e('Register without Reservation',SL_DOMAIN); ?></label></h3>
	</div>

	<div id="data_detail" >
		<div id="reserved">
				<label  ><?php _e('Name',SL_DOMAIN); ?>:</label>
				<span id="reserved_name" ></span>
				<label  ><?php _e('Reserved day',SL_DOMAIN); ?>:</label>
				<span id="target_day_bef" ></span>
				<label ><?php _e('Reserved staff',SL_DOMAIN); ?>:</label>
				<span id="staff_name_bef"></span>
				<label ><?php _e('Reserved menu',SL_DOMAIN); ?>:</label>
				<span id="item_name_bef" ></span>
				<label ><?php _e('Use coupon',SL_DOMAIN); ?>:</label>
				<span id="coupon_name" ></span>
				<label ><?php _e('Wishes',SL_DOMAIN); ?>:</label>
				<span id="remark_bef" ></span>
				<label ><?php _e('Status',SL_DOMAIN); ?>:</label>
				<span id="status_name" ></span>
		</div>
			<div id="no_reserved">
				<div id="multi_item_wrap" >
				<input id="mail" type="text" />
				<input id="button_search" type="button" class="sl_button" value="<?php _e('Search',SL_DOMAIN); ?>"/>
				</div>
				<input type="text" id="tel"/>
				<input type="text" id="name" value="" />
				<div id="regist_customer_wrap"  >
					<input id="regist_customer" type="checkbox"  value="<?php echo Salon_Regist_Customer::OK; ?>" />
				</div>
			</div>
			<div id="input_aft">
					<div id="date_time_wrap" >
						<input type="text" id="target_day" />
						<div id="time_sel_wrap" >
							<?php parent::echoTimeSelect("time_from_aft",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step']); ?>
							<?php parent::echoTimeSelect("time_to_aft",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step'],false,"",true); ?>
						</div>
					</div>
					<?php parent::echoStaffSelect("staff_cd",$this->staff_datas,false); ?>
					<?php parent::echoItemInputCheckTable($this->item_datas); ?>
					<?php parent::echoCouponSelect("coupon",$this->promotion_datas); ?>
					<textarea id="remark"  ></textarea>
					<input type="text" id="price" value="" />
			</div>

		<div class="spacer"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>

<div id="sl_search" class="modal">
	<div class="modalBody">
		<div id="sl_search_result"></div>
	</div>
</div>
<div id="sl_download" class="modal" >
	<div class="modalBody">
		<div id="sl_download_result"></div>
	</div>
</div>

<?php
	if ($this->isSalonAdmin() ) echo '<div id="sl_submit" ></div>';

	}	//show_page
}		//class

