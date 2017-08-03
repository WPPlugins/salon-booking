<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Promotion_Page extends Salon_Page {

	private $set_items = null;

	private $all_branch_datas = null;
	private $branch_datas = null;

	private $current_user_branch_cd = '';



	private $usable_patern_datas = null;
	private $customer_rank_datas = null;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array('description','set_code','valid_from','valid_to','discount','remark','usable_patern','times','rank_patern','discount_patern');
	}

	public function set_all_branch_datas ($branch_datas) {
		$this->all_branch_datas = $branch_datas;
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}



	public function set_usable_patern_datas ($datas) {
		$this->usable_patern_datas = $datas;
	}
	public function set_customer_rank_datas ($datas) {
		$this->customer_rank_datas = $datas;
	}

	public function get_set_branch_cd () {
		if (empty($_POST['set_branch_cd']) ) return;

		return @$_POST['set_branch_cd'];
	}

	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";

		var save_operate = "inserted";

		var exceptCheck = "";
		var usable_data = "";

		var staff_items = new Array();


		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php parent::set_datepicker_date($this->current_user_branch_cd,null ,unserialize($this->branch_datas['sp_dates'])); ?>


		$j(document).ready(function() {

			<?php parent::echoSetItemLabel(); ?>

			<?php  parent::set_datepickerDefault(false,true); ?>
			<?php  parent::set_datepicker("valid_from",true,$this->branch_datas['closed']); ?>
			<?php  parent::set_datepicker("valid_to",true,$this->branch_datas['closed']); ?>


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


			<?php parent::echoCommonButton('save_operate');  ?>

			$j("#usable_patern_cd").change(function() {
				$j("#times_wrap").hide();
				$j("#rank_patern_wrap").hide();
				exceptCheck = ",times,rank_patern";
//				if ($j(this).val() == <?php echo Salon_Coupon::UNLIMITED; ?>) {
//				}
				if ($j(this).val() == <?php echo Salon_Coupon::TIMES; ?>) {
					$j("#times").val(usable_data);
					$j("#times_wrap").show();

					exceptCheck = ",rank_patern";
				}
				if ($j(this).val() == <?php echo Salon_Coupon::RANK; ?>) {
					$j("#rank_patern_cd").val(usable_data);
					$j("#rank_patern_wrap").show();
					exceptCheck = ",times";
				}
//				if ($j(this).val() == <?php echo Salon_Coupon::FIRST; ?>) {
//				}
			});




//			$j("#time_from_aft").click(function(){
//				_fnSetEndTime()
//			});
//			$j("#target_day").change(function(){
//				$j("#staff_cd").change();
//				_fnSetEndTime()
//			});


			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slpromotion",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('set_code','description','valid_from','valid_to','remark')); //for only_branch?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Promotion_Init" } );
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php  parent::echoDataTableSelecter("set_code",false); ?>
					element.empty();
					element.append(sel_box);
					element.append(del_box);
				}
			});

		});

<?php //taregt_colはtdが前提 ?>
		function fnSelectRow(target_col) {
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			$j("#set_code").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['set_code']));
			$j("#description").val(setData['aoData'][position[0]]['_aData']['description']);
			$j("#valid_from").val(	setData['aoData'][position[0]]['_aData']['valid_from']);
			$j("#valid_to").val(setData['aoData'][position[0]]['_aData']['valid_to']);
			<?php //こっちを先に設定しとく ?>
			usable_data = setData['aoData'][position[0]]['_aData']['usable_data'];
			$j("#usable_patern_cd").val(setData['aoData'][position[0]]['_aData']['usable_patern_cd']).change();

			$j("#rank_patern").val(setData['aoData'][position[0]]['_aData']['rank_patern']);
			$j("#discount").val(setData['aoData'][position[0]]['_aData']['discount']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));

			var discount_patern_value = setData['aoData'][position[0]]['_aData']['discount_patern_cd'];
			$j("#data_detail :radio[name=\"discount_patern\"]").val([discount_patern_value]);

			$j("#button_update").attr("disabled",false);
			$j("#button_insert").attr("disabled",false);
			$j("#button_clear").attr("disabled",false);

			$j("#data_detail").show();

			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");
		}

		<?php parent::echoDataTableDeleteRow("promotion"); ?>

		<?php parent::echoDisplayErrorLable(); ?>


		function fnCheckDayFromTo(from,to) {
			var from = new Date();
		}


		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail","discount_patern_ratio,discount_patern_amount"+exceptCheck) ) return false;

			var branch_cd = <?php echo $this->current_user_branch_cd; ?>;
			var promotion_cd = "";
			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				promotion_cd = setData['aoData'][save_k1]['_aData']['promotion_cd'];
				branch_cd = setData['aoData'][save_k1]['_aData']['branch_cd'];
			}

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slpromotion",
					dataType : "json",
					data: {
						"promotion_cd":promotion_cd,
						"no":save_k1,
						"type":operate,
						"branch_cd":branch_cd,
						"set_code":$j("#set_code").val(),
						"description":$j("#description").val(),
						"valid_from":$j("#valid_from").val(),
						"valid_to":$j("#valid_to").val(),
						"usable_patern_cd":$j("#usable_patern_cd").val(),
						"times":$j("#times").val(),
						"rank_patern_cd":$j("#rank_patern_cd").val(),
						"discount_patern_cd":$j("#data_detail :radio[name=\"discount_patern\"]:checked").val(),
						"discount":$j("#discount").val(),
						"remark":$j("#remark").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Promotion_Edit"

					},
					success: function(data) {
//						alert(data.name+" "+data.address);
//						target.fnAddData( [data.dat1, data.dat2, data.dat3] );
<?php //[TODO]redrawするが良いか ?>
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
						alert ('<?php echo salon_component::getMsg('E401'); ?>['+textStatus+']');
					}
			 });
		}


		function fnDetailInit( ) {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail textarea").val("");

			$j("#button_update").attr("disabled", true);
			$j("#usable_patern_cd").val("<?php echo Salon_Coupon::UNLIMITED;?>");
<?php //[TODO] 	下のtoriggerが効かない？?>

			$j("#times").val("");
			$j("#rank_patern_cd").val("<?php echo Salon_CRank::STANDARD;?>");

			$j("#times_wrap").hide();
			$j("#rank_patern_wrap").hide();
			exceptCheck = ",times,rank_patern";
//			$j("#usable_patern_cd").trigger("change");



			$j("#data_detail :radio[name=\"discount_patern\"]").prop('checked', false);
			$j("#data_detail :radio[name=\"discount_patern\"]:first").prop('checked', true);
			save_k1 = "";
			<?php

					if ($this->is_multi_branch && $this->isSalonAdmin() ) echo '$j("#branch_cd").val('.$this->current_user_branch_cd.');';
			?>
			<?php parent::echo_clear_error(); ?>
		}

		<?php parent::echoRemoveModal(); ?>

		<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkMail','chkTime','chkDate','lenmax','reqOther','reqCheck','chkSpace','chkTel')); ?>


	</script>


	<h2 id="sl_admin_title"><?php _e('Promotion Regist',SL_DOMAIN); ?>
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
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button" />
	</div>

	<div id="data_detail" >
		<input type="text" id="set_code"/>
		<input type="text" id="description" value="" />
        <input type="text" id="valid_from"/>
        <input type="text" id="valid_to" />

		<select id="usable_patern_cd" >
		<?php
			foreach ($this->usable_patern_datas as $k1 => $d1 ) {
				echo '<option value="'.$k1.'" >'.$d1.'</option>';
			}
		?>
		</select>

		<div id="times_wrap" >
	   		<input type="text" id="times" />
		</div>
		<?php parent::echoRankPatern( $this->customer_rank_datas); ?>

		<div id="discount_wrap" class="sl_checkbox">
			<input type="radio"  id="discount_patern_ratio"  name="discount_patern"  style="width:16px;margin:5px 1px 0px 10px;" value="<?php echo Salon_Discount::PERCENTAGE; ?>">
			<label for="discount_patern_ratio" style="margin:5px;text-align:left;width:auto;"><?php _e('Percentage',SL_DOMAIN); ?></label>
			<input type="radio" id="discount_patern_amount"  name="discount_patern"  style="width:16px;margin:5px 5px 0px 10px;" value="<?php echo Salon_Discount::AMOUNT; ?>">
			<label for="discount_patern_amount" style="margin:5px;text-align:left;width:auto;"><?php _e('Amount',SL_DOMAIN); ?></label>
		</div>
		<input type="text" id="discount" value=""   />
		<textarea id="remark"  ></textarea>

		<div class="spacer"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>




<?php
	if ($this->isSalonAdmin() ) echo '<div id="sl_submit" ></div>';

	}	//show_page
}		//class

